<?php
require_once 'controllers/userController.php';

$userController = new UserController();
$users = $userController->index();
?>

<div class="users-role-card">
    <div class="users-header">
        <div class="header-titles">
            <h2>Người dùng theo vai trò</h2>
            <p>Phân loại danh sách thành viên và điều chỉnh quyền hạn hệ thống</p>
        </div>
        <button id="openPermissionModal" class="btn-edit-global">
            <i class="fa-solid fa-user-gear"></i> Chỉnh sửa quyền
        </button>
    </div>

    <?php
    $groupRoles = [];
    foreach($users as $user){
        if(!in_array($user['role_name'], $groupRoles)){
            $groupRoles[] = $user['role_name'];
        }
    }
    ?>

    <?php foreach($groupRoles as $role): ?>
    <div class="role-section">
        <h3 class="role-title">
            <span class="role-dot"></span>
            <?= htmlspecialchars($role) ?> 
            <span class="role-count">(<?= count(array_filter($users, fn($u) => $u['role_name'] === $role)) ?> người)</span>
        </h3>

        <div class="user-grid">
            <?php foreach($users as $user): ?>
                <?php if($user['role_name'] === $role): ?>
                
                <div class="user-card" data-id="<?= $user['user_id'] ?>">
                    <label class="user-select">
                        <input
                            type="radio"
                            name="selectedUser"
                            class="user-radio"
                            value="<?= $user['user_id'] ?>"
                            data-id="<?= $user['user_id'] ?>"
                            data-name="<?= htmlspecialchars($user['username']) ?>"
                            data-email="<?= htmlspecialchars($user['email']) ?>"
                            data-roleid="<?= $user['role_id'] ?>">
                        <span></span>
                    </label>

                    <div class="avt-detail">
                        <div class="avatar">
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        </div>
                        <div class="user-details">
                            <h4><?= htmlspecialchars($user['username']) ?></h4>
                            <div class="user-email">
                                <i class="far fa-envelope"></i>
                                <span><?= htmlspecialchars($user['email']) ?></span>
                            </div>
                            <span class="role-badge badge-<?= strtolower($role) ?>">
                                <?= htmlspecialchars($user['role_name']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'views/roles/modal.php'; ?>
<script src="assets/js/roles.js"></script>