<?php

$username = $_SESSION['username'] ?? 'User';
$roleName = $_SESSION['role_name'] ?? 'Account';
$userEmail = $_SESSION['email'] ?? '';
$avatarInitial = strtoupper(substr($username ?: 'U', 0, 1));

$topbarNotificationCount = 0;
if ((int)($_SESSION['role_id'] ?? 0) === 1) {
    try {
        require_once __DIR__ . '/../../config/database.php';
        $topbarDb = new Database();
        $topbarPdo = $topbarDb->connect();
        $topbarNotificationCount = (int)$topbarPdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
    } catch (Throwable $e) {
        $topbarNotificationCount = 0;
    }
}

?>

<header class="topbar">
    <div class="search-box global-search-container">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="search" id="adminGlobalSearch" class="global-search-input" autocomplete="off" placeholder="Tìm kiếm dự án, nhiệm vụ...">
        <div class="global-search-results" hidden></div>
    </div>

    <div class="topbar-right">
        <a href="index.php?page=notifications" class="notification" title="Thông báo">
            <i class="fa-regular fa-bell"></i>
            <?php if ($topbarNotificationCount > 0): ?><span class="notification-dot notification-count-badge"><?= $topbarNotificationCount ?></span><?php endif; ?>
        </a>

        <div class="profile" id="profileToggle" style="position: relative; cursor: pointer; user-select: none;">
            <div class="topbar-avatar-initial" aria-label="Avatar"><?= htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8') ?></div>

            <div>
                <h4><?= htmlspecialchars($username) ?></h4>
                <span><?= htmlspecialchars($roleName) ?></span>
            </div>

            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-user-info">
                    <h4><?= htmlspecialchars($username) ?></h4>
                    <p class="dropdown-email">
                        <?= htmlspecialchars($roleName) ?>
                    </p>
                </div>

                <div class="dropdown-divider"></div>

                <ul class="dropdown-menu-links">
                    <li>
                        <a href="index.php?page=profile">
                            <i class="fa-regular fa-user"></i>
                            Hồ sơ cá nhân
                        </a>
                    </li>

                    <li>
                        <a href="index.php?page=settings">
                            <i class="fa-solid fa-gear"></i>
                            Cài đặt
                        </a>
                    </li>

                    <div class="dropdown-divider"></div>

                    <li>
                        <a href="index.php?page=logout" class="logout-link">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const profileToggle = document.getElementById("profileToggle");
    const profileDropdown = document.getElementById("profileDropdown");

    if (profileToggle && profileDropdown) {
        profileToggle.addEventListener("click", function (e) {
            if (e.target.closest('.profile-dropdown')) return;

            profileDropdown.classList.toggle("show");
            e.stopPropagation();
        });

        document.addEventListener("click", function () {
            profileDropdown.classList.remove("show");
        });
    }
});
</script>
<script src="assets/js/global-search.js"></script>
