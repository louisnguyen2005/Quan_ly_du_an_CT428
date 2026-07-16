<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../../config/database.php';

require_once 'controllers/userController.php';

$userController = new UserController();
$user = $userController->edit($_SESSION['user_id']);

$phone = $user['phone'] ?? '+84 912 345 678';
$address = $user['address'] ?? '123 Đường ABC, Quận 1, TP. Hồ Chí Minh';
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

$profileSuccess = $_SESSION['profile_success'] ?? '';
$profileError = $_SESSION['profile_error'] ?? '';
unset($_SESSION['profile_success'], $_SESSION['profile_error']);

$owned = manager_owned_project_condition('p', ':mid');
$completedStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks t JOIN projects p ON p.project_id = t.project_id WHERE t.is_deleted = 0 AND p.is_deleted = 0 AND t.status = 'Completed' AND $owned");
$completedStmt->execute(['mid' => $managerId]);
$completedCount = (int)$completedStmt->fetchColumn();

manager_page_head('Hồ sơ cá nhân');
?>
<div class="manager-shell">
    <?php manager_sidebar($manager, '', $projectCount, $taskCount, $notifCount); ?>

    <main class="manager-main-content">
        <?php manager_topbar('Hồ sơ cá nhân', $manager, $notifCount); ?>

        <section class="manager-content manager-profile-page">
            <?php if ($profileSuccess || $profileError): ?>
                <div class="manager-toast <?= $profileSuccess ? 'success' : 'error' ?>" id="managerToast">
                    <?= mh($profileSuccess ?: $profileError) ?>
                </div>
            <?php endif; ?>
            <div class="manager-section-heading">
                <p>Quản lý thông tin và cài đặt tài khoản của bạn</p>
            </div>

            <div class="manager-profile-detail-grid">
                <div>
                    <div class="manager-profile-summary-card">
                        <div class="manager-profile-avatar large">
                            <?php if (!empty($manager['avatar'])): ?>
                                <img src="<?= mh($manager['avatar']) ?>" alt="avatar">
                            <?php else: ?>
                                <?= strtoupper(substr($manager['username'] ?? 'M', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <h3><?= mh($manager['username'] ?? 'manager') ?></h3>
                        <p>User Account</p>
                        <span class="manager-role-pill">Manager</span>

                        <button class="manager-primary-btn full-width" type="button">
                            <i class="bi bi-image"></i> Chỉnh sửa ảnh
                        </button>
                    </div>

                    <div class="manager-profile-summary-card compact">
                        <h3>Thống kê</h3>
                        <div class="manager-profile-stat-row">
                            <span>Tổng nhiệm vụ</span>
                            <strong><?= $taskCount + $completedCount ?></strong>
                        </div>
                        <div class="manager-profile-stat-row">
                            <span>Đã hoàn thành</span>
                            <strong class="green-text"><?= $completedCount ?></strong>
                        </div>
                        <div class="manager-profile-stat-row">
                            <span>Đang thực hiện</span>
                            <strong class="blue-text"><?= $taskCount ?></strong>
                        </div>
                    </div>
                </div>

                <div>
                    <form method="POST" action="index.php?action=updateProfile">
                        <div class="manager-profile-card-wide">
            <div class="manager-profile-card-head">
                <h3>Thông tin cá nhân</h3>

                <button class="manager-primary-btn manager-save-profile-btn" type="submit">
                    <i class="bi bi-save"></i> Lưu thay đổi
                </button>
            </div>

            <div class="manager-profile-info-grid">

                <div class="manager-profile-field">
                    <span>Họ và tên</span>
                    <input
                        type="text"
                        name="username"
                        value="<?= mh($manager['username'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="manager-profile-field">
                    <span>Role</span>
                    <input
                        type="text"
                        value="Manager"
                        disabled
                    >
                </div>

                <div class="manager-profile-field">
                    <span>Email</span>
                    <input
                        type="email"
                        name="email"
                        value="<?= mh($manager['email'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="manager-profile-field">
                    <span>Số điện thoại</span>
                    <input
                        type="text"
                        name="phone"
                        value="<?= mh(!empty($manager['phone']) ? $manager['phone'] : $phone) ?>"
                    >
                </div>

                <div class="manager-profile-field">
                    <span>Phòng ban</span>
                    <input
                        type="text"
                        value="IT Department"
                        disabled
                    >
                </div>

                <div class="manager-profile-field">
                    <span>Vị trí</span>
                    <input
                        type="text"
                        value="Manager"
                        disabled
                    >
                </div>

                <div class="manager-profile-field">
                    <span>Ngày tham gia</span>
                    <input
                        type="date"
                        name="created_at"
                        value="<?= !empty($manager['created_at']) ? date('Y-m-d', strtotime($manager['created_at'])) : '' ?>"
                    >
                </div>

            </div>
        </div>

        <div class="manager-profile-card-wide">
            <h3>Địa chỉ</h3>

            <div class="manager-profile-field single">
                <span>Địa chỉ</span>
                <input
                    type="text"
                    name="address"
                    value="<?= mh(!empty($manager['address']) ? $manager['address'] : $address) ?>"
                >
            </div>
        </div>  
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>
<script>
const managerToast = document.getElementById('managerToast');
if (managerToast) {
    setTimeout(() => {
        managerToast.style.opacity = '0';
        managerToast.style.transform = 'translateX(24px)';
        setTimeout(() => managerToast.remove(), 350);
    }, 2000);
}
</script>
<?php manager_layout_script(); ?>
