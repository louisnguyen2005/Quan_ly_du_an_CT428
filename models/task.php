<?php

require_once __DIR__ . '/../config/database.php';

class Task
{
    private PDO $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
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
                p.name AS project_name,
                p.manager_id,
                creator.username AS manager_name,
                assignee.username AS assigned_name,
                CASE
                    WHEN t.status = 'Completed' THEN 100
                    WHEN t.status = 'Review' THEN 80
                    WHEN t.status = 'In Progress' THEN 50
                    ELSE 0
                END AS progress
            FROM tasks t
            LEFT JOIN projects p
                ON t.project_id = p.project_id
            LEFT JOIN users assignee
                ON t.assignee_id = assignee.user_id
            LEFT JOIN users creator
                ON t.created_by = creator.user_id
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
        $allowed = ['Pending', 'In Progress', 'Review', 'Completed'];

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
