<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$roleId = (int)($_SESSION['role_id'] ?? 0);
$username = $_SESSION['username'] ?? 'admin';

if ($roleId === 1) {
    $dashboardPage = 'dashboard';
    $workspaceLabel = 'Admin workspace';
} elseif ($roleId === 2) {
    $dashboardPage = 'manager-dashboard';
    $workspaceLabel = 'Manager workspace';
} else {
    $dashboardPage = 'staff-dashboard';
    $workspaceLabel = 'Staff workspace';
}
?>

<aside class="sidebar projecthub-sidebar">
    <div>
        <div class="logo projecthub-logo">
            <div class="logo-icon projecthub-logo-icon">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <div class="projecthub-logo-text">
                <h2>ProjectHub</h2>
                <span><?= htmlspecialchars($workspaceLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>

        <nav>
            <ul class="sidebar-menu projecthub-menu">
                <li>
                    <a class="<?= $currentPage === $dashboardPage ? 'active' : '' ?>"
                       href="index.php?page=<?= htmlspecialchars($dashboardPage, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fa-solid fa-house"></i>
                        <span>Tổng quan</span>
                    </a>
                </li>

                <?php if ($roleId === 1): ?>
                    <li>
                        <a class="<?= $currentPage === 'users' ? 'active' : '' ?>"
                           href="index.php?page=users">
                            <i class="fa-solid fa-users"></i>
                            <span>Quản lý người dùng</span>
                        </a>
                    </li>
                    <li>
                        <a class="<?= $currentPage === 'roles' ? 'active' : '' ?>"
                           href="index.php?page=roles">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Phân quyền</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($roleId === 1 || $roleId === 2): ?>
                    <li>
                        <a class="<?= in_array($currentPage, ['projects', 'project-detail'], true) ? 'active' : '' ?>"
                           href="index.php?page=projects">
                            <i class="fa-regular fa-folder"></i>
                            <span>Dự án</span>
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a class="<?= in_array($currentPage, ['tasks', 'task-detail'], true) ? 'active' : '' ?>"
                       href="index.php?page=tasks">
                        <i class="fa-regular fa-square-check"></i>
                        <span>Nhiệm vụ</span>
                    </a>
                </li>

                <li>
                    <a class="<?= $currentPage === 'notifications' ? 'active' : '' ?>"
                       href="index.php?page=notifications">
                        <i class="fa-regular fa-bell"></i>
                        <span>Thông báo</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="projecthub-admin-user">
        <div class="projecthub-admin-avatar">
            <?= strtoupper(substr((string)$username, 0, 1)) ?>
        </div>
        <div>
            <strong><?= htmlspecialchars((string)$username, ENT_QUOTES, 'UTF-8') ?></strong>
            <small><?= $roleId === 1 ? 'Admin' : ($roleId === 2 ? 'Manager' : 'Staff') ?></small>
        </div>
    </div>
</aside>
