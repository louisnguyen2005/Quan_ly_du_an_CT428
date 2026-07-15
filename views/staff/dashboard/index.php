<?php

require_once __DIR__ . '/../../../controllers/staffDashboardController.php';
require_once __DIR__ . '/../../staff/helpers.php';

$vm = (new StaffDashboardController())->buildViewModel();

if ($vm === null) {
    header('Location:index.php?page=login');
    exit;
}

$counters      = $vm['counters'];
$deadlines     = $vm['upcoming_deadlines'];
$recentTasks   = $vm['recent_tasks'];
$notifications = $vm['notifications'];
$activities    = $vm['activities'];
$projects      = $vm['projects'];

$unreadNotificationCount = count(array_filter($notifications, fn($n) => (int)$n['is_read'] === 0));

$pageTitle    = 'Dashboard';
$pageSubtitle = 'Đây là những việc bạn nên làm hôm nay';
$extraCss     = ['assets/css/staff/dashboard.css'];
$extraJs      = ['assets/js/staff/dashboard.js'];

include __DIR__ . '/../layout/head.php';
?>

<div class="staff-cards-grid">
    <div class="staff-stat-card">
        <div class="staff-stat-icon bg-primary-soft text-primary"><i class="bi bi-calendar-day"></i></div>
        <div>
            <span class="staff-stat-value"><?= (int)$counters['today_tasks'] ?></span>
            <span class="staff-stat-label">Task hôm nay</span>
        </div>
    </div>

    <div class="staff-stat-card">
        <div class="staff-stat-icon bg-info-soft text-info"><i class="bi bi-arrow-repeat"></i></div>
        <div>
            <span class="staff-stat-value"><?= (int)$counters['in_progress_tasks'] ?></span>
            <span class="staff-stat-label">Đang thực hiện</span>
        </div>
    </div>

    <div class="staff-stat-card">
        <div class="staff-stat-icon bg-success-soft text-success"><i class="bi bi-check2-circle"></i></div>
        <div>
            <span class="staff-stat-value"><?= (int)$counters['completed_tasks'] ?></span>
            <span class="staff-stat-label">Hoàn thành</span>
        </div>
    </div>

    <div class="staff-stat-card">
        <div class="staff-stat-icon bg-danger-soft text-danger"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
            <span class="staff-stat-value"><?= (int)$counters['overdue_tasks'] ?></span>
            <span class="staff-stat-label">Quá hạn</span>
        </div>
    </div>
</div>

<div class="staff-dashboard-columns">

    <div class="staff-col-main">

        <section class="staff-panel">
            <div class="staff-panel-header">
                <h2><i class="bi bi-hourglass-split"></i> Deadline sắp tới</h2>
                <a href="index.php?page=staff-tasks" class="staff-link-more">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>

            <?php if (empty($deadlines)): ?>
                <p class="staff-empty">Không có deadline nào trong 7 ngày tới. 🎉</p>
            <?php else: ?>
                <div class="staff-list">
                    <?php foreach ($deadlines as $task): [$daysLabel, $daysClass] = staff_days_remaining_label($task['due_date']); ?>
                        <a href="index.php?page=task-detail&id=<?= (int)$task['task_id'] ?>" class="staff-list-item">
                            <div class="staff-list-item-main">
                                <span class="staff-badge staff-badge-<?= staff_priority_class($task['priority']) ?>"><?= staff_priority_label($task['priority']) ?></span>
                                <div>
                                    <strong><?= staff_e($task['title']) ?></strong>
                                    <small><?= staff_e($task['project_name'] ?? 'Không có dự án') ?></small>
                                </div>
                            </div>
                            <div class="staff-list-item-side">
                                <div class="staff-progress-mini">
                                    <div class="staff-progress-mini-bar" style="width: <?= (int)$task['progress'] ?>%"></div>
                                </div>
                                <span class="staff-badge staff-badge-<?= $daysClass ?>"><?= $daysLabel ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="staff-panel">
            <div class="staff-panel-header">
                <h2><i class="bi bi-clock-history"></i> Task được giao gần đây</h2>
                <a href="index.php?page=staff-tasks" class="staff-link-more">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>

            <?php if (empty($recentTasks)): ?>
                <p class="staff-empty">Bạn chưa được giao task nào.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table staff-table align-middle">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Dự án</th>
                                <th>Trạng thái</th>
                                <th>Ưu tiên</th>
                                <th>Deadline</th>
                                <th>Tiến độ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTasks as $task): ?>
                                <tr onclick="window.location='index.php?page=task-detail&id=<?= (int)$task['task_id'] ?>'">
                                    <td><?= staff_e($task['title']) ?></td>
                                    <td><?= staff_e($task['project_name'] ?? '--') ?></td>
                                    <td><span class="staff-badge staff-badge-<?= staff_status_class($task['status']) ?>"><?= staff_status_label($task['status']) ?></span></td>
                                    <td><span class="staff-badge staff-badge-<?= staff_priority_class($task['priority']) ?>"><?= staff_priority_label($task['priority']) ?></span></td>
                                    <td><?= staff_format_date($task['due_date']) ?></td>
                                    <td style="min-width:120px">
                                        <div class="staff-progress-mini">
                                            <div class="staff-progress-mini-bar" style="width: <?= (int)$task['progress'] ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </div>

    <div class="staff-col-side">

        <section class="staff-panel">
            <div class="staff-panel-header">
                <h2><i class="bi bi-graph-up"></i> Tiến độ dự án của tôi</h2>
            </div>

            <?php if (empty($projects)): ?>
                <p class="staff-empty">Bạn chưa tham gia dự án nào.</p>
            <?php else: ?>
                <?php foreach ($projects as $project):
                    $total = (int)$project['total_tasks'];
                    $done  = (int)$project['completed_tasks'];
                ?>
                    <div class="staff-project-mini">
                        <div class="staff-project-mini-head">
                            <strong><?= staff_e($project['name']) ?></strong>
                            <span><?= (int)$project['project_progress'] ?>%</span>
                        </div>
                        <div class="staff-progress-mini">
                            <div class="staff-progress-mini-bar" style="width: <?= (int)$project['project_progress'] ?>%"></div>
                        </div>
                        <small><?= $done ?>/<?= $total ?> task hoàn thành &middot; Hạn <?= staff_format_date($project['end_date']) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="staff-panel">
            <div class="staff-panel-header">
                <h2><i class="bi bi-bell"></i> Thông báo gần đây</h2>
                <a href="index.php?page=staff-notifications" class="staff-link-more">Xem tất cả</a>
            </div>

            <?php if (empty($notifications)): ?>
                <p class="staff-empty">Không có thông báo mới.</p>
            <?php else: ?>
                <div class="staff-list">
                    <?php foreach ($notifications as $n): ?>
                        <div class="staff-notif-item <?= (int)$n['is_read'] === 0 ? 'unread' : '' ?>">
                            <i class="bi bi-dot"></i>
                            <div>
                                <strong><?= staff_e($n['title']) ?></strong>
                                <small><?= staff_time_ago($n['created_at']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="staff-panel">
            <div class="staff-panel-header">
                <h2><i class="bi bi-activity"></i> Hoạt động gần đây</h2>
            </div>

            <?php if (empty($activities)): ?>
                <p class="staff-empty">Chưa có hoạt động nào được ghi nhận.</p>
            <?php else: ?>
                <ul class="staff-timeline">
                    <?php foreach ($activities as $a): ?>
                        <li>
                            <span class="staff-timeline-dot"></span>
                            <div>
                                <p><strong><?= staff_e($a['actor_name'] ?? 'Hệ thống') ?></strong> — <?= staff_e($a['note'] ?: $a['action_type']) ?></p>
                                <small><?= staff_e($a['task_title']) ?> &middot; <?= staff_time_ago($a['created_at']) ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

    </div>
</div>

<?php include __DIR__ . '/../layout/foot.php'; ?>
