<?php

require_once __DIR__ . '/../../../controllers/staffTaskController.php';
require_once __DIR__ . '/../../staff/helpers.php';

$staffTaskController = new StaffTaskController();
$vm = $staffTaskController->buildViewModel($_GET);

if ($vm === null) {
    header('Location:index.php?page=login');
    exit;
}

$filters  = $vm['filters'];
$tasks    = $vm['tasks'];
$counts   = $vm['counts'];
$projects = $vm['projects'];

$flash = $_SESSION['staff_task_flash'] ?? null;
unset($_SESSION['staff_task_flash']);

$pageTitle    = 'My Tasks';
$pageSubtitle = 'Toàn bộ nhiệm vụ được giao cho bạn';
$extraCss     = ['assets/css/staff/tasks.css'];
$extraJs      = ['assets/js/staff/tasks.js'];

$tabs = [
    'all'            => 'Tất cả',
    'todo'           => 'Cần làm',
    'in_progress'    => 'Đang thực hiện',
    'waiting_review' => 'Chờ duyệt',
    'completed'      => 'Hoàn thành',
];

include __DIR__ . '/../layout/head.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= staff_e($flash['type']) ?> staff-flash" role="alert">
        <?= staff_e($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="staff-tabs">
    <?php foreach ($tabs as $key => $label): ?>
        <a
            href="index.php?page=staff-tasks&tab=<?= $key ?>"
            class="staff-tab <?= $filters['tab'] === $key ? 'active' : '' ?>">
            <?= $label ?>
            <span class="staff-tab-count"><?= (int)($counts[$key] ?? 0) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<form method="get" action="index.php" class="staff-filter-bar">
    <input type="hidden" name="page" value="staff-tasks">
    <input type="hidden" name="tab" value="<?= staff_e($filters['tab']) ?>">

    <div class="staff-filter-search">
        <i class="bi bi-search"></i>
        <input type="text" name="search" placeholder="Tìm task theo tên..." value="<?= staff_e($filters['search']) ?>">
    </div>

    <select name="project_id" class="form-select staff-filter-select">
        <option value="">Tất cả dự án</option>
        <?php foreach ($projects as $p): ?>
            <option value="<?= (int)$p['project_id'] ?>" <?= (string)$filters['project_id'] === (string)$p['project_id'] ? 'selected' : '' ?>>
                <?= staff_e($p['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="priority" class="form-select staff-filter-select">
        <option value="">Mọi độ ưu tiên</option>
        <?php foreach (['Critical', 'High', 'Medium', 'Low'] as $p): ?>
            <option value="<?= $p ?>" <?= $filters['priority'] === $p ? 'selected' : '' ?>><?= staff_priority_label($p) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="sort" class="form-select staff-filter-select">
        <option value="deadline" <?= $filters['sort'] === 'deadline' ? 'selected' : '' ?>>Sắp xếp: Deadline</option>
        <option value="status" <?= $filters['sort'] === 'status' ? 'selected' : '' ?>>Sắp xếp: Trạng thái</option>
    </select>

    <button type="submit" class="btn staff-btn-primary">Lọc</button>
    <a href="index.php?page=staff-tasks" class="btn staff-btn-ghost">Xóa lọc</a>
</form>

<?php if (empty($tasks)): ?>
    <div class="staff-panel">
        <p class="staff-empty">Không có task nào phù hợp với bộ lọc hiện tại.</p>
    </div>
<?php else: ?>
    <div class="staff-task-grid">
        <?php foreach ($tasks as $task):
            [$daysLabel, $daysClass] = staff_days_remaining_label($task['due_date']);
            $status = $task['status'];
        ?>
            <div class="staff-task-card">
                <a href="index.php?page=task-detail&id=<?= (int)$task['task_id'] ?>" class="staff-task-card-link">
                    <div class="staff-task-card-top">
                        <span class="staff-badge staff-badge-<?= staff_priority_class($task['priority']) ?>"><?= staff_priority_label($task['priority']) ?></span>
                        <span class="staff-badge staff-badge-<?= staff_status_class($status) ?>"><?= staff_status_label($status) ?></span>
                    </div>

                    <h3><?= staff_e($task['title']) ?></h3>
                    <p class="staff-task-project"><i class="bi bi-folder2"></i> <?= staff_e($task['project_name'] ?? 'Không có dự án') ?></p>

                    <?php if ($status === 'Rejected' && !empty($task['review_note'])): ?>
                        <p class="staff-task-reject-note"><i class="bi bi-exclamation-circle"></i> Manager từ chối: <?= staff_e($task['review_note']) ?></p>
                    <?php endif; ?>

                    <div class="staff-progress-mini staff-progress-lg">
                        <div class="staff-progress-mini-bar" style="width: <?= (int)$task['progress'] ?>%"></div>
                    </div>
                    <span class="staff-task-progress-label"><?= (int)$task['progress'] ?>% hoàn thành</span>

                    <div class="staff-task-meta">
                        <span><i class="bi bi-calendar-event"></i> Giao: <?= staff_format_date($task['created_at']) ?></span>
                        <span><i class="bi bi-clock"></i> Ước tính: <?= $task['estimated_hours'] ? $task['estimated_hours'] . 'h' : '--' ?></span>
                        <span class="staff-badge staff-badge-<?= $daysClass ?>"><?= staff_format_date($task['due_date']) ?> &middot; <?= $daysLabel ?></span>
                    </div>
                </a>

                <div class="staff-task-actions" onclick="event.stopPropagation()">
                    <?php if ($status === 'Pending'): ?>
                        <form method="post" action="index.php?action=staffStartTask">
                            <input type="hidden" name="task_id" value="<?= (int)$task['task_id'] ?>">
                            <button type="submit" class="btn staff-btn-primary btn-sm"><i class="bi bi-play-fill"></i> Bắt đầu</button>
                        </form>
                    <?php elseif ($status === 'In Progress'): ?>
                        <form method="post" action="index.php?action=staffPauseTask">
                            <input type="hidden" name="task_id" value="<?= (int)$task['task_id'] ?>">
                            <button type="submit" class="btn staff-btn-ghost btn-sm"><i class="bi bi-pause-fill"></i> Tạm dừng</button>
                        </form>
                        <form method="post" action="index.php?action=staffSubmitForReview" onsubmit="return confirm('Gửi task này cho Manager duyệt?');">
                            <input type="hidden" name="task_id" value="<?= (int)$task['task_id'] ?>">
                            <button type="submit" class="btn staff-btn-success btn-sm"><i class="bi bi-send-check"></i> Gửi duyệt</button>
                        </form>
                    <?php elseif ($status === 'Paused' || $status === 'Rejected'): ?>
                        <form method="post" action="index.php?action=staffResumeTask">
                            <input type="hidden" name="task_id" value="<?= (int)$task['task_id'] ?>">
                            <button type="submit" class="btn staff-btn-primary btn-sm"><i class="bi bi-play-fill"></i> Tiếp tục</button>
                        </form>
                    <?php elseif ($status === 'Review'): ?>
                        <span class="staff-task-waiting"><i class="bi bi-hourglass-split"></i> Đang chờ Manager duyệt</span>
                    <?php else: ?>
                        <span class="staff-task-waiting text-success"><i class="bi bi-check2-circle"></i> Đã hoàn thành</span>
                    <?php endif; ?>

                    <a href="index.php?page=task-detail&id=<?= (int)$task['task_id'] ?>" class="btn staff-btn-ghost btn-sm">Chi tiết</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/foot.php'; ?>
