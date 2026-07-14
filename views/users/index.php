<?php
require_once 'controllers/userController.php';

$userController = new UserController();
$users = $userController->index();
?>

<div class="container users-page">
    <header class="manag">
        <div class="header-titles">
            <h2>Quản lý người dùng</h2>
            <p>Quản lý tài khoản và phân quyền người dùng</p>
        </div>
        <button class="btn-add" id="openUserModal">
            <i class="fas fa-plus"></i> Thêm người dùng
        </button>
    </header>

    <div class="toolbar">
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchUser" placeholder="Tìm kiếm theo tên hoặc email...">
        </div>
        <div class="filter-box">
            <select id="roleFilter">
                <option value="all">Tất cả vai trò</option>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
                <option value="staff">Staff</option>
            </select>
        </div>
    </div>

    <div class="user-grid">
        <?php foreach ($users as $user): ?>
            <?php
                $status = $user['status'] ?? 'active';
                $roleName = $user['role_name'] ?? 'Staff';
                $isActive = $status === 'active';
            ?>
            <div class="user-card <?= $isActive ? '' : 'user-card-locked' ?>" data-role="<?= strtolower($roleName) ?>">
                <div class="status-dot <?= $isActive ? 'status-active' : 'status-inactive' ?>"></div>

                <div class="card-main-content">
                    <div class="avatar">
                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                    </div>

                    <div class="user-details">
                        <h3 class="user-name"><?= htmlspecialchars($user['username']) ?></h3>
                        <p class="job-title"><?= htmlspecialchars($roleName) ?></p>

                        <div class="badge-group">
                            <span class="badge badge-<?= strtolower($roleName) ?>">
                                <?= htmlspecialchars($roleName) ?>
                            </span>

                            <?php if ($isActive): ?>
                                <span class="badge badge-green">Active</span>
                            <?php else: ?>
                                <span class="badge badge-red">Đã khóa</span>
                            <?php endif; ?>
                        </div>

                        <div class="card-body-info">
                            <div class="contact-info">
                                <i class="far fa-envelope"></i>
                                <span><?= htmlspecialchars($user['email']) ?></span>
                            </div>
                            <div class="contact-info">
                                <i class="fa-solid fa-user-tag"></i>
                                <span><?= $isActive ? 'Đang hoạt động' : 'Tài khoản đã bị khóa' ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <?php if ($isActive): ?>
                        <a href="index.php?page=edit-user&id=<?= $user['user_id'] ?>" class="btn-action btn-edit">
                            <i class="far fa-edit"></i> Sửa
                        </a>
                    <?php else: ?>
                        <button class="btn-action btn-edit" disabled>
                            <i class="far fa-edit"></i> Đã khóa
                        </button>
                    <?php endif; ?>

                    <a href="index.php?action=toggleUserStatus&id=<?= $user['user_id'] ?>" class="btn-action btn-lock" data-confirm="Bạn có chắc muốn <?= $isActive ? 'khóa' : 'mở khóa' ?> tài khoản này?">
                        <?= $isActive ? '🔒 Khóa' : '🔓 Mở khóa' ?>
                    </a>

                    <?php if ($isActive): ?>
                        <a href="index.php?action=deleteUser&id=<?= $user['user_id'] ?>"
                           data-confirm="Bạn có chắc muốn xóa vĩnh viễn người dùng này khỏi database?"
                           class="btn-action btn-delete">
                            <i class="far fa-trash-alt"></i>
                        </a>
                    <?php else: ?>
                        <button class="btn-action btn-delete" disabled title="Mở khóa tài khoản trước nếu muốn xóa">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'views/users/modal.php'; ?>
<script src="assets/js/users.js"></script>
