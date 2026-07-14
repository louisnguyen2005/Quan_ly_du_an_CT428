<?php

$currentPage = $_GET['page'] ?? 'dashboard';
$roleId = $_SESSION['role_id'] ?? 0;

if ($roleId == 1) {
    $dashboardPage = 'dashboard';
    $roleLabel = 'Admin page';
} elseif ($roleId == 2) {
    $dashboardPage = 'manager-dashboard';
    $roleLabel = 'Manager page';
} else {
    $dashboardPage = 'staff-dashboard';
    $roleLabel = 'Staff page';
}

?>

<aside class="sidebar">

    <div class="logo">
        <div class="logo-icon">
            <i class="fa-solid fa-clipboard-list"></i>
        </div>

        <div>
            <h2>CT428</h2>
            <span><?= $roleLabel ?></span>
        </div>
    </div>

    <nav>
        <ul class="sidebar-menu">

            <li>
                <a
                    class="<?= ($currentPage == $dashboardPage) ? 'active' : '' ?>"
                    href="index.php?page=<?= $dashboardPage ?>">
                    <i class="fa-solid fa-table-cells-large"></i>
                    Dashboard
                </a>
            </li>

            <?php if ($roleId == 1): ?>

                <li>
                    <a
                        class="<?= ($currentPage == 'users') ? 'active' : '' ?>"
                        href="index.php?page=users">
                        <i class="fa-solid fa-users"></i>
                        Quản lý User
                    </a>
                </li>

                <li>
                    <a
                        class="<?= ($currentPage == 'roles') ? 'active' : '' ?>"
                        href="index.php?page=roles">
                        <i class="fa-solid fa-shield-halved"></i>
                        Phân quyền
                    </a>
                </li>

            <?php endif; ?>

            <?php if ($roleId == 1 || $roleId == 2): ?>

                <li>
                    <a
                        class="<?= in_array($currentPage, ['projects', 'project-detail']) ? 'active' : '' ?>"
                        href="index.php?page=projects">
                        <i class="fa-solid fa-folder-open"></i>
                        Dự án
                    </a>
                </li>

            <?php endif; ?>

            <li>
                <a
                    class="<?= in_array($currentPage, ['tasks', 'task-detail']) ? 'active' : '' ?>"
                    href="index.php?page=tasks">
                    <i class="fa-solid fa-list-check"></i>
                    Nhiệm vụ
                </a>
            </li>

            <li>
                <a
                    class="<?= ($currentPage == 'notifications') ? 'active' : '' ?>"
                    href="index.php?page=notifications">
                    <i class="fa-regular fa-bell"></i>
                    Thông báo
                </a>
            </li>


        </ul>
    </nav>

    <div class="help-box">
        <h3>Need Help?</h3>
        <p>Check our documentation</p>
        <button>
            View Docs
        </button>
    </div>

</aside>    