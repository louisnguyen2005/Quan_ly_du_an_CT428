<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$pdo = $db->connect();

$managerId = (int)($_SESSION['user_id'] ?? 0);
if ($managerId <= 0 || (int)($_SESSION['role_id'] ?? 0) !== 2) {
    header('Location:index.php?page=login');
    exit;
}

$manager = manager_fetch_current($pdo, $managerId);

$sidebarCounts = manager_sidebar_counts($pdo, $managerId);
$projectCount = $sidebarCounts['projects'];
$taskCount = $sidebarCounts['tasks'];
$notifCount = $sidebarCounts['notifications'];
$pendingApprovalCount = $sidebarCounts['pending_approvals'];
$notificationPrefs = $sidebarCounts['preferences'];

$currentTheme = $_COOKIE['theme'] ?? 'light';
$currentLang = $_COOKIE['lang'] ?? 'vi';
$success = $_SESSION['settings_success'] ?? '';
$error = $_SESSION['settings_error'] ?? '';
unset($_SESSION['settings_success'], $_SESSION['settings_error']);

manager_page_head('Cài đặt');
?>
<div class="manager-shell">
    <?php manager_sidebar($manager, '', $projectCount, $taskCount, $notifCount, $pendingApprovalCount); ?>

    <main class="manager-main-content">
        <?php manager_topbar('Cài đặt', $manager, $notifCount); ?>

        <section class="manager-content manager-settings-page">
            <div class="manager-page-title-row settings-title">
                <div>
                    <h1>Cài đặt hệ thống</h1>
                    <p>Quản lý thông báo, giao diện và bảo mật tài khoản</p>
                </div>
            </div>

            <?php if ($success || $error): ?>
                <div class="manager-toast <?= $success ? 'success' : 'error' ?>" id="managerToast">
                    <?= mh($success ?: $error) ?>
                </div>
            <?php endif; ?>

            <form class="manager-settings-panel" action="index.php?action=updateNotificationSettings" method="POST" id="managerNotificationSettingsForm" data-ajax-stay>
                <h2><i class="bi bi-bell"></i> Thông báo</h2>

                <div class="manager-setting-row">
                    <div>
                        <h4>Email thông báo</h4>
                        <p>Nhận thông báo qua email</p>
                    </div>
                    <label class="manager-switch"><input type="checkbox" name="email_notifications" value="1" <?= (int)($notificationPrefs['email_notifications'] ?? 1) === 1 ? 'checked' : '' ?>><span></span></label>
                </div>

                <div class="manager-setting-row">
                    <div>
                        <h4>Thông báo nhiệm vụ mới</h4>
                        <p>Nhận thông báo khi được giao nhiệm vụ mới</p>
                    </div>
                    <label class="manager-switch"><input type="checkbox" name="task_notifications" value="1" <?= (int)($notificationPrefs['task_notifications'] ?? 1) === 1 ? 'checked' : '' ?>><span></span></label>
                </div>

                <div class="manager-setting-row">
                    <div>
                        <h4>Thông báo bình luận</h4>
                        <p>Nhận thông báo khi có bình luận mới</p>
                    </div>
                    <label class="manager-switch"><input type="checkbox" name="comment_notifications" value="1" <?= (int)($notificationPrefs['comment_notifications'] ?? 1) === 1 ? 'checked' : '' ?>><span></span></label>
                </div>

                <div class="manager-setting-row">
                    <div>
                        <h4>Nhắc deadline</h4>
                        <p>Nhắc nhở trước khi đến hạn deadline</p>
                    </div>
                    <label class="manager-switch"><input type="checkbox" name="deadline_notifications" value="1" <?= (int)($notificationPrefs['deadline_notifications'] ?? 1) === 1 ? 'checked' : '' ?>><span></span></label>
                </div>
            </form>

            <div class="manager-settings-panel">
                <h2><i class="bi bi-palette"></i> Giao diện</h2>

                <div class="manager-setting-row">
                    <div>
                        <h4>Chế độ hiển thị</h4>
                        <p>Chọn Light hoặc Dark Mode</p>
                    </div>
                    <select id="themeSelect" class="manager-select">
                        <option value="light" <?= $currentTheme === 'light' ? 'selected' : '' ?>>Light Mode</option>
                        <option value="dark" <?= $currentTheme === 'dark' ? 'selected' : '' ?>>Dark Mode</option>
                    </select>
                </div>

                <div class="manager-setting-row">
                    <div>
                        <h4>Ngôn ngữ</h4>
                        <p>Ngôn ngữ hiển thị hệ thống</p>
                    </div>
                    <select id="langSelect" class="manager-select">
                        <option value="vi" <?= $currentLang === 'vi' ? 'selected' : '' ?>>Tiếng Việt</option>
                        <option value="en" <?= $currentLang === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
            </div>

            <div class="manager-settings-panel" id="change-password">
                <h2><i class="bi bi-lock"></i> Bảo mật</h2>

                <form action="index.php?action=changePassword" method="POST" class="manager-password-form">
                    <div class="manager-form-group">
                        <label>Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" required>
                    </div>

                    <div class="manager-form-group">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_password" required>
                    </div>

                    <div class="manager-form-group">
                        <label>Xác nhận mật khẩu</label>
                        <input type="password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="manager-primary-btn">Đổi mật khẩu</button>
                </form>
            </div>
        </section>
    </main>
</div>
<script>
function setCookie(name, value, days = 30) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
}
const themeSelect = document.getElementById('themeSelect');
if (themeSelect) {
    themeSelect.addEventListener('change', () => {
        setCookie('theme', themeSelect.value);
        window.location.reload();
    });
}
const notifForm = document.getElementById('managerNotificationSettingsForm');
if (notifForm) {
    notifForm.querySelectorAll('input[type=checkbox]').forEach(input => {
        input.addEventListener('change', () => {
            notifForm.requestSubmit ? notifForm.requestSubmit() : notifForm.dispatchEvent(new Event("submit", {cancelable:true, bubbles:true}));
        });
    });
}
const managerToast = document.getElementById('managerToast');
if (managerToast) {
    setTimeout(() => {
        managerToast.style.opacity = '0';
        managerToast.style.transform = 'translateX(24px)';
        setTimeout(() => managerToast.remove(), 350);
    }, 2000);
}
const langSelect = document.getElementById('langSelect');
if (langSelect) {
    langSelect.addEventListener('change', () => {
        setCookie('lang', langSelect.value);
        window.location.reload();
    });
}
</script>
<?php manager_layout_script(); ?>
