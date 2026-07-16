<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/security.php';

class User
{
    private $conn;
    private string $lastError = '';

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
        $this->ensureStatusColumn();
        $this->ensureProfileColumns();
    }

    private function ensureStatusColumn()
    {
        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM users LIKE 'status'");
            $stmt->execute();

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->exec("ALTER TABLE users ADD status varchar(20) NOT NULL DEFAULT 'active' AFTER role_id");
            }
        } catch (PDOException $e) {
            // Không dừng hệ thống nếu database user không có quyền ALTER.
            // Nếu gặp lỗi, hãy chạy SQL trong project_management.sql bản mới.
        }
    }

    private function ensureProfileColumns()
    {
        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM users LIKE 'phone'");
            $stmt->execute();
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->exec("ALTER TABLE users ADD phone VARCHAR(20) NULL AFTER email");
            }

            $stmt = $this->conn->prepare("SHOW COLUMNS FROM users LIKE 'address'");
            $stmt->execute();
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->exec("ALTER TABLE users ADD address VARCHAR(255) NULL AFTER phone");
            }
        } catch (PDOException $e) {
            // Nếu database không cho ALTER, hãy thêm phone/address bằng phpMyAdmin.
        }
    }

    public function getAll()
    {
        $sql = "
            SELECT
                u.*,
                COALESCE(u.status, 'active') AS status,
                r.name AS role_name,
                r.role_code
            FROM users u
            LEFT JOIN roles r
                ON u.role_id = r.role_id
            WHERE u.is_deleted = 0
            ORDER BY u.user_id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $sql = "
            SELECT
                u.*,
                COALESCE(u.status, 'active') AS status,
                r.name AS role_name,
                r.role_code
            FROM users u
            LEFT JOIN roles r
                ON u.role_id = r.role_id
            WHERE u.user_id = ?
              AND u.is_deleted = 0
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        return $this->find($id);
    }

    public function findByEmail($email)
    {
        $sql = "
            SELECT
                u.*,
                COALESCE(u.status, 'active') AS status,
                r.name AS role_name,
                r.role_code
            FROM users u
            LEFT JOIN roles r
                ON u.role_id = r.role_id
            WHERE u.email = ?
              AND u.is_deleted = 0
              AND COALESCE(u.status, 'active') = 'active'
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    public function create($data)
    {
        try {
            $this->lastError = '';

            $username = trim((string)($data['username'] ?? ''));
            $email = trim((string)($data['email'] ?? ''));
            $password = (string)($data['password'] ?? '');
            $roleId = (int)($data['role_id'] ?? 0);

            if ($username === '' || $email === '' || $password === '' || $roleId <= 0) {
                $this->lastError = 'Vui lòng nhập đầy đủ thông tin người dùng.';
                return false;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->lastError = 'Email không hợp lệ.';
                return false;
            }

            if (!password_is_strong($password)) {
                $this->lastError = password_policy_message();
                return false;
            }

            $check = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND is_deleted = 0");
            $check->execute([$username, $email]);
            if ((int)$check->fetchColumn() > 0) {
                $this->lastError = 'Username hoặc Email đã tồn tại!';
                return false;
            }

            $sql = "
                INSERT INTO users
                (
                    username,
                    email,
                    password,
                    role_id,
                    status,
                    is_deleted
                )
                VALUES
                (
                    ?, ?, ?, ?, 'active', 0
                )
            ";

            $stmt = $this->conn->prepare($sql);
            $ok = $stmt->execute([
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $roleId
            ]);

            if ($ok) {
                $newUserId = (int)$this->conn->lastInsertId();
                $actorId = (int)($_SESSION['user_id'] ?? 0);
                $this->writeAuditLog(
                    $actorId > 0 ? $actorId : null,
                    'CREATE_USER',
                    'users',
                    $newUserId,
                    null,
                    'đã thêm người dùng "' . $username . '"'
                );
            }

            return $ok;

        } catch (PDOException $e) {
            $this->lastError = 'Không thể thêm người dùng. Vui lòng kiểm tra dữ liệu nhập.';
            return false;
        }
    }


    private function writeAuditLog($userId, string $action, string $entity, ?int $entityId, ?int $projectId, string $description): void
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO audit_logs(user_id, action, entity, entity_id, project_id, description) VALUES(?,?,?,?,?,?)");
            $stmt->execute([$userId, $action, $entity, $entityId, $projectId, $description]);
        } catch (PDOException $e) {
            // Audit log không được làm gián đoạn nghiệp vụ chính.
        }
    }

    public function update($id, $data)
    {
        $sql = "
            UPDATE users
            SET
                username = ?,
                email = ?,
                role_id = ?
            WHERE user_id = ?
              AND is_deleted = 0
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            trim($data['username']),
            trim($data['email']),
            (int)$data['role_id'],
            (int)$id
        ]);
    }
    public function updateProfile($id, $data)
    {
        $sql = "
            UPDATE users
            SET
                username = ?,
                email = ?,
                phone = ?,
                address = ?,
                created_at = ?
            WHERE user_id = ?
              AND is_deleted = 0
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            trim($data['username'] ?? ''),
            trim($data['email'] ?? ''),
            trim($data['phone'] ?? ''),
            trim($data['address'] ?? ''),
            !empty($data['created_at']) ? $data['created_at'] : date('Y-m-d'),
            (int)$id
        ]);
    }

    public function delete($id)
    {
        $id = (int)$id;

        if ($id <= 0) {
            return false;
        }

        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $fallbackUserId = $this->getFallbackUserId($id);

            if (!$fallbackUserId) {
                $this->conn->rollBack();
                return false;
            }

            $this->exec("UPDATE projects SET manager_id = NULL WHERE manager_id = ?", [$id]);
            $this->exec("UPDATE projects SET created_by = ? WHERE created_by = ?", [$fallbackUserId, $id]);

            $this->exec("UPDATE tasks SET assignee_id = NULL WHERE assignee_id = ?", [$id]);
            $this->exec("UPDATE tasks SET created_by = ? WHERE created_by = ?", [$fallbackUserId, $id]);

            $this->exec("UPDATE attachments SET uploaded_by = ? WHERE uploaded_by = ?", [$fallbackUserId, $id]);
            $this->exec("UPDATE audit_logs SET user_id = NULL WHERE user_id = ?", [$id]);

            $this->exec("DELETE FROM project_members WHERE user_id = ?", [$id]);
            $this->exec("DELETE FROM notifications WHERE user_id = ?", [$id]);
            $this->exec("DELETE FROM comments WHERE user_id = ?", [$id]);

            $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ?");
            $result = $stmt->execute([$id]);

            $this->conn->commit();
            return $result;

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return false;
        }
    }

    private function getFallbackUserId($deletedUserId)
    {
        $stmt = $this->conn->prepare("
            SELECT user_id
            FROM users
            WHERE user_id <> ?
              AND is_deleted = 0
              AND role_id = 1
            ORDER BY user_id ASC
            LIMIT 1
        ");
        $stmt->execute([$deletedUserId]);
        $adminId = $stmt->fetchColumn();

        if ($adminId) {
            return (int)$adminId;
        }

        $stmt = $this->conn->prepare("
            SELECT user_id
            FROM users
            WHERE user_id <> ?
              AND is_deleted = 0
            ORDER BY user_id ASC
            LIMIT 1
        ");
        $stmt->execute([$deletedUserId]);
        $userId = $stmt->fetchColumn();

        return $userId ? (int)$userId : null;
    }

    private function exec($sql, array $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateRole($userId, $roleId)
    {
        $sql = "
            UPDATE users
            SET role_id = ?
            WHERE user_id = ?
              AND is_deleted = 0
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            (int)$roleId,
            (int)$userId
        ]);
    }

    public function toggleStatus($userId)
    {
        $sql = "
            UPDATE users
            SET status =
                CASE
                    WHEN COALESCE(status, 'active') = 'active'
                    THEN 'inactive'
                    ELSE 'active'
                END
            WHERE user_id = ?
              AND is_deleted = 0
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([(int)$userId]);
    }

    public function updatePassword($id, $hash)
    {
        $sql = "
            UPDATE users
            SET password = ?
            WHERE user_id = ?
              AND is_deleted = 0
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $hash,
            (int)$id
        ]);
    }

    public function updateRefreshToken(int $id, string $refreshTokenHash, string $expiredAt): bool
    {
        $sql = "
            UPDATE users
            SET refresh_token = ?,
                token_expired_at = ?
            WHERE user_id = ?
              AND is_deleted = 0
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $refreshTokenHash,
            $expiredAt,
            $id
        ]);
    }

    public function clearRefreshToken(int $id): bool
    {
        $sql = "
            UPDATE users
            SET refresh_token = NULL,
                token_expired_at = NULL
            WHERE user_id = ?
              AND is_deleted = 0
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$id]);
    }

    public function findByRefreshToken(string $refreshTokenHash)
    {
        $sql = "
            SELECT
                u.*,
                COALESCE(u.status, 'active') AS status,
                r.name AS role_name,
                r.role_code
            FROM users u
            LEFT JOIN roles r
                ON u.role_id = r.role_id
            WHERE u.refresh_token = ?
              AND u.token_expired_at IS NOT NULL
              AND u.token_expired_at > NOW()
              AND u.is_deleted = 0
              AND COALESCE(u.status, 'active') = 'active'
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$refreshTokenHash]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
