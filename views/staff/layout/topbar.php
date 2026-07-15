<?php
$staffUsername = $_SESSION['username'] ?? 'Staff';
$staffUnreadCount = $unreadNotificationCount ?? 0;
?>
<header class="staff-topbar">
    <button class="staff-sidebar-toggle" id="staffSidebarToggle" type="button" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
    </button>

    <div class="staff-topbar-titles">
        <h1><?= staff_e($pageTitle) ?></h1>
        <?php if ($pageSubtitle): ?>
            <p><?= staff_e($pageSubtitle) ?></p>
        <?php endif; ?>
    </div>

    <div class="staff-topbar-actions">
        <a href="index.php?page=staff-notifications" class="staff-topbar-icon-btn" title="Thông báo">
            <i class="bi bi-bell"></i>
            <?php if ($staffUnreadCount > 0): ?>
                <span class="staff-badge-dot"><?= $staffUnreadCount > 9 ? '9+' : $staffUnreadCount ?></span>
            <?php endif; ?>
        </a>

        <a href="index.php?page=staff-profile" class="staff-topbar-user">
            <span class="staff-avatar"><?= staff_e(mb_substr($staffUsername, 0, 1)) ?></span>
            <span class="staff-topbar-user-name"><?= staff_e($staffUsername) ?></span>
        </a>
    </div>
</header>
