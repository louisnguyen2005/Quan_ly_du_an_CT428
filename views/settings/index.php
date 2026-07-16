<?php
$settingsSuccess = $_SESSION['settings_success'] ?? '';
$settingsError = $_SESSION['settings_error'] ?? '';
unset($_SESSION['settings_success'], $_SESSION['settings_error']);
?>
<?php
// Báo cho VS Code biết biến $lang và $theme đã được định nghĩa từ file header.php sang
/** @var array $lang */
/** @var string $theme */

// Đọc lại cookie lang để đồng bộ cho thẻ select bên dưới giao diện
$current_lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'vi';
require_once 'controllers/notificationController.php';
$notificationController = new NotificationController();
$notificationPrefs = $notificationController->preferences($_SESSION['user_id']);
?>
<div class="page-container">
    <?php if ($settingsSuccess || $settingsError): ?>
        <div class="profile-toast <?= $settingsSuccess ? 'success' : 'error' ?>" id="settingsToast">
            <?= htmlspecialchars($settingsSuccess ?: $settingsError) ?>
        </div>
    <?php endif; ?>
    <div class="settings-header">
        <h1><?= $lang['sys_settings'] ?></h1>
        <p><?= $lang['settings_desc'] ?></p>
    </div>

    <form class="settings-card" action="index.php?action=updateNotificationSettings" method="POST" id="notificationSettingsForm" data-ajax-stay>
        <h2><i class="fa-regular fa-bell"></i> <?= $lang['notifications'] ?></h2>
        
        <div class="setting-item">
            <div>
                <h4><?= $lang['email_notif'] ?></h4>
                <p><?= $lang['email_desc'] ?></p>
            </div>
            <label class="switch">
                <input type="checkbox" name="email_notifications" value="1" <?= (int)($notificationPrefs['email_notifications'] ?? 1) === 1 ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>

        <div class="setting-item">
            <div>
                <h4><?= isset($lang['task_notif']) ? $lang['task_notif'] : 'Thông báo nhiệm vụ mới' ?></h4>
                <p><?= isset($lang['task_notif_desc']) ? $lang['task_notif_desc'] : 'Nhận thông báo khi được giao nhiệm vụ mới' ?></p>
            </div>
            <label class="switch">
                <input type="checkbox" name="task_notifications" value="1" <?= (int)($notificationPrefs['task_notifications'] ?? 1) === 1 ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>

        <div class="setting-item">
            <div>
                <h4><?= isset($lang['comment_notif']) ? $lang['comment_notif'] : 'Thông báo bình luận' ?></h4>
                <p><?= isset($lang['comment_notif_desc']) ? $lang['comment_notif_desc'] : 'Nhận thông báo khi có bình luận mới' ?></p>
            </div>
            <label class="switch">
                <input type="checkbox" name="comment_notifications" value="1" <?= (int)($notificationPrefs['comment_notifications'] ?? 1) === 1 ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>

        <div class="setting-item">
            <div>
                <h4><?= isset($lang['deadline_notif']) ? $lang['deadline_notif'] : 'Nhắc deadline' ?></h4>
                <p><?= isset($lang['deadline_notif_desc']) ? $lang['deadline_notif_desc'] : 'Nhắc nhở trước khi đến hạn deadline' ?></p>
            </div>
            <label class="switch">
                <input type="checkbox" name="deadline_notifications" value="1" <?= (int)($notificationPrefs['deadline_notifications'] ?? 1) === 1 ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
        </div>

    </form>

    <div class="settings-card">
        <h2><i class="fa-solid fa-palette"></i> <?= $lang['appearance'] ?></h2>
        <div class="setting-item">
            <div>
                <h4><?= $lang['display_mode'] ?></h4>
                <p>Chọn Light hoặc Dark Mode</p>
            </div>
            <select id="themeSelect">
                <option value="light" <?= $theme === 'light' ? 'selected' : '' ?>>Light Mode</option>
                <option value="dark" <?= $theme === 'dark' ? 'selected' : '' ?>>Dark Mode</option>
            </select>
        </div>
        <div class="setting-item">
            <div>
                <h4><?= $lang['language'] ?></h4>
                <p>Ngôn ngữ hiển thị hệ thống</p>
            </div>
            <select id="langSelect">
                <option value="vi" <?= $current_lang === 'vi' ? 'selected' : '' ?>>Tiếng Việt</option>
                <option value="en" <?= $current_lang === 'en' ? 'selected' : '' ?>>English</option>
            </select>
        </div>
    </div>

    <div class="settings-card">
        <h2><i class="fa-solid fa-lock"></i> <?= $lang['security'] ?></h2>
        <form id="changePasswordForm" action="index.php?action=changePassword" method="POST">

        <div class="form-group">
            <label><?= $lang['curr_password'] ?></label>
            <input
                type="password"
                name="current_password"
                required
            >
        </div>

        <div class="form-group">
            <label><?= $lang['new_password'] ?></label>
            <input
                type="password"
                name="new_password"
                required
            >
        </div>

        <div class="form-group">
            <label><?= $lang['conf_password'] ?></label>
            <input
                type="password"
                name="confirm_password"
                required
            >
        </div>

        <button type="submit" class="btn-primary">
            <?= $lang['btn_change_pwd'] ?>
        </button>

        </form> 
    </div>
</div>

<script src="assets/js/settings.js"></script>
<style>
.profile-toast {
    position: fixed;
    top: 88px;
    right: 28px;
    z-index: 9999;
    min-width: 280px;
    padding: 14px 18px;
    border-radius: 14px;
    font-weight: 700;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16);
    transition: all .35s ease;
}
.profile-toast.success { background: #dcfce7; color: #166534; }
.profile-toast.error { background: #fee2e2; color: #991b1b; }
</style>
<script>
const settingsToast = document.getElementById('settingsToast');
if (settingsToast) {
    setTimeout(() => {
        settingsToast.style.opacity = '0';
        settingsToast.style.transform = 'translateX(24px)';
        setTimeout(() => settingsToast.remove(), 350);
    }, 2000);
}
</script>
