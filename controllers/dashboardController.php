<?php

require_once __DIR__ . '/../config/database.php';

class DashboardController
{
    private PDO $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function adminStats(): array
    {
        $users = $this->count("SELECT COUNT(*) FROM users WHERE is_deleted = 0");
        $projects = $this->count("SELECT COUNT(*) FROM projects WHERE is_deleted = 0");
        $tasks = $this->count("SELECT COUNT(*) FROM tasks WHERE is_deleted = 0");
        $completed = $this->count("SELECT COUNT(*) FROM tasks WHERE is_deleted = 0 AND status = 'Completed'");

        return [
            'users' => $users,
            'projects' => $projects,
            'tasks' => $tasks,
            'completed' => $completed,
            'completion_rate' => $tasks > 0 ? round(($completed / $tasks) * 100) : 0
        ];
    }

    public function managerStats(int $managerId): array
    {
        $projects = $this->count(
            "SELECT COUNT(*) FROM projects WHERE is_deleted = 0 AND manager_id = ?",
            [$managerId]
        );

        $tasks = $this->count(
            "SELECT COUNT(*)
             FROM tasks t
             JOIN projects p ON t.project_id = p.project_id
             WHERE t.is_deleted = 0
               AND p.is_deleted = 0
               AND p.manager_id = ?",
            [$managerId]
        );

        $completed = $this->count(
            "SELECT COUNT(*)
             FROM tasks t
             JOIN projects p ON t.project_id = p.project_id
             WHERE t.is_deleted = 0
               AND p.is_deleted = 0
               AND p.manager_id = ?
               AND t.status = 'Completed'",
            [$managerId]
        );

        $review = $this->count(
            "SELECT COUNT(*)
             FROM tasks t
             JOIN projects p ON t.project_id = p.project_id
             WHERE t.is_deleted = 0
               AND p.is_deleted = 0
               AND p.manager_id = ?
               AND t.status = 'Review'",
            [$managerId]
        );

        return [
            'projects' => $projects,
            'tasks' => $tasks,
            'completed' => $completed,
            'review' => $review,
            'completion_rate' => $tasks > 0 ? round(($completed / $tasks) * 100) : 0
        ];
    }

    public function staffStats(int $staffId): array
    {
        $tasks = $this->count(
            "SELECT COUNT(*) FROM tasks WHERE is_deleted = 0 AND assignee_id = ?",
            [$staffId]
        );

        $doing = $this->count(
            "SELECT COUNT(*) FROM tasks WHERE is_deleted = 0 AND assignee_id = ? AND status = 'In Progress'",
            [$staffId]
        );

        $completed = $this->count(
            "SELECT COUNT(*) FROM tasks WHERE is_deleted = 0 AND assignee_id = ? AND status = 'Completed'",
            [$staffId]
        );

        $overdue = $this->count(
            "SELECT COUNT(*)
             FROM tasks
             WHERE is_deleted = 0
               AND assignee_id = ?
               AND due_date IS NOT NULL
               AND due_date < CURDATE()
               AND status <> 'Completed'",
            [$staffId]
        );

        return [
            'tasks' => $tasks,
            'doing' => $doing,
            'completed' => $completed,
            'overdue' => $overdue,
            'completion_rate' => $tasks > 0 ? round(($completed / $tasks) * 100) : 0
        ];
    }

    public function projectStatusChart(?int $managerId = null): array
    {
        if ($managerId !== null) {
            $sql = "
                SELECT status, COUNT(*) total
                FROM projects
                WHERE is_deleted = 0
                  AND manager_id = ?
                GROUP BY status
            ";

            return $this->fetchAll($sql, [$managerId]);
        }

        return $this->fetchAll(
            "SELECT status, COUNT(*) total
             FROM projects
             WHERE is_deleted = 0
             GROUP BY status"
        );
    }

    public function taskStatusChart(?int $managerId = null, ?int $staffId = null): array
    {
        if ($managerId !== null) {
            $sql = "
                SELECT t.status, COUNT(*) total
                FROM tasks t
                JOIN projects p ON t.project_id = p.project_id
                WHERE t.is_deleted = 0
                  AND p.is_deleted = 0
                  AND p.manager_id = ?
                GROUP BY t.status
            ";

            return $this->fetchAll($sql, [$managerId]);
        }

        if ($staffId !== null) {
            $sql = "
                SELECT status, COUNT(*) total
                FROM tasks
                WHERE is_deleted = 0
                  AND assignee_id = ?
                GROUP BY status
            ";

            return $this->fetchAll($sql, [$staffId]);
        }

        return $this->fetchAll(
            "SELECT status, COUNT(*) total
             FROM tasks
             WHERE is_deleted = 0
             GROUP BY status"
        );
    }

    public function recentProjects(?int $managerId = null, ?int $staffId = null, int $limit = 5): array
    {
        if ($managerId !== null) {
            $sql = "
                SELECT
                    p.*,
                    p.name AS project_name,
                    COALESCE(u.username, 'Chưa phân công') AS manager_name,
                    COUNT(pm.user_id) AS team_count
                FROM projects p
                LEFT JOIN users u ON p.manager_id = u.user_id
                LEFT JOIN project_members pm ON p.project_id = pm.project_id
                WHERE p.is_deleted = 0
                  AND p.manager_id = ?
                GROUP BY p.project_id
                ORDER BY p.project_id DESC
                LIMIT $limit
            ";

            return $this->fetchAll($sql, [$managerId]);
        }

        if ($staffId !== null) {
            $sql = "
                SELECT DISTINCT
                    p.*,
                    p.name AS project_name,
                    COALESCE(u.username, 'Chưa phân công') AS manager_name,
                    COUNT(pm.user_id) AS team_count
                FROM projects p
                LEFT JOIN users u ON p.manager_id = u.user_id
                LEFT JOIN project_members pm ON p.project_id = pm.project_id
                LEFT JOIN tasks t ON p.project_id = t.project_id
                WHERE p.is_deleted = 0
                  AND (pm.user_id = ? OR t.assignee_id = ?)
                GROUP BY p.project_id
                ORDER BY p.project_id DESC
                LIMIT $limit
            ";

            return $this->fetchAll($sql, [$staffId, $staffId]);
        }

        $sql = "
            SELECT
                p.*,
                p.name AS project_name,
                COALESCE(u.username, 'Chưa phân công') AS manager_name,
                COUNT(pm.user_id) AS team_count
            FROM projects p
            LEFT JOIN users u ON p.manager_id = u.user_id
            LEFT JOIN project_members pm ON p.project_id = pm.project_id
            WHERE p.is_deleted = 0
            GROUP BY p.project_id
            ORDER BY p.project_id DESC
            LIMIT $limit
        ";

        return $this->fetchAll($sql);
    }

    public function recentTasks(?int $managerId = null, ?int $staffId = null, int $limit = 5): array
    {
        $base = "
            SELECT
                t.*,
                t.title AS task_name,
                p.name AS project_name,
                COALESCE(a.username, 'Unassigned') AS assigned_name,
                COALESCE(c.username, 'System') AS creator_name,
                CASE
                    WHEN t.status = 'Completed' THEN 100
                    WHEN t.status = 'Review' THEN 80
                    WHEN t.status = 'In Progress' THEN 50
                    ELSE 0
                END AS progress
            FROM tasks t
            LEFT JOIN projects p ON t.project_id = p.project_id
            LEFT JOIN users a ON t.assignee_id = a.user_id
            LEFT JOIN users c ON t.created_by = c.user_id
        ";

        if ($managerId !== null) {
            return $this->fetchAll(
                $base . "
                WHERE t.is_deleted = 0
                  AND p.is_deleted = 0
                  AND p.manager_id = ?
                ORDER BY t.due_date ASC, t.task_id DESC
                LIMIT $limit",
                [$managerId]
            );
        }

        if ($staffId !== null) {
            return $this->fetchAll(
                $base . "
                WHERE t.is_deleted = 0
                  AND t.assignee_id = ?
                ORDER BY t.due_date ASC, t.task_id DESC
                LIMIT $limit",
                [$staffId]
            );
        }

        return $this->fetchAll(
            $base . "
            WHERE t.is_deleted = 0
            ORDER BY t.task_id DESC
            LIMIT $limit"
        );
    }

    public function recentNotifications(?int $userId = null, int $limit = 4): array
    {
        if ($userId !== null) {
            $sql = "
                SELECT
                    n.*,
                    u.username AS receiver_name,
                    r.name AS receiver_role
                FROM notifications n
                LEFT JOIN users u ON n.user_id = u.user_id
                LEFT JOIN roles r ON u.role_id = r.role_id
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC
                LIMIT $limit
            ";

            return $this->fetchAll($sql, [$userId]);
        }

        $sql = "
            SELECT
                n.*,
                u.username AS receiver_name,
                r.name AS receiver_role
            FROM notifications n
            LEFT JOIN users u ON n.user_id = u.user_id
            LEFT JOIN roles r ON u.role_id = r.role_id
            ORDER BY n.created_at DESC
            LIMIT $limit
        ";

        return $this->fetchAll($sql);
    }
    public function latestTasks($limit = 5)
        {
            $sql = "
                SELECT
                    t.*,
                    t.title AS task_name,
                    p.name AS project_name,
                    u.username AS assigned_name
                FROM tasks t
                LEFT JOIN projects p
                    ON t.project_id = p.project_id
                LEFT JOIN users u
                    ON t.assignee_id = u.user_id
                WHERE t.is_deleted = 0
                ORDER BY t.task_id DESC
                LIMIT ?
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    private function count(string $sql, array $params = []): int
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
