<?php

require_once __DIR__ . '/../../controllers/dashboardController.php';

$dashboardController = new DashboardController();

$newTasks = $dashboardController->latestTasks(5);
$stats = $dashboardController->adminStats();
$projectChart = $dashboardController->projectStatusChart();
$taskChart = $dashboardController->taskStatusChart();
$projects = $dashboardController->recentProjects(null, null, 5);
$tasks = $dashboardController->recentTasks(null, null, 5);
$notifications = $dashboardController->recentNotifications(null, 4);

?>

<div class="page-container">

    <div class="dashboard-header">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Chào mừng quay trở lại hệ thống quản lý dự án</p>
        </div>
    </div>

    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <h2><?= (int)$stats['users'] ?></h2>
                <p>Tổng người dùng</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <div>
                <h2><?= (int)$stats['projects'] ?></h2>
                <p>Tổng dự án</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <h2><?= (int)$stats['tasks'] ?></h2>
                <p>Tổng nhiệm vụ</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <h2><?= (int)$stats['completion_rate'] ?>%</h2>
                <p>Tỷ lệ hoàn thành</p>
            </div>
        </div>

    </div>

    <div class="chart-grid">

        <div class="chart-card">
            <div class="chart-header">
                <h3>Project Status</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="projectChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Task Overview</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="taskChart"></canvas>
            </div>
        </div>

    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Dự án gần đây</h3>
            <a href="index.php?page=projects" class="btn-primary">Xem tất cả</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tên dự án</th>
                    <th>Manager</th>
                    <th>Tiến độ</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                    <tr>
                        <td colspan="4">Chưa có dự án nào.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <?php
                            $badgeClass = 'badge-blue';
                            if (($project['status'] ?? '') === 'Completed') {
                                $badgeClass = 'badge-green';
                            } elseif (($project['status'] ?? '') === 'Planning') {
                                $badgeClass = 'badge-orange';
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($project['project_name']) ?></td>
                            <td><?= htmlspecialchars($project['manager_name']) ?></td>
                            <td><?= (int)($project['progress'] ?? 0) ?>%</td>
                            <td>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($project['status'] ?? 'Planning') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="dashboard-two-columns">

        <div class="table-card">
            <div class="table-header">
                <h3>Nhiệm vụ mới</h3>

                <a href="index.php?page=tasks" class="btn-primary">
                    Xem task
                </a>
            </div>

            <div class="new-tasks-list">

                <?php if(empty($newTasks)): ?>

                    <p class="new-task-empty">
                        Chưa có nhiệm vụ mới.
                    </p>

                <?php else: ?>

                    <?php foreach($newTasks as $task): ?>

                        <?php
                            $statusClass = strtolower(
                                str_replace(' ', '-', $task['status'] ?? 'pending')
                            );
                        ?>

                        <a
                            href="index.php?page=task-detail&id=<?= $task['task_id'] ?>"
                            class="new-task-item">

                            <div class="new-task-icon">
                                <i class="fa-solid fa-list-check"></i>
                            </div>

                            <div class="new-task-content">
                                <span class="new-task-title">
                                    <?= htmlspecialchars($task['task_name'] ?? '') ?>
                                </span>

                                <span class="new-task-project">
                                    <?= htmlspecialchars($task['project_name'] ?? 'Không có dự án') ?>
                                </span>
                            </div>

                            <span class="new-task-status <?= $statusClass ?>">
                                <?= htmlspecialchars($task['status'] ?? 'Pending') ?>
                            </span>

                        </a>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </div>

        <div class="table-card dashboard-notifications">
            <div class="table-header">
                <h3>Thông báo hệ thống</h3>
                <a href="index.php?page=notifications" class="btn-primary">Xem thêm</a>
            </div>

            <div class="notifications-list dashboard-notification-list">
                <?php if (empty($notifications)): ?>
                    <div class="dashboard-empty">Chưa có thông báo.</div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-card dashboard-notification-card">
                            <div class="notification-icon">
                                <?php if (($notification['type'] ?? '') === 'task'): ?>
                                    <span class="dot blue"></span>
                                <?php elseif (($notification['type'] ?? '') === 'comment'): ?>
                                    <span class="dot orange"></span>
                                <?php else: ?>
                                    <span class="dot green"></span>
                                <?php endif; ?>
                            </div>

                            <div class="notification-content">
                                <h4><?= htmlspecialchars($notification['title']) ?></h4>
                                <p><?= htmlspecialchars($notification['content'] ?? '') ?></p>
                                <?php if (!empty($notification['receiver_name'])): ?>
                                    <small>Người nhận: <?= htmlspecialchars($notification['receiver_name']) ?></small>
                                <?php endif; ?>
                                <span class="notification-time">
                                    <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script>
    window.projectChartData = <?= json_encode($projectChart) ?>;
    window.taskChartData = <?= json_encode($taskChart) ?>;
</script>
<script src="assets/js/dashboard.js"></script>
