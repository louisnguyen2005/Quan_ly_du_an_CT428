<?php
$staffNav = [
    ['page' => 'staff-dashboard',     'icon' => 'bi-house-door',    'label' => 'Dashboard'],
    ['page' => 'staff-tasks',         'icon' => 'bi-list-check',    'label' => 'My Tasks'],
    ['page' => 'staff-projects',      'icon' => 'bi-folder2-open',  'label' => 'My Projects'],
    ['page' => 'staff-notifications', 'icon' => 'bi-bell',          'label' => 'Notifications'],
    ['page' => 'staff-profile',       'icon' => 'bi-person-circle', 'label' => 'My Profile'],
];
?>
<aside class="staff-sidebar" id="staffSidebar">
    <div class="staff-sidebar-brand">
        <div class="staff-sidebar-brand-icon">
            <i class="bi bi-kanban-fill"></i>
        </div>
        <div>
            <h1>CT428 PMS</h1>
            <span>Staff Workspace</span>
        </div>
    </div>

    <nav class="staff-sidebar-nav">
        <?php foreach ($staffNav as $item): ?>
            <a
                href="index.php?page=<?= $item['page'] ?>"
                class="staff-nav-link <?= $staffCurrentPage === $item['page'] ? 'active' : '' ?>">
                <i class="bi <?= $item['icon'] ?>"></i>
                <span><?= staff_e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="staff-sidebar-footer">
        <a href="index.php?page=logout" class="staff-nav-link staff-logout-link">
            <i class="bi bi-box-arrow-left"></i>
            <span>Đăng xuất</span>
        </a>
    </div>
</aside>
