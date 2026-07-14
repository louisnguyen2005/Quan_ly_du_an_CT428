<?php

require_once __DIR__ . '/../config/database.php';

class Project
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAll()
    {
        $sql = "
            SELECT
                p.*,
                p.name AS project_name,
                COALESCE(u.username, 'Chưa phân công') AS manager_name,
                COUNT(pm.user_id) AS team_count
            FROM projects p
            LEFT JOIN users u
                ON p.manager_id = u.user_id
            LEFT JOIN project_members pm
                ON p.project_id = pm.project_id
            WHERE p.is_deleted = 0
            GROUP BY p.project_id
            ORDER BY p.project_id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByManager($managerId)
    {
        $sql = "
            SELECT
                p.*,
                p.name AS project_name,
                COALESCE(u.username, 'Chưa phân công') AS manager_name,
                COUNT(pm.user_id) AS team_count
            FROM projects p
            LEFT JOIN users u
                ON p.manager_id = u.user_id
            LEFT JOIN project_members pm
                ON p.project_id = pm.project_id
            WHERE p.is_deleted = 0
              AND p.manager_id = ?
            GROUP BY p.project_id
            ORDER BY p.project_id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$managerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByStaff($staffId)
    {
        $sql = "
            SELECT DISTINCT
                p.*,
                p.name AS project_name,
                COALESCE(u.username, 'Chưa phân công') AS manager_name,
                COUNT(pm.user_id) AS team_count
            FROM projects p
            LEFT JOIN users u
                ON p.manager_id = u.user_id
            LEFT JOIN project_members pm
                ON p.project_id = pm.project_id
            LEFT JOIN tasks t
                ON t.project_id = p.project_id
            WHERE p.is_deleted = 0
              AND (
                    pm.user_id = ?
                    OR t.assignee_id = ?
                  )
            GROUP BY p.project_id
            ORDER BY p.project_id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$staffId, $staffId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $sql = "
            SELECT
                p.*,
                p.name AS project_name,
                COALESCE(u.username, 'Chưa phân công') AS manager_name,
                COUNT(pm.user_id) AS team_count
            FROM projects p
            LEFT JOIN users u
                ON p.manager_id = u.user_id
            LEFT JOIN project_members pm
                ON p.project_id = pm.project_id
            WHERE p.project_id = ?
              AND p.is_deleted = 0
            GROUP BY p.project_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "
            INSERT INTO projects
            (
                project_code,
                name,
                description,
                start_date,
                end_date,
                created_by,
                status,
                priority,
                progress,
                manager_id
            )
            VALUES
            (
                ?,?,?,?,?,?,?,?,?,?
            )
        ";

        $projectCode = $data['project_code'] ?? ('PRJ-' . time());

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $projectCode,
            $data['name'],
            $data['description'] ?? null,
            $data['start_date'] ?? date('Y-m-d'),
            $data['end_date'] ?? null,
            $_SESSION['user_id'],
            $data['status'] ?? 'Planning',
            $data['priority'] ?? 'Medium',
            $data['progress'] ?? 0,
            $data['manager_id'] ?? null
        ]);
    }

    public function update($id, $data)
    {
        $sql = "
            UPDATE projects
            SET
                name = ?,
                description = ?,
                start_date = ?,
                end_date = ?,
                status = ?,
                priority = ?,
                progress = ?,
                manager_id = ?
            WHERE project_id = ?
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['status'] ?? 'Planning',
            $data['priority'] ?? 'Medium',
            $data['progress'] ?? 0,
            $data['manager_id'] ?? null,
            $id
        ]);
    }

    public function delete($id)
    {
        $sql = "
            UPDATE projects
            SET is_deleted = 1
            WHERE project_id = ?
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$id]);
    }

    public function getMembers($projectId)
    {
        $sql = "
            SELECT
                u.user_id,
                u.username,
                u.email,
                r.name AS role_name,
                pm.role_in_project
            FROM project_members pm
            JOIN users u
                ON pm.user_id = u.user_id
            LEFT JOIN roles r
                ON u.role_id = r.role_id
            WHERE pm.project_id = ?
              AND u.is_deleted = 0
            ORDER BY u.username ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$projectId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMember($projectId, $userId, $role)
    {
        $sql = "
            INSERT INTO project_members
            (
                project_id,
                user_id,
                role_in_project
            )
            VALUES
            (
                ?,?,?
            )
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $projectId,
            $userId,
            $role
        ]);
    }

    public function countAll()
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM projects WHERE is_deleted = 0"
        );

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function countByManager($managerId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM projects
             WHERE is_deleted = 0
             AND manager_id = ?"
        );

        $stmt->execute([$managerId]);

        return $stmt->fetchColumn();
    }
}