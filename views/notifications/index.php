<?php

require_once 'controllers/notificationController.php';

$notificationController = new NotificationController();
$notifications = $notificationController->currentRoleNotifications();
$notifications = is_array($notifications) ? $notifications : [];
$roleId = (int)($_SESSION['role_id'] ?? 0);

?>

<div class="page-container">

    <div class="notifications-header">
        <div>
            <h1>Thông báo</h1>

            <?php if ($roleId === 1): ?>
                <p>Tất cả thông báo trong hệ thống</p>
            <?php else: ?>
                <p>Các thông báo mới nhất dành cho bạn</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="notification-toolbar">
        <select id="notificationFilter">
            <option value="all">Tất cả</option>
            <option value="task">Task</option>
            <option value="comment">Comment</option>
            <option value="system">System</option>
        </select>
    </div>

    <div class="notifications-list">

        <?php if (empty($notifications)): ?>

            <div class="notification-card">
                <div class="notification-content">
                    <h4>Chưa có thông báo</h4>
                    <p>Hiện tại chưa có thông báo nào để hiển thị.</p>
                </div>
            </div>

        <?php else: ?>

            <?php foreach ($notifications as $item): ?>
                <?php
                    $type = $item['type'] ?? 'system';
                    $dotClass = 'green';

                    if ($type === 'task') {
                        $dotClass = 'blue';
                    } elseif ($type === 'comment') {
                        $dotClass = 'orange';
                    }
                ?>

                <a
                    href="index.php?action=openNotification&id=<?= (int)$item['notification_id'] ?>&amp;_csrf_token=<?= urlencode(csrf_token()) ?>"
                    class="notification-card <?= ((int)($item['is_read'] ?? 0) === 0) ? 'unread' : '' ?>"
                    data-type="<?= htmlspecialchars($type) ?>"
                    data-read="<?= (int)($item['is_read'] ?? 0) ?>">

                    <div class="notification-icon">
                        <span class="dot <?= $dotClass ?>"></span>
                    </div>

                    <div class="notification-content">
                        <h4><?= htmlspecialchars($item['title'] ?? '') ?></h4>
                        <p><?= htmlspecialchars($item['content'] ?? '') ?></p>

                        <span class="notification-time">
                            <?= !empty($item['created_at'])
                                ? date('d/m/Y H:i', strtotime($item['created_at']))
                                : '' ?>
                        </span>

                        <?php if ($roleId === 1 && !empty($item['receiver_name'])): ?>
                            <span class="notification-time">
                                Người nhận: <?= htmlspecialchars($item['receiver_name']) ?>
                                <?php if (!empty($item['receiver_role'])): ?>
                                    - <?= htmlspecialchars($item['receiver_role']) ?>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>

                </a>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>

<script src="assets/js/notifications.js"></script>
