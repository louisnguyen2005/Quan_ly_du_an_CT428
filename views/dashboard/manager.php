<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../manager/_layout.php';

function manager_h($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function manager_date($date)
{
    return $date ? date('d/m/Y', strtotime($date)) : '--';
}

function manager_status_label($status)
{
    return match ($status) {
        'Pending' => 'Chờ xử lý',
        'In Progress' => 'Đang thực hiện',
        'Review' => 'Đang review',
        'Completed' => 'Hoàn thành',
        'Planning' => 'Đang lập kế hoạch',
        default => $status ?: 'Chưa rõ',
    };
}

function manager_priority_label($priority)
{
    return match ($priority) {
        'Critical' => 'Khẩn cấp',
        'High' => 'Cao',
        'Medium' => 'Trung bình',
        'Low' => 'Thấp',
        default => $priority ?: 'Chưa rõ',
    };
}

function manager_badge_class($value)
{
    return match ($value) {
        'Completed' => 'success',
        'In Progress' => 'primary',
        'Review' => 'warning',
        'Pending' => 'muted',
        'Planning' => 'orange',
        'Critical' => 'danger',
        'High' => 'danger',
        'Medium' => 'orange',
        'Low' => 'success',
        default => 'muted',
    };
}

$db = new Database();
$pdo = $db->connect();

$managerId = (int)($_SESSION['user_id'] ?? 0);

if ($managerId <= 0 || (int)($_SESSION['role_id'] ?? 0) !== 2) {
    header('Location:index.php?page=login');
    exit;
}

$stmt = $pdo->prepare("\n    SELECT u.*, r.name AS role_name\n    FROM users u\n    LEFT JOIN roles r ON u.role_id = r.role_id\n    WHERE u.user_id = ? AND u.is_deleted = 0\n    LIMIT 1\n");
$stmt->execute([$managerId]);
$manager = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$sidebarCounts = manager_sidebar_counts($pdo, $managerId);
$sidebarProjectCount = $sidebarCounts['projects'];
$sidebarTaskCount = $sidebarCounts['tasks'];
$sidebarNotifCount = $sidebarCounts['notifications'];
$sidebarPendingApprovalCount = $sidebarCounts['pending_approvals'];
$notificationPrefs = $sidebarCounts['preferences'];

$managedProjectWhere = "(p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n    SELECT 1\n    FROM project_members pmx\n    WHERE pmx.project_id = p.project_id\n      AND pmx.user_id = :manager_member\n      AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n))";

function manager_scope_params(int $managerId): array
{
    return [
        'manager_created' => $managerId,
        'manager_managed' => $managerId,
        'manager_member' => $managerId,
    ];
}

function manager_count(PDO $pdo, string $sql, int $managerId): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute(manager_scope_params($managerId));
    return (int)$stmt->fetchColumn();
}

$totalProjects = manager_count($pdo, "\n    SELECT COUNT(*)\n    FROM projects p\n    WHERE p.is_deleted = 0\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n", $managerId);

$activeProjects = manager_count($pdo, "\n    SELECT COUNT(*)\n    FROM projects p\n    WHERE p.is_deleted = 0\n      AND p.status IN ('Planning', 'In Progress')\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n", $managerId);

$totalMembers = manager_count($pdo, "\n    SELECT COUNT(DISTINCT pm.user_id)\n    FROM project_members pm\n    JOIN projects p ON p.project_id = pm.project_id\n    WHERE p.is_deleted = 0\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n", $managerId);

$totalTasks = manager_count($pdo, "\n    SELECT COUNT(*)\n    FROM tasks t\n    JOIN projects p ON p.project_id = t.project_id\n    WHERE t.is_deleted = 0\n      AND p.is_deleted = 0\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n", $managerId);

$completedTasks = manager_count($pdo, "\n    SELECT COUNT(*)\n    FROM tasks t\n    JOIN projects p ON p.project_id = t.project_id\n    WHERE t.is_deleted = 0\n      AND p.is_deleted = 0\n      AND t.status = 'Completed'\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n", $managerId);

$pendingTasks = manager_count($pdo, "\n    SELECT COUNT(*)\n    FROM tasks t\n    JOIN projects p ON p.project_id = t.project_id\n    WHERE t.is_deleted = 0\n      AND p.is_deleted = 0\n      AND t.status <> 'Completed'\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n", $managerId);

$urgentTasks = manager_count($pdo, "\n    SELECT COUNT(*)\n    FROM tasks t\n    JOIN projects p ON p.project_id = t.project_id\n    WHERE t.is_deleted = 0\n      AND p.is_deleted = 0\n      AND t.status <> 'Completed'\n      AND t.due_date IS NOT NULL\n      AND t.due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n", $managerId);

$completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

$stmt = $pdo->prepare("\n    SELECT t.status, COUNT(*) AS total\n    FROM tasks t\n    JOIN projects p ON p.project_id = t.project_id\n    WHERE t.is_deleted = 0\n      AND p.is_deleted = 0\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n    GROUP BY t.status\n");
$stmt->execute(manager_scope_params($managerId));
$statusRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$statusMap = ['Pending' => 0, 'In Progress' => 0, 'Review' => 0, 'Completed' => 0];
foreach ($statusRows as $row) {
    $statusMap[$row['status']] = (int)$row['total'];
}

$stmt = $pdo->prepare("\n    SELECT\n        t.task_id, t.title, t.priority, t.status, t.due_date,\n        COALESCE(u.username, 'Unassigned') AS username,\n        DATEDIFF(t.due_date, CURDATE()) AS days_left\n    FROM tasks t\n    JOIN projects p ON p.project_id = t.project_id\n    LEFT JOIN users u ON u.user_id = t.assignee_id\n    WHERE t.is_deleted = 0\n      AND p.is_deleted = 0\n      AND t.status <> 'Completed'\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n    ORDER BY t.due_date ASC, t.priority DESC\n    LIMIT 6\n");
$stmt->execute(manager_scope_params($managerId));
$upcomingTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("\n    SELECT COALESCE(u.username, 'Unassigned') AS username, COUNT(t.task_id) AS total_tasks\n    FROM tasks t\n    JOIN projects p ON p.project_id = t.project_id\n    LEFT JOIN users u ON u.user_id = t.assignee_id\n    WHERE t.is_deleted = 0\n      AND p.is_deleted = 0\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n    GROUP BY t.assignee_id, u.username\n    ORDER BY total_tasks DESC\n    LIMIT 8\n");
$stmt->execute(manager_scope_params($managerId));
$workloadData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("\n    SELECT\n        p.*,\n        COALESCE(u.username, 'Chưa rõ') AS creator_name,\n        (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.project_id AND t.is_deleted = 0) AS total_tasks,\n        (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.project_id AND t.status = 'Completed' AND t.is_deleted = 0) AS completed_tasks\n    FROM projects p\n    LEFT JOIN users u ON u.user_id = p.created_by\n    WHERE p.is_deleted = 0\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n    ORDER BY p.created_at DESC\n");
$stmt->execute(manager_scope_params($managerId));
$projectList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("\n    SELECT\n        t.*,\n        p.name AS project_name,\n        COALESCE(u.username, 'Unassigned') AS assignee_name\n    FROM tasks t\n    JOIN projects p ON p.project_id = t.project_id\n    LEFT JOIN users u ON u.user_id = t.assignee_id\n    WHERE t.is_deleted = 0\n      AND p.is_deleted = 0\n      AND (p.created_by = :manager_created OR p.manager_id = :manager_managed OR EXISTS (\n            SELECT 1 FROM project_members pmx\n            WHERE pmx.project_id = p.project_id\n              AND pmx.user_id = :manager_member\n              AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader')\n      ))\n    ORDER BY t.due_date ASC, t.task_id DESC\n");
$stmt->execute(manager_scope_params($managerId));
$taskList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$notificationFilter = manager_notification_filter_sql($notificationPrefs, 'n');
$stmt = $pdo->prepare("\n    SELECT n.*\n    FROM notifications n\n    WHERE n.user_id = ?\n    $notificationFilter\n    ORDER BY n.created_at DESC\n    LIMIT 10\n");
$stmt->execute([$managerId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
$notificationCount = $sidebarNotifCount;

$stmt = $pdo->prepare("\n    SELECT a.*, COALESCE(u.username, 'Bạn') AS username\n    FROM audit_logs a\n    LEFT JOIN users u ON u.user_id = a.user_id\n    WHERE a.user_id = ?\n       OR EXISTS (\n            SELECT 1\n            FROM projects p\n            WHERE p.project_id = a.project_id\n              AND (p.created_by = ? OR p.manager_id = ?)\n       )\n    ORDER BY a.created_at DESC\n    LIMIT 5\n");
$stmt->execute([$managerId, $managerId, $managerId]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$theme = $_COOKIE['theme'] ?? 'light';
$currentLang = $_COOKIE['lang'] ?? 'vi';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - ProjectHub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/dashboard_manager.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="<?= $theme === 'dark' ? 'dark-mode' : '' ?>">
    <div class="manager-shell">
        <?php manager_sidebar($manager, 'overview', $sidebarProjectCount, $sidebarTaskCount, $sidebarNotifCount, $sidebarPendingApprovalCount); ?>

        <main class="manager-main-content">
            <header class="manager-topbar">
                <div class="manager-topbar-left">
                    <h3>Tổng quan</h3>
                </div>
                <div class="manager-topbar-right">
                    <div class="manager-search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="managerSearch" placeholder="Tìm kiếm dự án, nhiệm vụ...">
                    </div>
                    <a class="manager-notification-btn" href="index.php?page=notifications">
                        <i class="bi bi-bell"></i>
                        <?php if ($sidebarNotifCount > 0): ?>
                            <span class="manager-notification-badge"><?= $sidebarNotifCount ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="manager-profile-menu" id="managerProfileToggle">
                        <div class="manager-profile-avatar"><?= strtoupper(substr($manager['username'] ?? 'M', 0, 1)) ?>
                        </div>
                        <div class="manager-profile-meta">
                            <strong><?= manager_h($manager['username'] ?? 'manager') ?></strong>
                            <span>Manager</span>
                        </div>

                        <div class="manager-profile-dropdown" id="managerProfileDropdown">
                            <div class="manager-dropdown-head">
                                <strong><?= manager_h($manager['username'] ?? 'manager') ?></strong>
                                <span>Manager</span>
                            </div>
                            <a href="index.php?page=manager-profile"><i class="bi bi-person"></i> Hồ sơ cá nhân</a>
                            <a href="index.php?page=manager-settings"><i class="bi bi-gear"></i> Cài đặt</a>
                            <a href="index.php?page=logout" class="logout"><i class="bi bi-box-arrow-right"></i> Đăng
                                xuất</a>
                        </div>
                    </div>
                </div>
            </header>

            <section class="manager-content" id="overview">
                <?php if ($flashSuccess): ?><div class="manager-alert success"><?= manager_h($flashSuccess) ?></div>
                <?php endif; ?>
                <?php if ($flashError): ?><div class="manager-alert error"><?= manager_h($flashError) ?></div>
                <?php endif; ?>

                <div class="manager-stats-grid">
                    <div class="manager-modern-stat-card">
                        <div class="manager-modern-top">
                            <div class="manager-modern-icon blue"><i class="bi bi-folder-fill"></i></div>
                            <div>
                                <h6>Tổng dự án</h6>
                                <h2><?= $totalProjects ?></h2><span>Dự án</span>
                            </div>
                        </div>
                        <div class="manager-modern-footer blue-bg"><i
                                class="bi bi-folder"></i><span><?= $activeProjects ?> dự án đang triển khai</span></div>
                    </div>

                    <div class="manager-modern-stat-card">
                        <div class="manager-modern-top">
                            <div class="manager-modern-icon green"><i class="bi bi-people-fill"></i></div>
                            <div>
                                <h6>Thành viên</h6>
                                <h2><?= $totalMembers ?></h2><span>Người dùng</span>
                            </div>
                        </div>
                        <div class="manager-modern-footer green-bg"><i
                                class="bi bi-person-check"></i><span><?= $totalMembers ?> thành viên trong dự án</span>
                        </div>
                    </div>

                    <div class="manager-modern-stat-card">
                        <div class="manager-modern-top">
                            <div class="manager-modern-icon orange"><i class="bi bi-list-task"></i></div>
                            <div>
                                <h6>Tổng nhiệm vụ</h6>
                                <h2><?= $totalTasks ?></h2><span>Nhiệm vụ</span>
                            </div>
                        </div>
                        <div class="manager-modern-footer orange-bg"><i
                                class="bi bi-exclamation-circle"></i><span><?= $pendingTasks ?> nhiệm vụ chưa hoàn
                                thành</span></div>
                    </div>

                    <div class="manager-modern-stat-card">
                        <div class="manager-modern-top">
                            <div class="manager-modern-icon purple"><i class="bi bi-check-circle-fill"></i></div>
                            <div>
                                <h6>Tỷ lệ hoàn thành</h6>
                                <h2><?= $completionRate ?>%</h2><span><?= $completedTasks ?>/<?= $totalTasks ?> nhiệm
                                    vụ</span>
                            </div>
                        </div>
                        <div class="manager-progress-line">
                            <div style="width:<?= $completionRate ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="manager-dashboard-row">
                    <div class="manager-dashboard-card">
                        <div class="manager-card-header-custom">
                            <h4>Tiến độ dự án</h4><span>Theo trạng thái nhiệm vụ</span><i class="bi bi-bar-chart"></i>
                        </div>
                        <div class="manager-chart-wrapper"><canvas id="statusChart"></canvas></div>
                    </div>

                    <div class="manager-dashboard-card">
                        <div class="manager-card-header-custom">
                            <h4>Tiến độ công việc</h4><a href="index.php?page=tasks">Xem tất cả</a>
                        </div>
                        <table class="manager-task-table">
                            <thead>
                                <tr>
                                    <th>Nhiệm vụ</th>
                                    <th>Người phụ trách</th>
                                    <th>Hạn hoàn thành</th>
                                    <th>Ưu tiên</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($upcomingTasks)): ?>
                                    <tr>
                                        <td colspan="4" class="manager-empty-row">Không có task cần theo dõi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($upcomingTasks as $task): ?>
                                        <tr>
                                            <td><?= manager_h($task['title']) ?></td>
                                            <td><?= manager_h($task['username']) ?></td>
                                            <td class="deadline">
                                                <?= manager_date($task['due_date']) ?><br>
                                                <?php if ((int)$task['days_left'] < 0): ?>
                                                    <small class="overdue">Quá hạn <?= abs((int)$task['days_left']) ?> ngày</small>
                                                <?php elseif ((int)$task['days_left'] === 0): ?>
                                                    <small class="today">Đến hạn hôm nay</small>
                                                <?php else: ?>
                                                    <small class="remaining">Còn <?= (int)$task['days_left'] ?> ngày</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><span
                                                    class="manager-status-pill <?= manager_badge_class(((int)$task['days_left'] < 0) ? 'Critical' : $task['priority']) ?>"><?= ((int)$task['days_left'] < 0) ? 'Quá hạn' : manager_priority_label($task['priority']) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="manager-dashboard-row">
                    <div class="manager-dashboard-card">
                        <div class="manager-card-header-custom">
                            <h4>Workload nhân sự <small>(Số nhiệm vụ đang phụ trách)</small></h4>
                        </div>
                        <div class="manager-chart-wrapper"><canvas id="workloadChart"></canvas></div>
                    </div>

                    <div class="manager-dashboard-card">
                        <div class="manager-card-header-custom">
                            <h4><i class="bi bi-activity me-2"></i>Hoạt động gần đây</h4>
                        </div>
                        <div class="manager-activity-list">
                            <?php if (empty($activities)): ?>
                                <div class="manager-empty-box">Chưa có hoạt động gần đây.</div>
                            <?php else: ?>
                                <?php foreach ($activities as $activity): ?>
                                    <div class="manager-activity-item">
                                        <div class="manager-activity-icon"><i class="bi bi-clock-history"></i></div>
                                        <div>
                                            <?php
                                            $isMine = ($activity['user_id'] == $managerId);
                                            ?>
                                            <p>
                                                <?php if ($isMine): ?>
                                                    <strong class="text-primary">Bạn</strong>
                                                <?php else: ?>
                                                    <strong><?= manager_h($activity['username']) ?></strong>
                                                <?php endif; ?>

                                                <?= manager_h($activity['description'] ?? $activity['action'] ?? 'cập nhật hệ thống') ?>
                                            </p>
                                            <small><?= manager_date($activity['created_at']) ?>
                                                <?= date('H:i', strtotime($activity['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileToggle = document.getElementById('managerProfileToggle');
            const profileDropdown = document.getElementById('managerProfileDropdown');

            if (profileToggle && profileDropdown) {
                profileToggle.addEventListener('click', function(event) {
                    if (event.target.closest('.manager-profile-dropdown')) {
                        return;
                    }

                    profileDropdown.classList.toggle('show');
                    event.stopPropagation();
                });

                document.addEventListener('click', function() {
                    profileDropdown.classList.remove('show');
                });
            }
        });

        function setCookie(name, value, days = 30) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
        }

        const themeSelect = document.getElementById('themeSelect');
        if (themeSelect) {
            themeSelect.addEventListener('change', function() {
                setCookie('theme', this.value);
                window.location.reload();
            });
        }
        const langSelect = document.getElementById('langSelect');
        if (langSelect) {
            langSelect.addEventListener('change', function() {
                setCookie('lang', this.value);
                window.location.reload();
            });
        }

        const search = document.getElementById('managerSearch');
        if (search) {
            search.addEventListener('keyup', function() {
                const keyword = this.value.toLowerCase();
                document.querySelectorAll('.manager-searchable').forEach(function(item) {
                    item.style.display = item.innerText.toLowerCase().includes(keyword) ? '' : 'none';
                });
            });
        }

        document.querySelectorAll('.manager-menu-item[href^="#"]').forEach(function(link) {
            link.addEventListener('click', function() {
                document.querySelectorAll('.manager-menu-item').forEach(item => item.classList.remove(
                    'active'));
                this.classList.add('active');
            });
        });

        const statusCtx = document.getElementById('statusChart');
        if (statusCtx && typeof Chart !== 'undefined') {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_keys($statusMap)) ?>,
                    datasets: [{
                        data: <?= json_encode(array_values($statusMap)) ?>,
                        backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '55%',
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }

        const workloadCtx = document.getElementById('workloadChart');
        if (workloadCtx && typeof Chart !== 'undefined') {
            new Chart(workloadCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($workloadData, 'username')) ?>,
                    datasets: [{
                        label: 'Số nhiệm vụ',
                        data: <?= json_encode(array_map('intval', array_column($workloadData, 'total_tasks'))) ?>,
                        backgroundColor: '#3b82f6',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>

</html>