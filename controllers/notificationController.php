<?php

require_once __DIR__ . '/../models/notification.php';

class NotificationController
{
    private $notification;

    public function __construct()
    {
        $this->notification = new Notification();
    }

    public function index()
    {
        return $this->notification->getAll();
    }

    public function userNotifications($userId)
    {
        return $this->notification->getByUser($userId);
    }

    public function adminNotifications()
    {
        return $this->notification->getAll();
    }

    public function currentRoleNotifications()
    {
        $roleId = $_SESSION['role_id'] ?? 0;
        $userId = $_SESSION['user_id'] ?? 0;

        if ($roleId == 1) {
            return $this->notification->getAll();
        }

        return $this->notification->getByUser($userId);
    }


    public function preferences($userId)
    {
        return $this->notification->getPreferences($userId);
    }

    public function updatePreferences($userId, $data)
    {
        return $this->notification->updatePreferences($userId, $data);
    }

    public function unreadCount($userId)
    {
        return $this->notification->unreadCount($userId);
    }

    public function read($id)
    {
        return $this->notification->markRead($id);
    }
}