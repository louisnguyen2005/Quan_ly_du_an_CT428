<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/taskHistory.php';

/**
 * Handles the Manager side of the review workflow: Approve or Reject a
 * task that a staff member submitted (status = 'Review'). Every query is
 * scoped so a manager can only act on tasks belonging to projects they
 * manage through projects.manager_id.
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
        return "p.manager_id = :mid";
    }

    /**
     * Fetches the task IF it belongs to a project this manager owns AND
     * is currently awaiting review. Returns null otherwise (invalid action).
     */
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
              AND t.status = 'Review'
              AND {$owned}
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['tid' => $taskId, 'mid' => $managerId]);

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
            SET status = 'Completed',
                progress = 100,
                reviewed_at = NOW(),
                reviewed_by = ?,
                review_note = NULL
            WHERE task_id = ? AND status = 'Review'
        ");
        $ok = $stmt->execute([$managerId, $taskId]) && $stmt->rowCount() > 0;

        if ($ok) {
            log_task_history($this->conn, $taskId, $managerId, 'APPROVE', 'Review', 'Completed', 'Manager duyệt task');
            $this->notifyStaff($task, 'Task đã được duyệt', 'Nhiệm vụ "' . $task['title'] . '" đã được Manager duyệt và đánh dấu hoàn thành.', 'task_approved');
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
                reviewed_at = NOW(),
                reviewed_by = ?,
                review_note = ?
            WHERE task_id = ? AND status = 'Review'
        ");
        $ok = $stmt->execute([$managerId, $note, $taskId]) && $stmt->rowCount() > 0;

        if ($ok) {
            log_task_history($this->conn, $taskId, $managerId, 'REJECT', 'Review', 'Rejected', $note);
            $this->notifyStaff($task, 'Task bị từ chối', 'Nhiệm vụ "' . $task['title'] . '" đã bị Manager từ chối. Lý do: ' . $note, 'task_rejected');
        }

        return $ok;
    }

    /**
     * Best-effort notification to the assignee. Failure here must never
     * roll back the approve/reject action itself, so errors are swallowed.
     */
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
            $stmt->execute([(int)$task['assignee_id'], $type, $title, $content, $task['task_id']]);
        } catch (PDOException $e) {
            // Notification is a nice-to-have; the review action already succeeded.
        }
    }

    public function reviewQueue(int $managerId): array
    {
    $sql = "
        SELECT
            t.task_id,
            t.title,
            t.progress_percent AS progress,
            t.due_date,
            t.updated_at AS submitted_at,
            p.name AS project_name,
            u.full_name AS assignee_name

        FROM tasks t
        JOIN projects p ON p.project_id = t.project_id
        LEFT JOIN users u ON u.user_id = t.assignee_id

        WHERE
            t.status = 'Review'
            AND t.is_deleted = 0
            AND p.is_deleted = 0
            AND p.manager_id = :managerId

        ORDER BY t.updated_at DESC
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute([
        'managerId' => $managerId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}