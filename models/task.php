<?php

require_once __DIR__ . '/../config/database.php';

class Task
{
    private PDO $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
        $this->ensureApprovalColumns();
    }

    private function ensureApprovalColumns(): void
    {
        try {
            $this->conn->exec("ALTER TABLE tasks MODIFY status ENUM('Pending','In Progress','Review','Completed','Pending Approval','Approved','Rejected') DEFAULT 'Pending'");

            $columns = [
                'approval_status' => "ALTER TABLE tasks ADD approval_status VARCHAR(30) NULL DEFAULT NULL AFTER status",
                'approved_by' => "ALTER TABLE tasks ADD approved_by INT NULL AFTER approval_status",
                'approved_at' => "ALTER TABLE tasks ADD approved_at DATETIME NULL AFTER approved_by",
                'rejected_reason' => "ALTER TABLE tasks ADD rejected_reason TEXT NULL AFTER approved_at",
                'progress_percent' => "ALTER TABLE tasks ADD progress_percent INT NOT NULL DEFAULT 0 AFTER rejected_reason",
                'progress_comment' => "ALTER TABLE tasks ADD progress_comment TEXT NULL AFTER progress_percent",
                'progress_file' => "ALTER TABLE tasks ADD progress_file VARCHAR(500) NULL AFTER progress_comment",
            ];

            foreach ($columns as $column => $sql) {
                $stmt = $this->conn->prepare("SHOW COLUMNS FROM tasks LIKE ?");
                $stmt->execute([$column]);
                if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->conn->exec($sql);
                }
            }
        } catch (Throwable $e) {
            // Database migrations can be applied manually with the ALTER statements in the handoff note.
        }
    }

    private function writeAuditLog(?int $userId, string $action, string $entity, ?int $entityId, ?int $projectId, string $description): void
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO audit_logs(user_id, action, entity, entity_id, project_id, description) VALUES(?,?,?,?,?,?)");
            $stmt->execute([$userId, $action, $entity, $entityId, $projectId, $description]);
        } catch (PDOException $e) {
            // Không để lỗi ghi log làm hỏng chức năng chính.
        }
    }


    private function shouldNotify(int $userId, string $type): bool
    {
        try {
            $stmt = $this->conn->prepare("SELECT task_notifications, comment_notifications, deadline_notifications FROM notification_preferences WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $prefs = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$prefs) {
                return true;
            }
            $type = strtolower($type);
            if (str_contains($type, 'task') && (int)$prefs['task_notifications'] === 0) {
                return false;
            }
            if (str_contains($type, 'comment') && (int)$prefs['comment_notifications'] === 0) {
                return false;
            }
            if ((str_contains($type, 'deadline') || str_contains($type, 'due')) && (int)$prefs['deadline_notifications'] === 0) {
                return false;
            }
        } catch (Throwable $e) {
            return true;
        }
        return true;
    }

    private function createNotification(int $userId, string $type, string $title, string $content, string $relatedType = 'task', ?int $relatedId = null): void
    {
        if ($userId <= 0 || !$this->shouldNotify($userId, $type)) {
            return;
        }
        try {
            $stmt = $this->conn->prepare("INSERT INTO notifications(user_id, type, title, content, related_type, related_id, is_read, created_at) VALUES(?,?,?,?,?,?,0,NOW())");
            $stmt->execute([$userId, $type, $title, $content, $relatedType, $relatedId]);
        } catch (PDOException $e) {
            // Không để lỗi thông báo làm hỏng chức năng chính.
        }
    }

    private function taskNotifyInfo(int $taskId, int $actorId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT
                t.title,
                t.project_id,
                p.manager_id,
                p.created_by,
                u.username AS actor_name
            FROM tasks t
            JOIN projects p ON p.project_id = t.project_id
            LEFT JOIN users u ON u.user_id = ?
            WHERE t.task_id = ?
            LIMIT 1
        ");
        $stmt->execute([$actorId, $taskId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function taskBasicInfo(int $taskId): ?array
    {
        $stmt = $this->conn->prepare("SELECT task_id, title, project_id FROM tasks WHERE task_id = ? LIMIT 1");
        $stmt->execute([$taskId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function selectBase(): string
    {
        return "
            SELECT
                t.*,
                t.title AS task_name,
                t.due_date AS deadline,
                t.created_at AS assigned_date,
                p.name AS project_name,
                p.manager_id,
                COALESCE(manager.username, creator.username) AS manager_name,
                assignee.username AS assigned_name,
                COALESCE(t.progress_percent,
                    CASE
                        WHEN t.status IN ('Completed', 'Approved', 'Pending Approval') THEN 100
                        WHEN t.status = 'Review' THEN 80
                        WHEN t.status = 'In Progress' THEN 50
                        ELSE 0
                    END
                ) AS progress
            FROM tasks t
            LEFT JOIN projects p
                ON t.project_id = p.project_id
            LEFT JOIN users assignee
                ON t.assignee_id = assignee.user_id
            LEFT JOIN users creator
                ON t.created_by = creator.user_id
            LEFT JOIN users manager
                ON p.manager_id = manager.user_id
        ";
    }

    public function getAll(): array
    {
        $sql = $this->selectBase() . "
            WHERE t.is_deleted = 0
            ORDER BY t.task_id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByManager(int $managerId): array
    {
        $sql = $this->selectBase() . "
            WHERE t.is_deleted = 0
              AND p.is_deleted = 0
              AND p.manager_id = ?
            ORDER BY t.task_id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$managerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByStaff(int $staffId): array
    {
        $sql = $this->selectBase() . "
            WHERE t.is_deleted = 0
              AND t.assignee_id = ?
            ORDER BY t.due_date ASC, t.task_id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$staffId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByProject(int $projectId): array
    {
        $sql = $this->selectBase() . "
            WHERE t.is_deleted = 0
              AND t.project_id = ?
            ORDER BY t.task_id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$projectId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $sql = $this->selectBase() . "
            WHERE t.task_id = ?
              AND t.is_deleted = 0
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            return null;
        }

        $task['comments'] = $this->getComments($id);
        $task['attachments'] = $this->getAttachments($id);

        return $task;
    }

    public function findForStaff(int $taskId, int $staffId): ?array
    {
        $sql = $this->selectBase() . "
            WHERE t.task_id = ?
              AND t.assignee_id = ?
              AND t.is_deleted = 0
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$taskId, $staffId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            return null;
        }

        $task['comments'] = $this->getComments($taskId);
        $task['attachments'] = $this->getAttachments($taskId);
        return $task;
    }

    public function findForManager(int $taskId, int $managerId): ?array
    {
        $sql = $this->selectBase() . "
            WHERE t.task_id = ?
              AND t.is_deleted = 0
              AND p.is_deleted = 0
              AND p.manager_id = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$taskId, $managerId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            return null;
        }

        $task['comments'] = $this->getComments($taskId);
        $task['attachments'] = $this->getAttachments($taskId);
        return $task;
    }

    public function create(array $data): bool
    {
        $sql = "
            INSERT INTO tasks
            (
                project_id,
                title,
                description,
                status,
                priority,
                assignee_id,
                created_by,
                due_date
            )
            VALUES
            (
                ?,?,?,?,?,?,?,?
            )
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $data['project_id'],
            $data['title'] ?? $data['task_name'],
            $data['description'] ?? null,
            $data['status'] ?? 'Pending',
            $data['priority'] ?? 'Medium',
            $data['assignee_id'] ?? $data['assigned_to'] ?? null,
            $_SESSION['user_id'] ?? $data['created_by'] ?? 1,
            $data['due_date'] ?? null
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE tasks
            SET
                project_id = ?,
                title = ?,
                description = ?,
                status = ?,
                priority = ?,
                assignee_id = ?,
                due_date = ?
            WHERE task_id = ?
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $data['project_id'],
            $data['title'] ?? $data['task_name'],
            $data['description'] ?? null,
            $data['status'] ?? 'Pending',
            $data['priority'] ?? 'Medium',
            $data['assignee_id'] ?? $data['assigned_to'] ?? null,
            $data['due_date'] ?? null,
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "
            UPDATE tasks
            SET is_deleted = 1
            WHERE task_id = ?
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$id]);
    }

    public function updateStatus(int $taskId, string $status, int $userId): bool
    {
        $allowed = ['Pending', 'In Progress', 'Review', 'Completed', 'Pending Approval', 'Approved', 'Rejected'];

        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $roleId = $_SESSION['role_id'] ?? 0;
        $task = $this->taskBasicInfo($taskId);

        if ($roleId == 3) {
            $sql = "
                UPDATE tasks
                SET status = ?
                WHERE task_id = ?
                  AND assignee_id = ?
                  AND is_deleted = 0
            ";

            $stmt = $this->conn->prepare($sql);
            $ok = $stmt->execute([$status, $taskId, $userId]);
        } else {
            $sql = "
                UPDATE tasks
                SET status = ?
                WHERE task_id = ?
                  AND is_deleted = 0
            ";

            $stmt = $this->conn->prepare($sql);
            $ok = $stmt->execute([$status, $taskId]);
        }

        if ($ok && $stmt->rowCount() > 0 && $task) {
            $this->writeAuditLog(
                $userId > 0 ? $userId : null,
                $roleId == 3 ? 'STAFF_UPDATE_PROGRESS' : 'UPDATE_TASK_STATUS',
                'tasks',
                $taskId,
                (int)$task['project_id'],
                'đã cập nhật tiến độ nhiệm vụ "' . $task['title'] . '" sang trạng thái "' . $status . '"'
            );

            if ($roleId == 3) {
                $info = $this->taskNotifyInfo($taskId, $userId);
                $targetUser = (int)($info['manager_id'] ?: $info['created_by'] ?: 0);
                $statusText = [
                    'Pending' => 'Chưa bắt đầu',
                    'In Progress' => 'Đang thực hiện',
                    'Review' => 'Đang xem xét',
                    'Completed' => 'Hoàn thành'
                ];
                if ($info && $targetUser > 0 && $targetUser !== $userId) {
                    $this->createNotification(
                        $targetUser,
                        'task',
                        'Cập nhật trạng thái',
                        ($info['actor_name'] ?? 'Staff') . ' đã cập nhật trạng thái nhiệm vụ "' . $info['title'] . '" thành "' . ($statusText[$status] ?? $status) . '"',
                        'task',
                        $taskId
                    );
                }
            }
        }

        return $ok;
    }

    public function saveProgress(int $taskId, int $staffId, int $progress, string $comment, string $status, ?array $file, bool $submit): bool
    {
        // Staff chỉ được tự chọn 3 trạng thái này. "Pending Approval"/"Completed" thật (được
        // tính vào % dự án) và "Rejected" là do hệ thống/manager quyết định, không phải staff tự set.
        $allowed = ['Pending', 'In Progress', 'Completed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $task = $this->findForStaff($taskId, $staffId);
        if (!$task) {
            return false;
        }

        $filePath = $task['progress_file'] ?? null;

        try {
            $this->conn->beginTransaction();

            if ($file && !empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
                if (!$this->addAttachment($taskId, $staffId, $file)) {
                    throw new RuntimeException('Upload file thất bại.');
                }

                $latest = $this->getAttachments($taskId)[0] ?? null;
                $filePath = $latest['file_path'] ?? $filePath;
            }

            // Chọn "Completed" LUÔN chuyển thành "Pending Approval", bất kể bấm Save Draft hay
            // Submit - tránh việc staff né vòng duyệt bằng cách chọn Completed rồi lưu draft.
            $nextStatus = ($status === 'Completed') ? 'Pending Approval' : $status;
            $approvalStatus = ($status === 'Completed') ? 'Pending Approval' : ($task['approval_status'] ?? null);

            // Bất kỳ lần staff lưu tiến độ mới nào (draft hoặc submit) cũng xóa lý do từ chối cũ:
            // badge "Bị từ chối" chỉ tồn tại cho tới khi staff cập nhật lại, không phụ thuộc việc
            // họ đã "xem" hay chưa.
            $stmt = $this->conn->prepare("
                UPDATE tasks
                SET status = ?,
                    approval_status = ?,
                    progress_percent = ?,
                    progress_comment = ?,
                    progress_file = ?,
                    rejected_reason = NULL
                WHERE task_id = ?
                  AND assignee_id = ?
                  AND is_deleted = 0
            ");
            $ok = $stmt->execute([
                $nextStatus,
                $approvalStatus,
                max(0, min(100, $progress)),
                $comment,
                $filePath,
                $taskId,
                $staffId
            ]);

            if ($comment !== '') {
                $this->addComment($taskId, $staffId, $comment);
            }

            if ($nextStatus === 'Pending Approval') {
                $managerId = (int)($task['manager_id'] ?: $task['created_by']);
                $this->createNotification(
                    $managerId,
                    'task_approval',
                    'Task chờ duyệt',
                    'Staff đã submit task "' . $task['title'] . '" để duyệt.',
                    'task',
                    $taskId
                );
                $this->writeAuditLog($staffId, 'SUBMIT_TASK_APPROVAL', 'tasks', $taskId, (int)$task['project_id'], 'đã submit task "' . $task['title'] . '" để manager duyệt');
            }

            $this->conn->commit();
            return $ok;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function getPendingApprovalByManager(int $managerId): array
    {
        $sql = $this->selectBase() . "
            WHERE t.is_deleted = 0
              AND p.is_deleted = 0
              AND t.status = 'Pending Approval'
              AND p.manager_id = ?
            ORDER BY t.updated_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$managerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tính lại % tiến độ + trạng thái của project dựa trên số task Completed.
     * Gọi sau khi 1 task được duyệt xong, để thanh tiến độ dự án phản ánh đúng ngay lập tức.
     */
    private function syncProjectProgress(int $projectId): void
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total_tasks,
                   SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) AS completed_tasks
            FROM tasks
            WHERE project_id = ? AND is_deleted = 0
        ");
        $stmt->execute([$projectId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = (int)($row['total_tasks'] ?? 0);
        $completed = (int)($row['completed_tasks'] ?? 0);

        if ($total === 0) {
            $status = 'Planning';
            $progress = 0;
        } elseif ($completed === $total) {
            $status = 'Completed';
            $progress = 100;
        } else {
            $status = 'In Progress';
            $progress = (int)round($completed / $total * 100);
        }

        $this->conn->prepare("UPDATE projects SET status = ?, progress = ? WHERE project_id = ?")
            ->execute([$status, $progress, $projectId]);
    }

    public function reviewApproval(int $taskId, int $managerId, bool $approve, string $reason = ''): bool
    {
        $task = $this->findForManager($taskId, $managerId);
        if (!$task || $task['status'] !== 'Pending Approval') {
            return false;
        }

        // Approve -> "Completed" thật (được updateProjectStatus/syncProjectProgress tính vào %
        // dự án). Reject -> quay lại "Pending" ngay lập tức kèm lý do; không dùng status "Rejected"
        // để lưu lâu dài, tránh phải đồng bộ 1 trạng thái riêng biệt ở khắp nơi. Badge "Bị từ chối"
        // bên staff dựa vào rejected_reason còn tồn tại hay không, không dựa vào status.
        $nextStatus = $approve ? 'Completed' : 'Pending';

        $stmt = $this->conn->prepare("
            UPDATE tasks
            SET status = ?,
                approval_status = ?,
                approved_by = ?,
                approved_at = ?,
                progress_percent = ?,
                rejected_reason = ?
            WHERE task_id = ?
        ");
        $ok = $stmt->execute([
            $nextStatus,
            $approve ? 'Approved' : null,
            $approve ? $managerId : null,
            $approve ? date('Y-m-d H:i:s') : null,
            $approve ? 100 : (int)($task['progress_percent'] ?? 0),
            $approve ? null : $reason,
            $taskId,
        ]);

        if ($ok && $approve) {
            $this->syncProjectProgress((int)$task['project_id']);
        }

        if ($ok && !empty($task['assignee_id'])) {
            $title = $approve ? 'Task đã được duyệt' : 'Task bị từ chối';
            $content = $approve
                ? 'Manager đã duyệt task "' . $task['title'] . '".'
                : 'Manager từ chối task "' . $task['title'] . '". Lý do: ' . $reason;
            $this->createNotification((int)$task['assignee_id'], 'task_approval', $title, $content, 'task', $taskId);
            $this->writeAuditLog($managerId, $approve ? 'APPROVE_TASK' : 'REJECT_TASK', 'tasks', $taskId, (int)$task['project_id'], $content);
        }

        return $ok;
    }

    public function addComment(int $taskId, int $userId, string $content): bool
    {
        $sql = "
            INSERT INTO comments
            (
                task_id,
                user_id,
                content
            )
            VALUES
            (
                ?,?,?
            )
        ";

        $stmt = $this->conn->prepare($sql);

        $ok = $stmt->execute([
            $taskId,
            $userId,
            $content
        ]);

        if ($ok) {
            $info = $this->taskNotifyInfo($taskId, $userId);
            $targetUser = (int)($info['manager_id'] ?: $info['created_by'] ?: 0);
            if ($info && $targetUser > 0 && $targetUser !== $userId) {
                $this->createNotification(
                    $targetUser,
                    'comment',
                    'Bình luận mới',
                    ($info['actor_name'] ?? 'Người dùng') . ' đã bình luận vào nhiệm vụ "' . $info['title'] . '"',
                    'task',
                    $taskId
                );
            }
        }

        return $ok;
    }

    public function getComments(int $taskId): array
    {
        $sql = "
            SELECT
                c.*,
                u.username AS author,
                r.name AS role_name
            FROM comments c
            JOIN users u
                ON c.user_id = u.user_id
            LEFT JOIN roles r
                ON u.role_id = r.role_id
            WHERE c.task_id = ?
            ORDER BY c.created_at ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$taskId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addAttachment(int $taskId, int $userId, array $file): bool
    {
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $uploadDir = __DIR__ . '/../uploads/task_reports';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = basename($file['name']);
        $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $targetPath = $uploadDir . '/' . $safeName;
        $dbPath = 'uploads/task_reports/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return false;
        }

        $sql = "
            INSERT INTO attachments
            (
                task_id,
                file_name,
                file_path,
                file_type,
                file_size,
                uploaded_by
            )
            VALUES
            (
                ?,?,?,?,?,?
            )
        ";

        $stmt = $this->conn->prepare($sql);

        $ok = $stmt->execute([
            $taskId,
            $originalName,
            $dbPath,
            $file['type'] ?? null,
            $file['size'] ?? 0,
            $userId
        ]);

        if ($ok) {
            $task = $this->taskBasicInfo($taskId);
            $this->writeAuditLog(
                $userId > 0 ? $userId : null,
                'STAFF_UPLOAD_REPORT',
                'attachments',
                (int)$this->conn->lastInsertId(),
                $task ? (int)$task['project_id'] : null,
                'đã gửi báo cáo tiến độ cho nhiệm vụ "' . ($task['title'] ?? ('#' . $taskId)) . '"'
            );

            $info = $this->taskNotifyInfo($taskId, $userId);
            $targetUser = (int)($info['manager_id'] ?: $info['created_by'] ?: 0);
            if ($info && $targetUser > 0 && $targetUser !== $userId) {
                $this->createNotification(
                    $targetUser,
                    'task',
                    'Báo cáo mới',
                    ($info['actor_name'] ?? 'Staff') . ' đã tải lên báo cáo "' . $originalName . '" cho nhiệm vụ "' . $info['title'] . '"',
                    'task',
                    $taskId
                );
            }
        }

        return $ok;
    }

    public function getAttachments(int $taskId): array
    {
        $sql = "
            SELECT
                a.*,
                u.username AS uploader_name
            FROM attachments a
            LEFT JOIN users u
                ON a.uploaded_by = u.user_id
            WHERE a.task_id = ?
            ORDER BY a.uploaded_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$taskId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(): int
    {
        return (int)$this->conn->query("SELECT COUNT(*) FROM tasks WHERE is_deleted = 0")->fetchColumn();
    }

    public function countCompleted(): int
    {
        return (int)$this->conn->query("SELECT COUNT(*) FROM tasks WHERE status='Completed' AND is_deleted = 0")->fetchColumn();
    }

    public function countByManager(int $managerId): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tasks t JOIN projects p ON t.project_id=p.project_id WHERE p.manager_id=? AND t.is_deleted=0");
        $stmt->execute([$managerId]);
        return (int)$stmt->fetchColumn();
    }

    public function countCompletedByManager(int $managerId): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tasks t JOIN projects p ON t.project_id=p.project_id WHERE p.manager_id=? AND t.status='Completed' AND t.is_deleted=0");
        $stmt->execute([$managerId]);
        return (int)$stmt->fetchColumn();
    }

    public function countByStaff(int $staffId): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tasks WHERE assignee_id=? AND is_deleted=0");
        $stmt->execute([$staffId]);
        return (int)$stmt->fetchColumn();
    }

    public function countCompletedByStaff(int $staffId): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tasks WHERE assignee_id=? AND status='Completed' AND is_deleted=0");
        $stmt->execute([$staffId]);
        return (int)$stmt->fetchColumn();
    }
}
