<?php

require_once __DIR__ . '/../config/database.php';

class Notification
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
        $this->ensurePreferencesTable();
    }

    private function ensurePreferencesTable()
    {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS notification_preferences (
            user_id INT PRIMARY KEY,
            email_notifications TINYINT(1) NOT NULL DEFAULT 1,
            task_notifications TINYINT(1) NOT NULL DEFAULT 1,
            comment_notifications TINYINT(1) NOT NULL DEFAULT 1,
            deadline_notifications TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_notification_preferences_user_model
                FOREIGN KEY (user_id) REFERENCES users(user_id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function getPreferences($userId)
    {
        $stmt = $this->conn->prepare("INSERT IGNORE INTO notification_preferences(user_id) VALUES(?)");
        $stmt->execute([$userId]);

        $stmt = $this->conn->prepare("SELECT * FROM notification_preferences WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'email_notifications' => 1,
            'task_notifications' => 1,
            'comment_notifications' => 1,
            'deadline_notifications' => 1,
        ];
    }

    public function updatePreferences($userId, $data)
    {
        $this->getPreferences($userId);

        $stmt = $this->conn->prepare("\n            UPDATE notification_preferences\n            SET\n                email_notifications = ?,\n                task_notifications = ?,\n                comment_notifications = ?,\n                deadline_notifications = ?\n            WHERE user_id = ?\n        ");

        return $stmt->execute([
            isset($data['email_notifications']) ? 1 : 0,
            isset($data['task_notifications']) ? 1 : 0,
            isset($data['comment_notifications']) ? 1 : 0,
            isset($data['deadline_notifications']) ? 1 : 0,
            $userId
        ]);
    }

    private function shouldReceive($userId, $type)
    {
        $prefs = $this->getPreferences($userId);
        $type = strtolower((string)$type);

        if (str_contains($type, 'task') && (int)$prefs['task_notifications'] === 0) {
            return false;
        }

        if (str_contains($type, 'comment') && (int)$prefs['comment_notifications'] === 0) {
            return false;
        }

        if ((str_contains($type, 'deadline') || str_contains($type, 'due')) && (int)$prefs['deadline_notifications'] === 0) {
            return false;
        }

        return true;
    }

    private function visibleTypeFilter($userId, $alias = 'n')
    {
        $prefs = $this->getPreferences($userId);
        $hidden = [];

        $conditions = [];

        if ((int)$prefs['task_notifications'] === 0) {
            $conditions[] = "LOWER(COALESCE($alias.type, '')) LIKE '%task%'";
        }

        if ((int)$prefs['comment_notifications'] === 0) {
            $conditions[] = "LOWER(COALESCE($alias.type, '')) LIKE '%comment%'";
        }

        if ((int)$prefs['deadline_notifications'] === 0) {
            $conditions[] = "(LOWER(COALESCE($alias.type, '')) LIKE '%deadline%' OR LOWER(COALESCE($alias.type, '')) LIKE '%due%')";
        }

        if (empty($conditions)) {
            return '';
        }

        return " AND NOT (" . implode(' OR ', $conditions) . ") ";
    }

    public function getAll()
    {
        $sql = "
            SELECT
                n.*,
                u.username AS receiver_name,
                r.name AS receiver_role
            FROM notifications n
            LEFT JOIN users u
                ON n.user_id = u.user_id
            LEFT JOIN roles r
                ON u.role_id = r.role_id
            ORDER BY n.created_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser($userId)
    {
        $filter = $this->visibleTypeFilter($userId, 'n');

        $sql = "
            SELECT
                n.*,
                u.username AS receiver_name,
                r.name AS receiver_role
            FROM notifications n
            LEFT JOIN users u
                ON n.user_id = u.user_id
            LEFT JOIN roles r
                ON u.role_id = r.role_id
            WHERE n.user_id = ?
            $filter
            ORDER BY n.created_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($userId, $title, $type, $content)
    {
        if (!$this->shouldReceive($userId, $type)) {
            return true;
        }

        $sql = "
            INSERT INTO notifications
            (
                user_id,
                title,
                type,
                content,
                is_read,
                created_at
            )
            VALUES
            (
                ?, ?, ?, ?, 0, NOW()
            )
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $userId,
            $title,
            $type,
            $content
        ]);
    }

    public function unreadCount($userId)
    {
        $filter = $this->visibleTypeFilter($userId, 'n');

        $stmt = $this->conn->prepare("\n            SELECT COUNT(*)\n            FROM notifications n\n            WHERE n.user_id = ?\n              AND n.is_read = 0\n              $filter\n        ");
        $stmt->execute([$userId]);

        return (int)$stmt->fetchColumn();
    }

    public function markRead($id)
    {
        $sql = "
            UPDATE notifications
            SET is_read = 1
            WHERE notification_id = ?
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$id]);
    }

    /**
     * Đánh dấu TẤT CẢ thông báo chưa đọc của 1 user thành đã đọc. Dùng khi user mở trang
     * "Thông báo" - để badge đỏ ở sidebar/topbar biến mất ngay, cho tới khi có thông báo mới
     * (is_read=0) được tạo ra thì đếm lại từ đầu.
     */
    public function markAllRead($userId)
    {
        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");

        return $stmt->execute([$userId]);
    }
}
