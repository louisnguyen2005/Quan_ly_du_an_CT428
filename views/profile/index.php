<?php
require_once 'controllers/userController.php';

$userController = new UserController();
$user = $userController->edit($_SESSION['user_id']);

$phone = $user['phone'] ?? '+84 912 345 678';
$address = $user['address'] ?? '123 Đường ABC, Quận 1, TP. Hồ Chí Minh';
$avatarInitial = mb_strtoupper(mb_substr(trim((string)($user['username'] ?? 'U')), 0, 1, 'UTF-8'), 'UTF-8');
$profileSuccess = $_SESSION['profile_success'] ?? '';
$profileError = $_SESSION['profile_error'] ?? '';
unset($_SESSION['profile_success'], $_SESSION['profile_error']);
?>

<div class="page-container profile-page">
    <?php if ($profileSuccess || $profileError): ?>
        <div class="profile-toast <?= $profileSuccess ? 'success' : 'error' ?>" id="profileToast">
            <?= htmlspecialchars($profileSuccess ?: $profileError) ?>
        </div>
    <?php endif; ?>
    <p style="font-size:15px; margin-bottom:24px;">
        Quản lý thông tin và cài đặt tài khoản của bạn
    </p>

    <form method="POST" action="index.php?action=updateProfile">
        <div style="display:grid; grid-template-columns:320px 1fr; gap:24px; align-items:start;">

            <div style="display:flex; flex-direction:column; gap:24px;">
                <div class="card" style="text-align:center; padding:32px 24px;">
                    <div class="profile-avatar-letter"><?= htmlspecialchars($avatarInitial) ?></div>

                    <h3 style="font-size:20px; font-weight:700; margin-bottom:4px;">
                        <?= htmlspecialchars($user['username']) ?>
                    </h3>

                    <p style="font-size:14px; margin-bottom:12px;">User Account</p>

                    <span style="background:#f3e8ff; color:#a855f7; padding:4px 12px; border-radius:20px; font-size:13px; font-weight:600; display:inline-block; margin-bottom:24px;">
                        <?= htmlspecialchars($user['role_name']) ?>
                    </span>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:24px;">
                <div class="card" style="padding:28px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                        <h4 style="font-size:18px; font-weight:700;">Thông tin cá nhân</h4>

                        <button type="submit" class="btn-primary">
                            <i class="fa-regular fa-floppy-disk"></i>
                            Lưu thay đổi
                        </button>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div class="form-group">
                            <label>Họ và tên</label>
                            <input type="text" name="username"
                                   value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" value="<?= htmlspecialchars($user['role_name']) ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email"
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Số điện thoại</label>
                            <input type="text" name="phone"
                                   value="<?= htmlspecialchars($phone) ?>">
                        </div>

                        <div class="form-group">
                            <label>Phòng ban</label>
                            <input type="text" value="IT Department" disabled>
                        </div>

                        <div class="form-group">
                            <label>Vị trí</label>
                            <input type="text" value="<?= htmlspecialchars($user['role_name']) ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label>Ngày tham gia</label>
                            <input type="date" name="created_at"
                                   value="<?= date('Y-m-d', strtotime($user['created_at'])) ?>">
                        </div>
                    </div>
                </div>

                <div class="card" style="padding:28px;">
                    <h4 style="font-size:16px; font-weight:700; margin-bottom:16px;">Địa chỉ</h4>

                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <input type="text" name="address"
                               value="<?= htmlspecialchars($address) ?>">
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
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
const profileToast = document.getElementById('profileToast');
if (profileToast) {
    setTimeout(() => {
        profileToast.style.opacity = '0';
        profileToast.style.transform = 'translateX(24px)';
        setTimeout(() => profileToast.remove(), 350);
    }, 2000);
}
</script>
