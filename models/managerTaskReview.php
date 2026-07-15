<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Compatibility model for Manager approval/rejection.
 * Uses the current tasks schema: Pending Approval / Approved / Rejected,
 * approval_status, approved_by, approved_at and rejected_reason.
 */
class ManagerTaskReviewModel
{
    private PDO $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    private function ownedCondition(): string
    {
        return "(
            p.created_by = :mid_created
            OR p.manager_id = :mid_manager
            OR EXISTS(
                SELECT 1 FROM project_members pmx
                WHERE pmx.project_id = p.project_id
                  AND pmx.user_id = :mid_member
                  AND (pmx.role_in_project LIKE '%Manager%'
                       OR pmx.role_in_project IN ('Leader', 'Manager'))
            )
        )";
    }

    private function managerParams(int $managerId): array
    {
        return [
            'mid_created' => $managerId,
            'mid_manager' => $managerId,
            'mid_member' => $managerId,
        ];
    }

    private function findReviewableTask(int $taskId, int $managerId): ?array
    {
        $owned = $this->ownedCondition();
        $sql = "
            SELECT t.task_id, t.project_id, t.status, t.assignee_id, t.title
            FROM tasks t
            JOIN projects p ON p.project_id = t.project_id
            WHERE t.task_id = :tid
              AND t.is_deleted = 0
              AND p.is_deleted = 0
              AND t.status = 'Pending Approval'
              AND {$owned}
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_merge(['tid' => $taskId], $this->managerParams($managerId)));
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        return $task ?: null;
    }

    public function approve(int $taskId, int $managerId): bool
    {
        $task = $this->findReviewableTask($taskId, $managerId);
        if (!$task) {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE tasks
            SET status = 'Approved',
                approval_status = 'Approved',
                progress_percent = 100,
                approved_at = NOW(),
                approved_by = ?,
                rejected_reason = NULL
            WHERE task_id = ? AND status = 'Pending Approval'
        ");
        $ok = $stmt->execute([$managerId, $taskId]) && $stmt->rowCount() > 0;

        if ($ok) {
            $this->writeAuditLog($managerId, 'APPROVE_TASK', $task, 'Manager duyệt task');
            $this->notifyStaff(
                $task,
                'Task đã được duyệt',
                'Nhiệm vụ "' . $task['title'] . '" đã được Manager duyệt.',
                'task_approval'
            );
        }

        return $ok;
    }

    public function reject(int $taskId, int $managerId, string $note): bool
    {
        $note = trim($note);
        if ($note === '') {
            return false;
        }

        $task = $this->findReviewableTask($taskId, $managerId);
        if (!$task) {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE tasks
            SET status = 'Rejected',
                approval_status = 'Rejected',
                approved_at = NOW(),
                approved_by = ?,
                rejected_reason = ?
            WHERE task_id = ? AND status = 'Pending Approval'
        ");
        $ok = $stmt->execute([$managerId, $note, $taskId]) && $stmt->rowCount() > 0;

        if ($ok) {
            $this->writeAuditLog($managerId, 'REJECT_TASK', $task, 'Manager từ chối task: ' . $note);
            $this->notifyStaff(
                $task,
                'Task bị từ chối',
                'Nhiệm vụ "' . $task['title'] . '" bị từ chối. Lý do: ' . $note,
                'task_approval'
            );
        }

        return $ok;
    }

    private function writeAuditLog(int $managerId, string $action, array $task, string $description): void
    {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO audit_logs(user_id, action, entity, entity_id, project_id, description)
                VALUES (?, ?, 'tasks', ?, ?, ?)
            ");
            $stmt->execute([
                $managerId,
                $action,
                (int)$task['task_id'],
                (int)$task['project_id'],
                $description,
            ]);
        } catch (Throwable $e) {
            // Audit log must not break the main review transaction.
        }
    }

    private function notifyStaff(array $task, string $title, string $content, string $type): void
    {
        if (empty($task['assignee_id'])) {
            return;
        }

        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (user_id, type, title, content, related_type, related_id, is_read)
                VALUES (?, ?, ?, ?, 'task', ?, 0)
            ");
            $stmt->execute([(int)$task['assignee_id'], $type, $title, $content, (int)$task['task_id']]);
        } catch (Throwable $e) {
            // Notification failure must not roll back the review action.
        }
    }
}
