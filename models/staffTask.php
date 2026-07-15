<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/taskHistory.php';

/**
 * All queries here are scoped to assignee_id = the logged-in staff user.
 * Staff can only ever see and act on their own tasks.
 */
class StaffTaskModel
{
    private PDO $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * @param array $filters {
     *   tab: 'all'|'todo'|'in_progress'|'waiting_review'|'completed',
     *   search: string,
     *   project_id: int,
     *   priority: string,
     *   sort: 'deadline'|'status'
     * }
     */
    public function getFilteredTasks(int $staffId, array $filters): array
    {
        $where  = ['t.assignee_id = ?', 't.is_deleted = 0'];
        $params = [$staffId];

        switch ($filters['tab'] ?? 'all') {
            case 'todo':
                $where[] = "t.status = 'Pending'";
                break;
            case 'in_progress':
                $where[] = "t.status IN ('In Progress','Paused','Rejected')";
                break;
            case 'waiting_review':
                $where[] = "t.status = 'Review'";
                break;
            case 'completed':
                $where[] = "t.status = 'Completed'";
                break;
        }

        if (!empty($filters['search'])) {
            $where[] = 't.title LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['project_id'])) {
            $where[] = 't.project_id = ?';
            $params[] = (int)$filters['project_id'];
        }

        if (!empty($filters['priority'])) {
            $where[] = 't.priority = ?';
            $params[] = $filters['priority'];
        }

        $orderBy = 't.due_date ASC, t.task_id DESC';
        if (($filters['sort'] ?? '') === 'status') {
            $orderBy = "FIELD(t.status,'Rejected','In Progress','Paused','Pending','Review','Completed'), t.due_date ASC";
        }

        $sql = "
            SELECT
                t.task_id, t.title, t.status, t.priority, t.progress,
                t.due_date, t.created_at, t.estimated_hours, t.review_note,
                p.project_id, p.name AS project_name
            FROM tasks t
            LEFT JOIN projects p ON p.project_id = t.project_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY {$orderBy}
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Counts per tab, used for the tab badges (computed once, unaffected
     * by the active filters so the numbers stay stable while browsing).
     */
    public function getTabCounts(int $staffId): array
    {
        $sql = "
            SELECT
                COUNT(*) AS all_count,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS todo_count,
                SUM(CASE WHEN status IN ('In Progress','Paused','Rejected') THEN 1 ELSE 0 END) AS in_progress_count,
                SUM(CASE WHEN status = 'Review' THEN 1 ELSE 0 END) AS waiting_review_count,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_count
            FROM tasks
            WHERE assignee_id = ? AND is_deleted = 0
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$staffId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'all'            => (int)($row['all_count'] ?? 0),
            'todo'           => (int)($row['todo_count'] ?? 0),
            'in_progress'    => (int)($row['in_progress_count'] ?? 0),
            'waiting_review' => (int)($row['waiting_review_count'] ?? 0),
            'completed'      => (int)($row['completed_count'] ?? 0),
        ];
    }

    /**
     * Distinct projects this staff member has tasks in, for the filter dropdown.
     */
    public function getStaffProjectOptions(int $staffId): array
    {
        $sql = "
            SELECT DISTINCT p.project_id, p.name
            FROM tasks t
            JOIN projects p ON p.project_id = t.project_id
            WHERE t.assignee_id = ? AND t.is_deleted = 0
            ORDER BY p.name ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$staffId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches a single task IF it belongs to this staff member, otherwise null.
     * Used before every transition to guarantee ownership.
     */
    private function findOwnTask(int $taskId, int $staffId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT task_id, status, progress
            FROM tasks
            WHERE task_id = ? AND assignee_id = ? AND is_deleted = 0
            LIMIT 1
        ");
        $stmt->execute([$taskId, $staffId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        return $task ?: null;
    }

    private function logHistory(int $taskId, int $userId, string $actionType, ?string $from, ?string $to, ?string $note = null): void
    {
        log_task_history($this->conn, $taskId, $userId, $actionType, $from, $to, $note);
    }

    public function startTask(int $taskId, int $staffId): bool
    {
        $task = $this->findOwnTask($taskId, $staffId);

        if (!$task || $task['status'] !== 'Pending') {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE tasks SET status = 'In Progress', started_at = NOW()
            WHERE task_id = ? AND assignee_id = ? AND status = 'Pending'
        ");
        $ok = $stmt->execute([$taskId, $staffId]) && $stmt->rowCount() > 0;

        if ($ok) {
            $this->logHistory($taskId, $staffId, 'STATUS_CHANGE', 'Pending', 'In Progress', 'Bắt đầu thực hiện task');
        }

        return $ok;
    }

    public function pauseTask(int $taskId, int $staffId): bool
    {
        $task = $this->findOwnTask($taskId, $staffId);

        if (!$task || $task['status'] !== 'In Progress') {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE tasks SET status = 'Paused'
            WHERE task_id = ? AND assignee_id = ? AND status = 'In Progress'
        ");
        $ok = $stmt->execute([$taskId, $staffId]) && $stmt->rowCount() > 0;

        if ($ok) {
            $this->logHistory($taskId, $staffId, 'STATUS_CHANGE', 'In Progress', 'Paused', 'Tạm dừng task');
        }

        return $ok;
    }

    public function resumeTask(int $taskId, int $staffId): bool
    {
        $task = $this->findOwnTask($taskId, $staffId);

        if (!$task || !in_array($task['status'], ['Paused', 'Rejected'], true)) {
            return false;
        }

        $fromStatus = $task['status'];

        $stmt = $this->conn->prepare("
            UPDATE tasks SET status = 'In Progress'
            WHERE task_id = ? AND assignee_id = ? AND status = ?
        ");
        $ok = $stmt->execute([$taskId, $staffId, $fromStatus]) && $stmt->rowCount() > 0;

        if ($ok) {
            $note = $fromStatus === 'Rejected' ? 'Tiếp tục xử lý sau khi bị từ chối' : 'Tiếp tục task';
            $this->logHistory($taskId, $staffId, 'STATUS_CHANGE', $fromStatus, 'In Progress', $note);
        }

        return $ok;
    }

    public function submitForReview(int $taskId, int $staffId): bool
    {
        $task = $this->findOwnTask($taskId, $staffId);

        if (!$task || $task['status'] !== 'In Progress') {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE tasks SET status = 'Review', submitted_at = NOW()
            WHERE task_id = ? AND assignee_id = ? AND status = 'In Progress'
        ");
        $ok = $stmt->execute([$taskId, $staffId]) && $stmt->rowCount() > 0;

        if ($ok) {
            $this->logHistory($taskId, $staffId, 'SUBMIT_REVIEW', 'In Progress', 'Review', 'Gửi yêu cầu Manager duyệt task');
        }

        return $ok;
    }

    /**
     * Staff-editable progress (0-100). Never touches status directly;
     * status changes only happen through the explicit workflow actions above.
     */
    public function updateProgress(int $taskId, int $staffId, int $progress): bool
    {
        $progress = max(0, min(100, $progress));
        $task = $this->findOwnTask($taskId, $staffId);

        if (!$task || !in_array($task['status'], ['In Progress', 'Paused'], true)) {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE tasks SET progress = ?
            WHERE task_id = ? AND assignee_id = ?
        ");
        $ok = $stmt->execute([$progress, $taskId, $staffId]) && $stmt->rowCount() > 0;

        if ($ok) {
            $this->logHistory($taskId, $staffId, 'PROGRESS_UPDATE', (string)$task['progress'], (string)$progress);
        }

        return $ok;
    }
}
