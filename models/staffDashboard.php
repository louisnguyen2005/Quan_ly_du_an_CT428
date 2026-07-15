<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Read-only aggregation model for the Staff Dashboard.
 * Every query is scoped to a single staff user (assignee_id / user_id),
 * so a staff member can never see another staff member's data.
 */
class StaffDashboardModel
{
    private PDO $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Core counters used by the dashboard cards.
     */
    public function getCounters(int $staffId): array
    {
        $sql = "
            SELECT
                SUM(CASE WHEN due_date = CURDATE() AND status <> 'Completed' THEN 1 ELSE 0 END) AS today_tasks,
                SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress_tasks,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_tasks,
                SUM(CASE WHEN due_date < CURDATE() AND status <> 'Completed' THEN 1 ELSE 0 END) AS overdue_tasks,
                COUNT(*) AS total_tasks
            FROM tasks
            WHERE assignee_id = ?
              AND is_deleted = 0
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$staffId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'today_tasks'       => (int)($row['today_tasks'] ?? 0),
            'in_progress_tasks' => (int)($row['in_progress_tasks'] ?? 0),
            'completed_tasks'   => (int)($row['completed_tasks'] ?? 0),
            'overdue_tasks'     => (int)($row['overdue_tasks'] ?? 0),
            'total_tasks'       => (int)($row['total_tasks'] ?? 0),
        ];
    }

    /**
     * Tasks due within the next $days days (excluding overdue/completed),
     * soonest first.
     */
    public function getUpcomingDeadlines(int $staffId, int $days = 7, int $limit = 5): array
    {
        $sql = "
            SELECT
                t.task_id, t.title, t.due_date, t.priority, t.status, t.progress,
                p.name AS project_name
            FROM tasks t
            LEFT JOIN projects p ON p.project_id = t.project_id
            WHERE t.assignee_id = ?
              AND t.is_deleted = 0
              AND t.status <> 'Completed'
              AND t.due_date IS NOT NULL
              AND t.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY t.due_date ASC
            LIMIT ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $staffId, PDO::PARAM_INT);
        $stmt->bindValue(2, $days, PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Most recently assigned tasks (newest first).
     */
    public function getRecentAssignedTasks(int $staffId, int $limit = 5): array
    {
        $sql = "
            SELECT
                t.task_id, t.title, t.status, t.priority, t.progress,
                t.due_date, t.created_at,
                p.name AS project_name
            FROM tasks t
            LEFT JOIN projects p ON p.project_id = t.project_id
            WHERE t.assignee_id = ?
              AND t.is_deleted = 0
            ORDER BY t.created_at DESC
            LIMIT ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $staffId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recent notifications for this staff member.
     */
    public function getRecentNotifications(int $staffId, int $limit = 5): array
    {
        $sql = "
            SELECT notification_id, type, title, content, is_read, created_at
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $staffId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recent activity feed: history rows for tasks assigned to this staff
     * member, falling back gracefully if task_history has no rows yet
     * (e.g. right after migration, before any task action is recorded).
     */
    public function getRecentActivities(int $staffId, int $limit = 6): array
    {
        $sql = "
            SELECT
                h.action_type, h.from_value, h.to_value, h.note, h.created_at,
                t.title AS task_title, t.task_id,
                u.username AS actor_name
            FROM task_history h
            JOIN tasks t ON t.task_id = h.task_id
            LEFT JOIN users u ON u.user_id = h.user_id
            WHERE t.assignee_id = ?
              AND t.is_deleted = 0
            ORDER BY h.created_at DESC
            LIMIT ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $staffId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Projects this staff member belongs to, with task completion stats.
     */
    public function getMyProjectsProgress(int $staffId, int $limit = 4): array
    {
        $sql = "
            SELECT
                p.project_id, p.name, p.end_date, p.progress AS project_progress,
                COUNT(t.task_id) AS total_tasks,
                SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) AS completed_tasks
            FROM project_members pm
            JOIN projects p ON p.project_id = pm.project_id AND p.is_deleted = 0
            LEFT JOIN tasks t ON t.project_id = p.project_id AND t.is_deleted = 0
            WHERE pm.user_id = ?
            GROUP BY p.project_id, p.name, p.end_date, p.progress
            ORDER BY p.end_date ASC
            LIMIT ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $staffId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
