<?php

$username = $_SESSION['username'] ?? 'User';
$roleName = $_SESSION['role_name'] ?? 'Account';
$userEmail = $_SESSION['email'] ?? '';

?>

<header class="topbar">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Tìm kiếm dự án, nhiệm vụ...">
    </div>

    <div class="topbar-right">
        <a href="index.php?page=notifications" class="notification" title="Thông báo">
            <i class="fa-regular fa-bell"></i>
            <span class="notification-dot"></span>
        </a>

        <div class="profile" id="profileToggle" style="position: relative; cursor: pointer; user-select: none;">
            <img src="assets/images/abc.png" alt="avatar">

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
