<?php

require_once 'controllers/taskController.php';
require_once 'controllers/projectController.php';
require_once 'controllers/userController.php';

$taskController = new TaskController();
$projectController = new ProjectController();
$userController = new UserController();

$tasks = $taskController->index();
$projects = $projectController->index();
$users = $userController->index();

$roleId = $_SESSION['role_id'] ?? 0;

?>

<div class="page-container">

    <div class="tasks-header">
        <div>
            <h1>Task Management</h1>

            <?php if ($roleId == 1): ?>
                <p>Quản lý tất cả nhiệm vụ trong hệ thống</p>
            <?php elseif ($roleId == 2): ?>
                <p>Quản lý nhiệm vụ thuộc dự án của bạn</p>
            <?php else: ?>
                <p>Danh sách nhiệm vụ được giao cho bạn</p>
            <?php endif; ?>
        </div>

        <?php if ($roleId == 1 || $roleId == 2): ?>
            <button id="openTaskModal" class="btn-primary">
                <i class="fa-solid fa-plus"></i>
                Tạo Task
            </button>
        <?php endif; ?>
    </div>

    <div class="task-toolbar">
        <div class="task-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input id="taskSearch" type="text" placeholder="Tìm kiếm task...">
        </div>

        <select id="taskFilter">
            <option value="all">Tất cả trạng thái</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Review">Review</option>
            <option value="Completed">Completed</option>
        </select>
    </div>

    <div class="task-list-container">

        <?php if (empty($tasks)): ?>

            <div class="detail-card">
                <p>Không có nhiệm vụ nào.</p>
            </div>

        <?php else: ?>

            <?php foreach ($tasks as $task): ?>

                <?php
                $assignedName = $task['assigned_name'] ?? 'Unassigned';
                $firstLetter = !empty($assignedName) ? strtoupper(substr($assignedName, 0, 1)) : 'U';
                $status = $task['status'] ?? 'Pending';
                $priority = $task['priority'] ?? 'Medium';
                $progress = (int)($task['progress'] ?? 0);
                ?>

                <div
                    class="task-row-card task-item-clickable"
                    data-id="<?= $task['task_id'] ?>"
                    data-status="<?= htmlspecialchars($status) ?>">

                    <div class="task-left-section">

                        <div class="task-icon-wrapper">
                            <i class="fa-solid fa-list-check"></i>
                        </div>

                        <div class="task-main-content">

                            <h3 class="task-title">
                                <?= htmlspecialchars($task['task_name']) ?>
                            </h3>

                            <p class="task-desc">
                                <?= htmlspecialchars($task['description'] ?? 'Chưa có mô tả') ?>
                            </p>

                            <div class="task-badges-group">
                                <span class="badge badge-status <?= strtolower(str_replace(' ', '-', $status)) ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>

                                <span class="badge badge-priority <?= strtolower($priority) ?>">
                                    <?= htmlspecialchars($priority) ?>
                                </span>

                                <span class="badge badge-project">
                                    <?= htmlspecialchars($task['project_name'] ?? 'Không thuộc dự án') ?>
                                </span>
                            </div>

                            <div class="task-footer-meta">
                                <div class="assignee-box">
                                    <div class="avatar">
                                        <?= htmlspecialchars($firstLetter) ?>
                                    </div>

                                    <span class="user-name">
                                        <?= htmlspecialchars($assignedName) ?>
                                    </span>
                                </div>

                                <div class="date-box">
                                    <i class="fa-regular fa-clock"></i>
                                    <span>
                                        <?= !empty($task['due_date'])
                                            ? date('d/m/Y', strtotime($task['due_date']))
                                            : 'Chưa có deadline' ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($roleId == 3): ?>
                                <form
                                    class="staff-status-form"
                                    method="POST"
                                    action="index.php?action=updateTaskStatus">

                                    <input
                                        type="hidden"
                                        name="task_id"
                                        value="<?= $task['task_id'] ?>">

                                    <select name="status">
                                        <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="In Progress" <?= $status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="Review" <?= $status === 'Review' ? 'selected' : '' ?>>Review</option>
                                        <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>

                                    <button type="submit" class="btn-primary btn-small">
                                        Cập nhật
                                    </button>
                                </form>
                            <?php endif; ?>

                        </div>
                    </div>

                    <div class="task-right-section">
                        <div class="progress-block">
                            <div class="progress-text">
                                <span class="progress-label">Tiến độ</span>
                                <span class="progress-value">
                                    <?= $progress ?>%
                                </span>
                            </div>

                            <div class="progress-bar-bg">
                                <div
                                    class="progress-bar-fill"
                                    style="width: <?= $progress ?>%">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>
</div>

<?php if ($roleId == 1 || $roleId == 2): ?>
    <?php include 'views/tasks/modal.php'; ?>
<?php endif; ?>

<script src="assets/js/tasks.js"></script>
