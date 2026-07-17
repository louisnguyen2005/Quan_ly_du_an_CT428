<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$roleId = (int)($_SESSION['role_id'] ?? 0);
$username = (string)($_SESSION['username'] ?? 'admin');
$email = (string)($_SESSION['email'] ?? '');
$initial = strtoupper(substr($username ?: 'A', 0, 1));

$adminNotificationCount = 0;
if ($roleId === 1) {
    try {
        require_once __DIR__ . '/../../config/database.php';
        $sidebarDb = new Database();
        $sidebarPdo = $sidebarDb->connect();
        $adminNotificationCount = (int)$sidebarPdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
    } catch (Throwable $e) {
        $adminNotificationCount = 0;
    }
}
?>

<aside class="sidebar projecthub-admin-sidebar">
    <div>
        <div class="projecthub-admin-logo">
            <div class="projecthub-admin-logo-box"><i class="fa-solid fa-briefcase"></i></div>
            <div class="projecthub-admin-logo-text">
                <h2>ProjectHub</h2>
                <span>Admin workspace</span>
            </div>
        </div>

        <nav>
            <ul class="sidebar-menu projecthub-admin-menu">
                <li><a class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard"><i class="fa-solid fa-house"></i><span>Tổng quan</span></a></li>
                <li><a class="<?= in_array($currentPage, ['users','user-create','user-edit'], true) ? 'active' : '' ?>" href="index.php?page=users"><i class="fa-solid fa-users"></i><span>Quản lý người dùng</span></a></li>
                <li><a class="<?= in_array($currentPage, ['roles','role-edit'], true) ? 'active' : '' ?>" href="index.php?page=roles"><i class="fa-solid fa-shield-halved"></i><span>Phân quyền</span></a></li>
                <li><a class="<?= in_array($currentPage, ['projects','project-detail','project-create','project-edit'], true) ? 'active' : '' ?>" href="index.php?page=projects"><i class="fa-regular fa-folder"></i><span>Dự án</span></a></li>
                <li><a class="<?= in_array($currentPage, ['tasks','task-detail','task-create','task-edit'], true) ? 'active' : '' ?>" href="index.php?page=tasks"><i class="fa-regular fa-square-check"></i><span>Nhiệm vụ</span></a></li>
                <li><a class="<?= $currentPage === 'notifications' ? 'active' : '' ?>" href="index.php?page=notifications"><i class="fa-regular fa-bell"></i><span>Thông báo</span><span class="admin-sidebar-badge"><?= $adminNotificationCount ?></span></a></li>
            </ul>
        </nav>
    </div>

    <div class="projecthub-admin-account">
        <div class="projecthub-admin-avatar"><?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="projecthub-admin-account-info">
            <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong>
            <small><?= $email !== '' ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : 'Admin' ?></small>
        </div>
    </div>
</aside>
