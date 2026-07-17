<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/notification.php';

function staff_h($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function staff_format_date($date)
{
    return $date ? date('d/m/Y', strtotime($date)) : '--';
}

// Bên staff: Pending/In Progress đều gộp chung "Cần làm" (chi tiết % nằm trong modal riêng),
// trừ khi task vừa bị manager từ chối và staff chưa cập nhật lại (rejected_reason còn) thì hiện
// "Bị từ chối". Khi staff lưu tiến độ mới, rejected_reason bị xóa nên badge tự quay lại "Cần làm".
function staff_status_label($status, $rejectedReason = null)
{
    if (in_array($status, ['Pending', 'In Progress'], true)) {
        return !empty($rejectedReason) ? 'Bị từ chối' : 'Cần làm';
    }

    return match ($status) {
        'Completed' => 'Hoàn thành',
        'Pending Approval' => 'Chờ duyệt',
        default => 'Chưa rõ',
    };
}

function staff_priority_label($priority)
{
    return match ($priority) {
        'Critical' => 'Khẩn cấp',
        'High' => 'Cao',
        'Medium' => 'Trung bình',
        'Low' => 'Thấp',
        default => $priority ?: 'Trung bình',
    };
}

function staff_status_class($status, $rejectedReason = null)
{
    if (in_array($status, ['Pending', 'In Progress'], true)) {
        // "in-progress" (xanh dương) để khớp màu với mtaskclass() bên Manager cho cùng trạng thái.
        return !empty($rejectedReason) ? 'danger' : 'in-progress';
    }

    return match ($status) {
        'Completed' => 'success',
        'Pending Approval' => 'warning',
        default => 'muted',
    };
}

// Dùng đúng tên class thô (high/medium/low/critical) như bên Manager (mclass($priority)) để
// cùng 1 mức ưu tiên luôn ra cùng 1 màu ở cả 2 trang - trước đây "Cao" hiện màu cam bên Staff
// nhưng màu đỏ bên Manager, không khớp nhau.
function staff_priority_class($priority)
{
    return match ($priority) {
        'Critical' => 'critical',
        'High' => 'high',
        'Medium' => 'medium',
        'Low' => 'low',
        default => 'medium',
    };
}

$db = new Database();
$pdo = $db->connect();

try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'phone'");
    $stmt->execute();
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec("ALTER TABLE users ADD phone VARCHAR(20) NULL AFTER email");
    }

    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'address'");
    $stmt->execute();
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec("ALTER TABLE users ADD address VARCHAR(255) NULL AFTER phone");
    }
} catch (PDOException $e) {
    // Nếu database không cho ALTER, hãy thêm phone/address bằng phpMyAdmin.
}

$staffId = (int)($_SESSION['user_id'] ?? 0);
$staffPage = $_GET['page'] ?? 'staff-dashboard';
$staffPageTitles = [
    'staff-dashboard' => ['Tổng quan', 'Dashboard nhiệm vụ cá nhân'],
    'staff-projects' => ['Dự án', 'Dự án được giao'],
    'staff-tasks' => ['Nhiệm vụ', 'Nhiệm vụ của tôi'],
    'staff-kanban' => ['Kanban', 'Kanban nhiệm vụ'],
    'staff-calendar' => ['Lịch', 'Lịch công việc'],
    'staff-notifications' => ['Thông báo', 'Thông báo của tôi'],
    'staff-charts' => ['Biểu đồ', 'Biểu đồ nhiệm vụ'],
    'staff-profile' => ['Hồ sơ cá nhân', 'Hồ sơ cá nhân'],
    'staff-settings' => ['Cài đặt', 'Cài đặt tài khoản'],
];
if (!isset($staffPageTitles[$staffPage])) {
    $staffPage = 'staff-dashboard';
}

if ($staffId <= 0 || (int)($_SESSION['role_id'] ?? 0) !== 3) {
    header('Location: index.php?page=login');
    exit;
}

$staffAction = $_GET['staff_action'] ?? ($_POST['staff_action'] ?? '');

if ($staffAction === 'get_calendar') {
    header('Content-Type: application/json; charset=utf-8');

    $month = (int)($_GET['month'] ?? date('m'));
    $year = (int)($_GET['year'] ?? date('Y'));

    if ($month < 1 || $month > 12) {
        $month = (int)date('m');
    }

    if ($year < 2000 || $year > 2100) {
        $year = (int)date('Y');
    }

    $stmt = $pdo->prepare("
        SELECT task_id, title, status, due_date
        FROM tasks
        WHERE assignee_id = ?
          AND is_deleted = 0
          AND due_date IS NOT NULL
          AND MONTH(due_date) = ?
          AND YEAR(due_date) = ?
        ORDER BY due_date ASC
    ");
    $stmt->execute([$staffId, $month, $year]);
    $tasksInMonth = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $calendarTasks = [];
    $statusCounts = [
        'Pending' => 0,
        'In Progress' => 0,
        'Review' => 0,
        'Completed' => 0,
    ];

    foreach ($tasksInMonth as $task) {
        $day = (int)date('j', strtotime($task['due_date']));
        $calendarTasks[$day][] = $task;

        if (isset($statusCounts[$task['status']])) {
            $statusCounts[$task['status']]++;
        }
    }

    echo json_encode([
        'success' => true,
        'month' => $month,
        'year' => $year,
        'firstDay' => (int)date('N', strtotime(sprintf('%04d-%02d-01', $year, $month))),
        'daysInMonth' => cal_days_in_month(CAL_GREGORIAN, $month, $year),
        'calendarTasks' => $calendarTasks,
        'chartData' => array_values($statusCounts),
        'today' => [
            'day' => (int)date('d'),
            'month' => (int)date('m'),
            'year' => (int)date('Y'),
        ],
    ]);
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$profileSuccess = $_SESSION['profile_success'] ?? '';
$profileError = $_SESSION['profile_error'] ?? '';
$settingsSuccess = $_SESSION['settings_success'] ?? '';
$settingsError = $_SESSION['settings_error'] ?? '';

unset(
    $_SESSION['profile_success'],
    $_SESSION['profile_error'],
    $_SESSION['settings_success'],
    $_SESSION['settings_error']
);

$stmt = $pdo->prepare("
    SELECT user_id, username, email, phone, address, created_at
    FROM users
    WHERE user_id = ? AND is_deleted = 0
");
$stmt->execute([$staffId]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$staff) {
    die('Không tìm thấy tài khoản staff.');
}

$staff['phone'] = !empty($staff['phone']) ? $staff['phone'] : '+84 912 345 678';
$staff['address'] = !empty($staff['address']) ? $staff['address'] : '123 Đường ABC, Quận 1, TP. Hồ Chí Minh';
$staff['created_at'] = !empty($staff['created_at']) ? $staff['created_at'] : date('Y-m-d');

$notificationModel = new Notification();
$notificationPrefs = $notificationModel->getPreferences($staffId);

// Mở đúng trang "Thông báo" (không phải chỉ liếc qua widget ở Dashboard) coi như đã xem hết -
// đánh dấu tất cả đã đọc để badge đỏ ở sidebar + nút chuông trên topbar biến mất ngay, cho tới
// khi có thông báo mới thì đếm lại từ đầu.
if ($staffPage === 'staff-notifications') {
    $notificationModel->markAllRead($staffId);
}

$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total_tasks,
        SUM(status = 'In Progress') AS doing_tasks,
        SUM(status = 'Completed') AS done_tasks,
        SUM(due_date < CURDATE() AND status != 'Completed') AS late_tasks
    FROM tasks
    WHERE assignee_id = ? AND is_deleted = 0
");
$stmt->execute([$staffId]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$totalTasks = (int)($stats['total_tasks'] ?? 0);
$doingTasks = (int)($stats['doing_tasks'] ?? 0);
$doneTasks = (int)($stats['done_tasks'] ?? 0);
$lateTasks = (int)($stats['late_tasks'] ?? 0);
$openTasks = $totalTasks - $doneTasks;
$completionRate = $totalTasks > 0 ? round($doneTasks / $totalTasks * 100) : 0;

$stmt = $pdo->prepare('SELECT COUNT(*) FROM project_members WHERE user_id = ?');
$stmt->execute([$staffId]);
$totalProjects = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT
        p.project_id,
        p.project_code,
        p.name,
        p.description,
        p.end_date,
        COALESCE(manager.username, 'Chưa phân công') AS manager_name,
        COUNT(t.task_id) AS total_tasks,
        SUM(t.status = 'Completed') AS completed_tasks,
        SUM(t.status != 'Completed') AS open_tasks,
        SUM(t.priority IN ('Critical', 'High') AND t.status != 'Completed') AS urgent_tasks
    FROM project_members pm
    JOIN projects p ON p.project_id = pm.project_id
    LEFT JOIN users manager ON manager.user_id = COALESCE(p.manager_id, p.created_by)
    LEFT JOIN tasks t
        ON t.project_id = p.project_id
        AND t.assignee_id = pm.user_id
        AND t.is_deleted = 0
    WHERE pm.user_id = ?
      AND p.is_deleted = 0
    GROUP BY p.project_id
    ORDER BY p.end_date ASC
    LIMIT 6
");
$stmt->execute([$staffId]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT
        t.task_id,
        t.title,
        t.description,
        t.status,
        t.rejected_reason,
        t.priority,
        t.due_date,
        p.name AS project_name,
        COALESCE(manager.username, 'System') AS manager_name,
        COUNT(DISTINCT c.comment_id) AS comment_count,
        COUNT(DISTINCT a.attachment_id) AS attachment_count
    FROM tasks t
    JOIN projects p ON p.project_id = t.project_id
    LEFT JOIN users manager ON manager.user_id = t.created_by
    LEFT JOIN comments c ON c.task_id = t.task_id
    LEFT JOIN attachments a ON a.task_id = t.task_id
    WHERE t.assignee_id = ?
      AND t.is_deleted = 0
      AND p.is_deleted = 0
    GROUP BY t.task_id
    ORDER BY
        t.status = 'Completed',
        FIELD(t.priority, 'Critical', 'High', 'Medium', 'Low'),
        t.due_date ASC
    LIMIT 12
");
$stmt->execute([$staffId]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$kanbanColumns = [
    'Pending' => ['label' => 'Cần làm', 'icon' => 'bi-hourglass-split'],
    'In Progress' => ['label' => 'Đang thực hiện', 'icon' => 'bi-play-circle'],
    'Pending Approval' => ['label' => 'Chờ duyệt', 'icon' => 'bi-search'],
    'Completed' => ['label' => 'Hoàn thành', 'icon' => 'bi-check-circle'],
];

$kanbanTasks = array_fill_keys(array_keys($kanbanColumns), []);
foreach ($tasks as $task) {
    $status = $task['status'];
    if (!isset($kanbanTasks[$status])) {
        $status = 'Pending';
    }
    $kanbanTasks[$status][] = $task;
}

$stmt = $pdo->prepare("
    SELECT task_id, title, status, priority, due_date
    FROM tasks
    WHERE assignee_id = ? AND is_deleted = 0
    ORDER BY
        status = 'Completed',
        FIELD(priority, 'Critical', 'High', 'Medium', 'Low'),
        due_date ASC
");
$stmt->execute([$staffId]);
$reportTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT c.comment_id, c.content, c.created_at, t.task_id, t.title, t.status
    FROM comments c
    JOIN tasks t ON t.task_id = c.task_id
    WHERE c.user_id = ?
      AND t.assignee_id = ?
      AND t.is_deleted = 0
    ORDER BY c.created_at DESC
    LIMIT 5
");
$stmt->execute([$staffId, $staffId]);
$recentReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT t.task_id, t.title, t.priority, t.due_date, p.name AS project_name
    FROM tasks t
    JOIN projects p ON p.project_id = t.project_id
    WHERE t.assignee_id = ?
      AND t.status != 'Completed'
      AND t.is_deleted = 0
      AND (t.due_date = CURDATE() OR t.priority IN ('Critical', 'High') OR t.due_date < CURDATE())
    ORDER BY t.due_date ASC
    LIMIT 4
");
$stmt->execute([$staffId]);
$todayFocus = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT p.name AS project_name, COUNT(t.task_id) AS open_tasks
    FROM tasks t
    JOIN projects p ON p.project_id = t.project_id
    WHERE t.assignee_id = ?
      AND t.status != 'Completed'
      AND t.is_deleted = 0
      AND p.is_deleted = 0
    GROUP BY p.project_id
    ORDER BY open_tasks DESC
    LIMIT 4
");
$stmt->execute([$staffId]);
$workload = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Widget ở Dashboard chỉ cần xem nhanh 4 thông báo mới nhất, nhưng trang "Thông báo" riêng
// (staff-notifications) phải hiện đầy đủ - trước đây luôn giới hạn 4 dù đang ở trang nào.
$allNotifications = $notificationModel->getByUser($staffId);
$notifications = $staffPage === 'staff-notifications' ? $allNotifications : array_slice($allNotifications, 0, 4);
$unreadNotifications = $notificationModel->unreadCount($staffId);

$initials = strtoupper(substr($staff['username'], 0, 1));
$theme = $_COOKIE['theme'] ?? 'light';
$currentLang = $_COOKIE['lang'] ?? 'vi';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= csrf_meta_tag() ?>
    <title>PMS</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/projecthub-icon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/dashboard_staff.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/task-detail-shared.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/staff_modals.css?v=<?= time() ?>">

<style>
/* Staff page scope: mỗi menu chỉ hiển thị đúng nội dung của trang đó */
body[class*="staff-page-"] .staff-content-row,
body[class*="staff-page-"] #staff-profile,
body[class*="staff-page-"] #staff-settings {
    display: none !important;
}
body.staff-page-staff-dashboard .staff-row-stats,
body.staff-page-staff-dashboard .staff-row-projects,
body.staff-page-staff-dashboard .staff-row-tasks {
    display: flex !important;
}
body.staff-page-staff-projects .staff-row-projects,
body.staff-page-staff-tasks .staff-row-tasks,
body.staff-page-staff-kanban .staff-row-kanban,
body.staff-page-staff-calendar .staff-row-calendar,
body.staff-page-staff-notifications .staff-row-notifications,
body.staff-page-staff-charts .staff-row-charts {
    display: flex !important;
}
body.staff-page-staff-profile #staff-profile,
body.staff-page-staff-settings #staff-settings {
    display: block !important;
}
/* Khi tách riêng từng trang, cho nội dung chiếm full width */
body.staff-page-staff-projects .staff-row-projects > div,
body.staff-page-staff-tasks .staff-row-tasks > div,
body.staff-page-staff-kanban .staff-row-kanban > div,
body.staff-page-staff-calendar .staff-row-calendar > div,
body.staff-page-staff-notifications .staff-row-notifications > div,
body.staff-page-staff-charts .staff-row-charts > div {
    width: 100%;
    flex: 0 0 100%;
    max-width: 100%;
}
body.staff-page-staff-dashboard .content {
    min-height: 100vh;
}
</style>


<style>
body.staff-page-staff-projects .staff-charts-col,
body.staff-page-staff-charts .staff-projects-col {
    display: none !important;
}
body.staff-page-staff-projects .staff-projects-col,
body.staff-page-staff-charts .staff-charts-col {
    width: 100%;
    flex: 0 0 100%;
    max-width: 100%;
}
</style>


<style>
body.staff-page-staff-calendar .staff-notifications-col,
body.staff-page-staff-notifications .staff-calendar-col {
    display: none !important;
}
body.staff-page-staff-calendar .staff-calendar-col,
body.staff-page-staff-notifications .staff-notifications-col {
    width: 100%;
    flex: 0 0 100%;
    max-width: 100%;
}
</style>

</head>
<body class="<?= ($theme === 'dark' ? 'dark-mode ' : '') . 'staff-page-' . staff_h($staffPage) ?>">
<div class="staff-shell">
    <aside class="staff-sidebar" id="staffSidebar">
        <div class="brand-row">
            <div class="brand-mark"><i class="bi bi-briefcase-fill"></i></div>
            <div>
                <strong>ProjectHub</strong>
                <span>Staff workspace</span>
            </div>
            <button class="icon-button mobile-toggle" id="mobileMenuButton" type="button" aria-label="Mở menu">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <nav class="staff-nav" aria-label="Điều hướng staff">
            <a class="<?= $staffPage === 'staff-dashboard' ? 'active' : '' ?>" href="index.php?page=staff-dashboard"><i class="bi bi-grid-1x2"></i><span>Tổng quan</span></a>
            <a class="<?= $staffPage === 'staff-projects' ? 'active' : '' ?>" href="index.php?page=staff-projects"><i class="bi bi-folder2-open"></i><span>Dự án</span><b><?= $totalProjects ?></b></a>
            <a class="<?= $staffPage === 'staff-tasks' ? 'active' : '' ?>" href="index.php?page=staff-tasks"><i class="bi bi-check2-square"></i><span>Nhiệm vụ</span><b><?= $totalTasks ?></b></a>
            <a class="<?= $staffPage === 'staff-kanban' ? 'active' : '' ?>" href="index.php?page=staff-kanban"><i class="bi bi-kanban"></i><span>Kanban</span></a>
            <a class="<?= $staffPage === 'staff-calendar' ? 'active' : '' ?>" href="index.php?page=staff-calendar"><i class="bi bi-calendar3"></i><span>Lịch</span></a>
            <a class="<?= $staffPage === 'staff-notifications' ? 'active' : '' ?>" href="index.php?page=staff-notifications"><i class="bi bi-bell"></i><span>Thông báo</span><b class="badge-red"><?= $unreadNotifications ?></b></a>
        </nav>

        <div class="sidebar-profile">
            <div class="avatar"><?= staff_h($initials) ?></div>
            <div>
                <strong><?= staff_h($staff['username']) ?></strong>
                <span><?= staff_h($staff['email']) ?></span>
            </div>
        </div>
    </aside>

    <main class="content">
        <header class="topbar">
            <div>
                <p class="eyebrow"><?= staff_h($staffPageTitles[$staffPage][1]) ?></p>
                <?php if ($staffPage === 'staff-dashboard'): ?>
                    <h1>Chào <?= staff_h($staff['username']) ?>, hôm nay có <?= count($todayFocus) ?> việc cần ưu tiên</h1>
                    <span><?= date('d/m/Y') ?> · Theo dõi nhanh tình hình công việc của bạn</span>
                <?php else: ?>
                    <h1><?= staff_h($staffPageTitles[$staffPage][0]) ?></h1>
                    <span><?= date('d/m/Y') ?> · <?= staff_h($staffPageTitles[$staffPage][1]) ?></span>
                <?php endif; ?>
            </div>

            <div class="topbar-actions">
                <label class="search-box">
                    <i class="bi bi-search"></i>
                    <input id="searchInput" type="search" placeholder="Tìm dự án, task, manager">
                </label>
                <button class="icon-button" type="button" onclick="location.reload()" aria-label="Đồng bộ">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                <a class="icon-button notify-button" href="index.php?page=staff-notifications" <?= $unreadNotifications > 0 ? 'data-count="' . $unreadNotifications . '"' : '' ?> aria-label="Thông báo">
                    <i class="bi bi-bell"></i>
                </a>

                <div class="staff-profile-menu" id="staffProfileToggle">
                    <div class="staff-profile-avatar-small"><?= staff_h($initials) ?></div>
                    <div class="staff-profile-meta">
                        <strong><?= staff_h($staff['username']) ?></strong>
                        <span>Staff</span>
                    </div>

                    <div class="staff-profile-dropdown" id="staffProfileDropdown">
                        <div class="staff-dropdown-head">
                            <strong><?= staff_h($staff['username']) ?></strong>
                            <span>Staff</span>
                        </div>
                        <a href="index.php?page=staff-profile"><i class="bi bi-person"></i> Hồ sơ cá nhân</a>
                        <a href="index.php?page=staff-settings"><i class="bi bi-gear"></i> Cài đặt</a>
                        <a href="index.php?page=logout" class="logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                    </div>
                </div>
            </div>
        </header>

        <section class="container-fluid px-0">
            <?php if ($flash): ?>
                <div class="alert alert-<?= staff_h($flash['type']) ?> progress-alert" role="alert">
                    <?= staff_h($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($profileSuccess) || !empty($profileError) || !empty($settingsSuccess) || !empty($settingsError)): ?>
                <div class="staff-toast <?= (!empty($profileSuccess) || !empty($settingsSuccess)) ? 'success' : 'error' ?>" id="staffToast">
                    <?= staff_h($profileSuccess ?: $settingsSuccess ?: $profileError ?: $settingsError) ?>
                </div>
            <?php endif; ?>

            <div class="row g-4 staff-content-row staff-row-stats">
                <div class="col-xl-3 col-md-6">
                    <article class="stat-card">
                        <div class="card-top"><div class="icon-circle icon-blue"><i class="bi bi-folder2-open"></i></div></div>
                        <div class="card-title-custom">Dự án tham gia</div>
                        <div class="card-number"><?= $totalProjects ?></div>
                        <small>Dự án bạn có trong project_members</small>
                    </article>
                </div>

                <div class="col-xl-3 col-md-6">
                    <article class="stat-card">
                        <div class="card-top"><div class="icon-circle icon-orange"><i class="bi bi-list-check"></i></div></div>
                        <div class="card-title-custom">Nhiệm vụ mở</div>
                        <div class="card-number"><?= $openTasks ?></div>
                        <small><?= $lateTasks ?> việc đang quá hạn</small>
                    </article>
                </div>

                <div class="col-xl-3 col-md-6">
                    <article class="stat-card">
                        <div class="card-top"><div class="icon-circle icon-green"><i class="bi bi-play-circle-fill"></i></div></div>
                        <div class="card-title-custom">Đang thực hiện</div>
                        <div class="card-number"><?= $doingTasks ?></div>
                        <small>Cần cập nhật tiến độ thường xuyên</small>
                    </article>
                </div>

                <div class="col-xl-3 col-md-6">
                    <article class="stat-card">
                        <div class="card-top"><div class="icon-circle icon-red"><i class="bi bi-check-circle"></i></div></div>
                        <div class="card-title-custom">Tỷ lệ hoàn thành</div>
                        <div class="card-number"><?= $completionRate ?>%</div>
                        <small><?= $doneTasks ?>/<?= $totalTasks ?> nhiệm vụ đã xong</small>
                    </article>
                </div>
            </div>

            <div class="row g-4 mt-2 staff-content-row staff-row-projects staff-row-charts">
                <div class="col-xl-8 staff-projects-col">
                    <section class="dashboard-card p-4" id="projects">
                        <div class="section-head">
                            <div>
                                <h4>Dự án được giao</h4>
                                <p>Theo project_members và các task được gán cho bạn</p>
                            </div>
                            <div class="segmented-control">
                                <button class="active" type="button" data-project-filter="all">Tất cả</button>
                                <button type="button" data-project-filter="active">Đang làm</button>
                                <button type="button" data-project-filter="urgent">Gấp</button>
                            </div>
                        </div>

                        <div class="project-grid" id="projectGrid">
                            <?php foreach ($projects as $project): ?>
                                <?php
                                $projectTotal = (int)$project['total_tasks'];
                                $projectDone = (int)$project['completed_tasks'];
                                $projectOpen = (int)$project['open_tasks'];
                                $projectUrgent = (int)$project['urgent_tasks'];
                                $progress = $projectTotal > 0 ? round($projectDone / $projectTotal * 100) : 0;
                                $projectState = trim(($projectOpen > 0 ? 'active ' : '') . ($projectUrgent > 0 ? 'urgent' : ''));
                                ?>
                                <article class="project-card" role="button" tabindex="0" data-project-id="<?= (int)$project['project_id'] ?>" data-status="<?= staff_h($projectState ?: 'done') ?>" data-search="<?= staff_h(strtolower($project['name'] . ' ' . $project['project_code'] . ' ' . $project['manager_name'])) ?>">
                                    <div class="project-top">
                                        <div>
                                            <strong><?= staff_h($project['name']) ?></strong>
                                            <span><?= staff_h($project['project_code']) ?> · Manager: <?= staff_h($project['manager_name']) ?></span>
                                        </div>
                                        <span class="pill <?= $projectUrgent > 0 ? 'danger' : 'primary' ?>">
                                            <?= $projectUrgent > 0 ? 'Gấp' : 'Đang làm' ?>
                                        </span>
                                    </div>
                                    <p><?= staff_h($project['description']) ?></p>
                                    <div class="progress-block">
                                        <div><span>Tiến độ phần việc</span><b><?= $progress ?>%</b></div>
                                        <div class="progress"><div class="progress-bar" style="width: <?= $progress ?>%"></div></div>
                                    </div>
                                    <div class="project-meta">
                                        <span>Task mở<b><?= $projectOpen ?></b></span>
                                        <span>Deadline<b><?= staff_format_date($project['end_date']) ?></b></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <div class="empty-state" id="projectEmpty" <?= count($projects) ? '' : 'style="display:block"' ?>>
                            Chưa có dự án nào được gán cho tài khoản này.
                        </div>
                    </section>
                </div>

                <div class="col-xl-4 staff-charts-col">
                    <section class="dashboard-card p-4 h-100" id="staff-charts">
                        <div class="section-head">
                            <div>
                                <h4>Trạng thái task</h4>
                                <p>Theo deadline trong tháng đang xem</p>
                            </div>
                        </div>

                        <div class="chart-box"><canvas id="statusChart"></canvas></div>

                        <div class="workload-list mt-4">
                            <?php if (count($workload) > 0): ?>
                                <?php $maxLoad = max(array_column($workload, 'open_tasks')); ?>
                                <?php foreach ($workload as $row): ?>
                                    <?php $width = $maxLoad > 0 ? round($row['open_tasks'] / $maxLoad * 100) : 0; ?>
                                    <div class="workload-row">
                                        <span><?= staff_h($row['project_name']) ?></span>
                                        <b><?= (int)$row['open_tasks'] ?></b>
                                        <div class="progress"><div class="progress-bar" style="width: <?= $width ?>%"></div></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="side-empty">Không còn task đang mở.</div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-4 mt-2 staff-content-row staff-row-tasks">
                <div class="col-xl-8">
                    <section class="dashboard-card p-4" id="tasks">
                        <div class="section-head">
                            <div>
                                <h4>Nhiệm vụ của tôi</h4>
                                <p>Danh sách từ bảng tasks với assignee_id là user hiện tại</p>
                            </div>
                            <div class="segmented-control">
                                <button class="active" type="button" data-task-filter="all">Tất cả</button>
                                <button type="button" data-task-filter="today">Hôm nay</button>
                                <button type="button" data-task-filter="blocked">Quá hạn</button>
                            </div>
                        </div>

                        <div class="table-responsive staff-task-table">
                            <table class="table align-middle mb-0 recent-projects-table">
                                <thead>
                                    <tr>
                                        <th>Nhiệm vụ</th>
                                        <th>Dự án</th>
                                        <th>Ưu tiên</th>
                                        <th>Hạn</th>
                                        <th>Trạng thái</th>
                                        <th class="text-center">Mở</th>
                                    </tr>
                                </thead>
                                <tbody id="taskRows">
                                    <?php foreach ($tasks as $task): ?>
                                        <?php
                                        $isDone = $task['status'] === 'Completed';
                                        $isToday = $task['due_date'] && date('Y-m-d', strtotime($task['due_date'])) === date('Y-m-d');
                                        $isLate = $task['due_date'] && strtotime($task['due_date']) < strtotime(date('Y-m-d')) && !$isDone;
                                        $taskState = $isLate ? 'blocked' : ($isToday ? 'today' : 'all');
                                        ?>
                                        <tr class="task-row" data-state="<?= staff_h($taskState) ?>" data-search="<?= staff_h(strtolower($task['title'] . ' ' . $task['project_name'] . ' ' . $task['manager_name'] . ' ' . $task['priority'] . ' ' . $task['status'])) ?>">
                                            <td>
                                                <strong><a class="js-staff-task-link" href="#" data-task-id="<?= (int)$task['task_id'] ?>">#<?= (int)$task['task_id'] ?> · <?= staff_h($task['title']) ?></a></strong>
                                                <span><?= staff_h($task['description'] ?: 'Không có mô tả') ?></span>
                                                <small>
                                                    <i class="bi bi-chat-left-text"></i> <?= (int)$task['comment_count'] ?>
                                                    <i class="bi bi-paperclip ms-2"></i> <?= (int)$task['attachment_count'] ?>
                                                    · Tạo bởi <?= staff_h($task['manager_name']) ?>
                                                </small>
                                            </td>
                                            <td><?= staff_h($task['project_name']) ?></td>
                                            <td><span class="pill <?= staff_priority_class($task['priority']) ?>"><?= staff_h(staff_priority_label($task['priority'])) ?></span></td>
                                            <td><?= staff_format_date($task['due_date']) ?></td>
                                            <td><span class="pill <?= staff_status_class($task['status'], $task['rejected_reason'] ?? null) ?>"><?= staff_h(staff_status_label($task['status'], $task['rejected_reason'] ?? null)) ?></span></td>
                                            <td class="text-center"><button class="open-task js-staff-task-link" type="button" data-task-id="<?= (int)$task['task_id'] ?>"><i class="bi bi-arrow-up-right"></i></button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="empty-state" id="taskEmpty" <?= count($tasks) ? '' : 'style="display:block"' ?>>Không tìm thấy nhiệm vụ phù hợp.</div>
                    </section>
                </div>

                <div class="col-xl-4">
                    <section class="dashboard-card p-4 h-100">
                        <div class="section-head">
                            <div>
                                <h4>Việc cần ưu tiên</h4>
                                <p>Task đến hạn, quá hạn hoặc ưu tiên cao</p>
                            </div>
                        </div>
                        <div class="focus-list">
                            <?php if (count($todayFocus) > 0): ?>
                                <?php foreach ($todayFocus as $item): ?>
                                    <article class="focus-item">
                                        <span class="pill <?= staff_priority_class($item['priority']) ?>"><?= staff_h(staff_priority_label($item['priority'])) ?></span>
                                        <strong><?= staff_h($item['title']) ?></strong>
                                        <p><?= staff_h($item['project_name']) ?> · Deadline <?= staff_format_date($item['due_date']) ?></p>
                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="side-empty">Không có việc gấp hôm nay.</div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-4 mt-2 staff-content-row staff-row-kanban">
                <div class="col-12">
                    <section class="dashboard-card p-4" id="kanban">
                        <div class="section-head">
                            <div>
                                <h4>Kanban nhiệm vụ</h4>
                                <p>Nhìn nhanh các task theo trạng thái</p>
                            </div>
                        </div>

                        <div class="kanban-board">
                            <?php foreach ($kanbanColumns as $status => $column): ?>
                                <div class="kanban-column">
                                    <div class="kanban-head">
                                        <i class="bi <?= staff_h($column['icon']) ?>"></i>
                                        <span><?= staff_h($column['label']) ?></span>
                                        <b><?= count($kanbanTasks[$status]) ?></b>
                                    </div>
                                    <div class="kanban-list">
                                        <?php foreach ($kanbanTasks[$status] as $task): ?>
                                            <article class="kanban-card">
                                                <strong>#<?= (int)$task['task_id'] ?> · <?= staff_h($task['title']) ?></strong>
                                                <span><?= staff_h($task['project_name']) ?></span>
                                                <div>
                                                    <span class="pill <?= staff_priority_class($task['priority']) ?>"><?= staff_h(staff_priority_label($task['priority'])) ?></span>
                                                    <small><?= staff_format_date($task['due_date']) ?></small>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                        <?php if (count($kanbanTasks[$status]) === 0): ?>
                                            <div class="kanban-empty">Không có task.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
            </div>
            <div class="row g-4 mt-2 staff-content-row staff-row-calendar staff-row-notifications">
                <div class="col-xl-8 staff-calendar-col">
                    <section class="dashboard-card p-4" id="calendar">
                        <div class="section-head">
                            <div>
                                <h4>Lịch công việc</h4>
                                <p>Deadline theo due_date của các task được giao</p>
                            </div>
                            <div class="calendar-actions">
                                <button id="btnPrev" class="icon-button" type="button" aria-label="Tháng trước"><i class="bi bi-chevron-left"></i></button>
                                <strong id="calendarTitle"></strong>
                                <button id="btnNext" class="icon-button" type="button" aria-label="Tháng sau"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="calendar-shell">
                            <div class="calendar-header"><div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div>T7</div><div>CN</div></div>
                            <div class="calendar-grid" id="calendarGrid"></div>
                        </div>
                    </section>
                </div>

                <div class="col-xl-4 staff-notifications-col">
                    <section class="dashboard-card p-4 h-100" id="notifications">
                        <div class="section-head"><h4>Thông báo</h4><span class="small-badge"><?= $unreadNotifications ?> mới</span></div>

                        <div class="notification-list">
                            <?php if (count($notifications) > 0): ?>
                                <?php foreach ($notifications as $note): ?>
                                    <?php
                                        // Bấm vào thông báo là mở luôn chi tiết nhiệm vụ liên quan (nhiệm vụ
                                        // mới, bình luận, kết quả duyệt...). Thông báo hệ thống thì không có link.
                                        $noteType = $note['type'] ?? 'system';
                                        $noteRelatedId = (int)($note['related_id'] ?? 0);
                                        $noteLink = ($noteRelatedId > 0)
                                            ? 'index.php?action=openNotification&id=' . (int)$note['notification_id'] . '&_csrf_token=' . urlencode(csrf_token())
                                            : null;
                                    ?>
                                    <a href="<?= $noteLink ? staff_h($noteLink) : 'javascript:void(0)' ?>" class="notification-item <?= $note['is_read'] ? '' : 'unread' ?> <?= $noteLink ? '' : 'no-link' ?>">
                                        <div class="activity-icon <?= $note['is_read'] ? 'bg-primary-subtle' : 'bg-success-subtle' ?>">
                                            <i class="bi bi-bell <?= $note['is_read'] ? 'text-primary' : 'text-success' ?>"></i>
                                        </div>
                                        <div>
                                            <strong><?= staff_h($note['title']) ?></strong>
                                            <p><?= staff_h($note['content']) ?></p>
                                            <span><?= date('d/m/Y H:i', strtotime($note['created_at'])) ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="side-empty">Chưa có thông báo.</div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        <section class="container-fluid px-0 mt-4" id="staff-profile">
            <form method="POST" action="index.php?action=updateProfile" class="staff-profile-form">
                <div class="row g-4">
                    <div class="col-xl-4">
                        <section class="dashboard-card p-4 h-100 profile-card-staff">
                            <div class="profile-avatar-xl"><?= staff_h($initials) ?></div>
                            <h4><?= staff_h($staff['username']) ?></h4>
                            <p><?= staff_h($staff['email']) ?></p>
                            <span class="pill primary">Staff</span>

                            <div class="profile-mini-stats">
                                <div>
                                    <strong><?= $totalTasks ?></strong>
                                    <span>Tổng nhiệm vụ</span>
                                </div>
                                <div>
                                    <strong><?= $doneTasks ?></strong>
                                    <span>Đã hoàn thành</span>
                                </div>
                                <div>
                                    <strong><?= $doingTasks ?></strong>
                                    <span>Đang thực hiện</span>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-xl-8">
                        <section class="dashboard-card p-4 h-100 staff-profile-edit-card">
                            <div class="section-head">
                                <div>
                                    <h4>Hồ sơ cá nhân</h4>
                                    <p>Chỉnh sửa thông tin tài khoản được phép thay đổi</p>
                                </div>
                                <button class="staff-primary-btn staff-save-btn" type="submit">
                                    <i class="bi bi-save"></i> Lưu thay đổi
                                </button>
                            </div>

                            <div class="staff-profile-grid editable">
                                <label class="profile-field">
                                    <span>Họ và tên</span>
                                    <input type="text" name="username" value="<?= staff_h($staff['username']) ?>" required>
                                </label>
                                <label class="profile-field">
                                    <span>Email</span>
                                    <input type="email" name="email" value="<?= staff_h($staff['email']) ?>" required>
                                </label>
                                <label class="profile-field">
                                    <span>Số điện thoại</span>
                                    <input type="text" name="phone" value="<?= staff_h($staff['phone'] ?? '+84 912 345 678') ?>">
                                </label>
                                <label class="profile-field">
                                    <span>Ngày tham gia</span>
                                    <input type="date" name="created_at" value="<?= !empty($staff['created_at']) ? date('Y-m-d', strtotime($staff['created_at'])) : date('Y-m-d') ?>">
                                </label>
                                <label class="profile-field">
                                    <span>Vai trò</span>
                                    <input type="text" value="Staff" disabled>
                                </label>
                                <label class="profile-field">
                                    <span>Dự án tham gia</span>
                                    <input type="text" value="<?= $totalProjects ?>" disabled>
                                </label>
                                <label class="profile-field profile-field-full">
                                    <span>Địa chỉ</span>
                                    <input type="text" name="address" value="<?= staff_h($staff['address'] ?? '123 Đường ABC, Quận 1, TP. Hồ Chí Minh') ?>">
                                </label>
                            </div>
                        </section>
                    </div>
                </div>
            </form>
        </section>

        <section class="container-fluid px-0 mt-4" id="staff-settings">
            <div class="staff-settings-layout">
                <form class="dashboard-card p-4 staff-settings-card staff-settings-notification" action="index.php?action=updateNotificationSettings" method="POST" id="staffNotificationSettingsForm" data-ajax-stay>
                    <div class="section-head">
                        <div>
                            <h4><i class="bi bi-bell"></i> Thông báo</h4>
                            <p>Bật/tắt loại thông báo bạn muốn nhận</p>
                        </div>
                    </div>

                    <div class="staff-setting-row">
                        <div>
                            <h5>Email thông báo</h5>
                            <p>Nhận thông báo qua email</p>
                        </div>
                        <label class="staff-switch"><input type="checkbox" name="email_notifications" value="1" <?= (int)($notificationPrefs['email_notifications'] ?? 1) === 1 ? 'checked' : '' ?>><span></span></label>
                    </div>

                    <div class="staff-setting-row">
                        <div>
                            <h5>Thông báo nhiệm vụ mới</h5>
                            <p>Nhận thông báo khi được giao nhiệm vụ mới</p>
                        </div>
                        <label class="staff-switch"><input type="checkbox" name="task_notifications" value="1" <?= (int)($notificationPrefs['task_notifications'] ?? 1) === 1 ? 'checked' : '' ?>><span></span></label>
                    </div>

                    <div class="staff-setting-row">
                        <div>
                            <h5>Thông báo bình luận</h5>
                            <p>Nhận thông báo khi có bình luận mới</p>
                        </div>
                        <label class="staff-switch"><input type="checkbox" name="comment_notifications" value="1" <?= (int)($notificationPrefs['comment_notifications'] ?? 1) === 1 ? 'checked' : '' ?>><span></span></label>
                    </div>

                    <div class="staff-setting-row">
                        <div>
                            <h5>Nhắc deadline</h5>
                            <p>Nhắc deadline khi nhiệm vụ gần đến hạn hoặc quá hạn</p>
                        </div>
                        <label class="staff-switch"><input type="checkbox" name="deadline_notifications" value="1" <?= (int)($notificationPrefs['deadline_notifications'] ?? 1) === 1 ? 'checked' : '' ?>><span></span></label>
                    </div>
                </form>

                <section class="dashboard-card p-4 staff-settings-card">
                    <div class="section-head">
                        <div>
                            <h4><i class="bi bi-palette"></i> Cài đặt giao diện</h4>
                            <p>Đổi chế độ hiển thị và ngôn ngữ giao diện</p>
                        </div>
                    </div>

                    <div class="staff-settings-grid">
                        <label>
                            <span>Chế độ hiển thị</span>
                            <select id="themeSelect" class="staff-input">
                                <option value="light" <?= $theme === 'light' ? 'selected' : '' ?>>Light Mode</option>
                                <option value="dark" <?= $theme === 'dark' ? 'selected' : '' ?>>Dark Mode</option>
                            </select>
                        </label>

                        <label>
                            <span>Ngôn ngữ</span>
                            <select id="langSelect" class="staff-input">
                                <option value="vi" <?= $currentLang === 'vi' ? 'selected' : '' ?>>Tiếng Việt</option>
                                <option value="en" <?= $currentLang === 'en' ? 'selected' : '' ?>>English</option>
                            </select>
                        </label>
                    </div>
                </section>

                <form class="dashboard-card p-4 staff-settings-card" action="index.php?action=changePassword" method="POST">
                    <div class="section-head">
                        <div>
                            <h4><i class="bi bi-lock"></i> Bảo mật tài khoản</h4>
                            <p>Đổi mật khẩu đăng nhập của tài khoản Staff</p>
                        </div>
                    </div>

                    <div class="staff-settings-grid">
                        <label>
                            <span>Mật khẩu hiện tại</span>
                            <input class="staff-input" type="password" name="current_password" required>
                        </label>

                        <label>
                            <span>Mật khẩu mới</span>
                            <input class="staff-input" type="password" name="new_password" required>
                        </label>

                        <label>
                            <span>Xác nhận mật khẩu mới</span>
                            <input class="staff-input" type="password" name="confirm_password" required>
                        </label>

                        <button type="submit" class="staff-primary-btn">Đổi mật khẩu</button>
                    </div>
                </form>
            </div>
        </section>

    </main>
</div>

<div class="staff-modal-backdrop" id="staffProjectModal" aria-hidden="true">
    <div class="staff-modal staff-project-modal" role="dialog" aria-modal="true" aria-labelledby="staffProjectModalTitle">
        <div class="staff-modal-header">
            <h2 id="staffProjectModalTitle">Chi tiết dự án</h2>
            <button class="staff-modal-close" type="button" data-staff-close="staffProjectModal" aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="staff-modal-body" id="staffProjectModalBody"></div>
    </div>
</div>

<div class="staff-modal-backdrop staff-modal-stack" id="staffTaskModal" aria-hidden="true">
    <div class="staff-modal staff-task-modal" role="dialog" aria-modal="true" aria-labelledby="staffTaskModalTitle">
        <div class="staff-modal-header">
            <h2 id="staffTaskModalTitle">Chi tiết nhiệm vụ</h2>
            <button class="staff-modal-close" type="button" data-staff-close="staffTaskModal" aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="staff-modal-body">
            <div id="staffTaskDetailBody"></div>
            <div class="task-section">
                <h3><i class="bi bi-send-check"></i> Nộp báo cáo tiến độ</h3>
                <form id="staffTaskProgressForm" enctype="multipart/form-data">
                    <input type="hidden" name="task_id" id="staffProgressTaskId">
                    <!-- Chỉ còn 1 hành động "Nộp báo cáo" = báo hoàn thành, luôn chuyển task sang
                         Chờ duyệt. Không còn Lưu nháp / chọn trạng thái / nhập % nữa - 2 giá trị
                         này trước đây không ảnh hưởng gì tới việc duyệt (Manager duyệt luôn set
                         100%), nên cố định sẵn ở đây cho gọn. Muốn ghi chú cập nhật giữa chừng thì
                         dùng khung "Trao đổi" ở trên. -->
                    <input type="hidden" name="status" value="Completed">
                    <input type="hidden" name="progress_percent" value="100">
                    <div class="staff-progress-grid">
                        <label class="staff-progress-full">
                            <span>Ghi chú báo cáo</span>
                            <textarea name="progress_comment" id="staffProgressComment" rows="4" placeholder="Mô tả ngắn gọn kết quả đã hoàn thành..."></textarea>
                        </label>
                        <label class="staff-progress-full">
                            <span>File báo cáo</span>
                            <input type="file" name="progress_file" id="staffProgressFile">
                        </label>
                    </div>
                    <div class="staff-modal-actions">
                        <button class="staff-primary-btn" type="submit">Nộp báo cáo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function setCookie(name, value, days = 30) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
}

const themeSelect = document.getElementById('themeSelect');
if (themeSelect) {
    themeSelect.addEventListener('change', function () {
        setCookie('theme', this.value);
        window.location.reload();
    });
}

const langSelect = document.getElementById('langSelect');
if (langSelect) {
    langSelect.addEventListener('change', function () {
        setCookie('lang', this.value);
        window.location.reload();
    });
}

const staffNotifForm = document.getElementById('staffNotificationSettingsForm');
if (staffNotifForm) {
    staffNotifForm.querySelectorAll('input[type=checkbox]').forEach(input => {
        input.addEventListener('change', async () => {
            const formData = new FormData();

            staffNotifForm.querySelectorAll('input[type=checkbox]').forEach(item => {
                if (item.checked) {
                    formData.append(item.name, '1');
                }
            });

            try {
                const response = await fetch(staffNotifForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const result = await response.json();
                if (!result.success) {
                    alert(result.message || 'Không lưu được cài đặt thông báo.');
                    window.location.reload();
                }
            } catch (error) {
                alert('Không kết nối được server. Vui lòng thử lại.');
                window.location.reload();
            }
        });
    });
}

const staffToast = document.getElementById('staffToast');
if (staffToast) {
    setTimeout(() => {
        staffToast.style.opacity = '0';
        staffToast.style.transform = 'translateX(24px)';
        setTimeout(() => staffToast.remove(), 350);
    }, 2000);
}

document.addEventListener('DOMContentLoaded', function () {
    const profileToggle = document.getElementById('staffProfileToggle');
    const profileDropdown = document.getElementById('staffProfileDropdown');

    if (profileToggle && profileDropdown) {
        profileToggle.addEventListener('click', function (event) {
            if (event.target.closest('.staff-profile-dropdown')) {
                return;
            }

            profileDropdown.classList.toggle('show');
            event.stopPropagation();
        });

        document.addEventListener('click', function () {
            profileDropdown.classList.remove('show');
        });
    }
});

let currentMonth = new Date().getMonth() + 1;
let currentYear = new Date().getFullYear();
let statusChart = null;

const sidebar = document.getElementById('staffSidebar');
const menuButton = document.getElementById('mobileMenuButton');
const searchInput = document.getElementById('searchInput');
const projectButtons = document.querySelectorAll('[data-project-filter]');
const taskButtons = document.querySelectorAll('[data-task-filter]');
const projectEmpty = document.getElementById('projectEmpty');
const taskEmpty = document.getElementById('taskEmpty');

let projectFilter = 'all';
let taskFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    loadCalendarData(currentMonth, currentYear);

    document.getElementById('btnPrev').addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        loadCalendarData(currentMonth, currentYear);
    });

    document.getElementById('btnNext').addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        loadCalendarData(currentMonth, currentYear);
    });
});

if (menuButton) {
    menuButton.addEventListener('click', function() {
        sidebar.classList.toggle('open');
    });
}

if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
}

projectButtons.forEach(function(button) {
    button.addEventListener('click', function() {
        projectFilter = button.dataset.projectFilter;
        setActiveButton(projectButtons, button);
        applyFilters();
    });
});

taskButtons.forEach(function(button) {
    button.addEventListener('click', function() {
        taskFilter = button.dataset.taskFilter;
        setActiveButton(taskButtons, button);
        applyFilters();
    });
});

function setActiveButton(buttons, activeButton) {
    buttons.forEach(function(button) {
        button.classList.toggle('active', button === activeButton);
    });
}

function applyFilters() {
    const keyword = (searchInput ? searchInput.value.toLowerCase().trim() : '');
    let visibleProjects = 0;
    let visibleTasks = 0;

    document.querySelectorAll('.project-card').forEach(function(card) {
        const searchText = card.dataset.search || '';
        const status = card.dataset.status || '';
        const keywordMatch = !keyword || searchText.includes(keyword);
        const filterMatch = projectFilter === 'all' || status.includes(projectFilter);
        const show = keywordMatch && filterMatch;
        card.style.display = show ? '' : 'none';
        if (show) visibleProjects++;
    });

    document.querySelectorAll('.task-row').forEach(function(row) {
        const searchText = row.dataset.search || '';
        const state = row.dataset.state || 'all';
        const keywordMatch = !keyword || searchText.includes(keyword);
        const filterMatch = taskFilter === 'all' || state === taskFilter;
        const show = keywordMatch && filterMatch;
        row.style.display = show ? '' : 'none';
        if (show) visibleTasks++;
    });

    if (projectEmpty) projectEmpty.style.display = visibleProjects ? 'none' : 'block';
    if (taskEmpty) taskEmpty.style.display = visibleTasks ? 'none' : 'block';
}

function loadCalendarData(month, year) {
    fetch(`index.php?page=staff-calendar&staff_action=get_calendar&month=${month}&year=${year}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) return;
            renderCalendar(data);
            renderChart(data.chartData);
        })
        .catch(() => {
            renderCalendar({ month, year, firstDay: 1, daysInMonth: 30, calendarTasks: {}, today: {} });
            renderChart([0, 0, 0, 0]);
        });
}

function renderCalendar(data) {
    const title = document.getElementById('calendarTitle');
    const grid = document.getElementById('calendarGrid');

    title.textContent = `Tháng ${data.month}/${data.year}`;
    grid.innerHTML = '';

    for (let i = 1; i < data.firstDay; i++) {
        const emptyCell = document.createElement('div');
        // Đúng tên class CSS thật ("empty-day"), trước đây dùng "muted-day" không có style nào áp dụng.
        emptyCell.className = 'calendar-day empty-day';
        grid.appendChild(emptyCell);
    }

    for (let day = 1; day <= data.daysInMonth; day++) {
        const cell = document.createElement('div');
        const isToday = data.today && day === data.today.day && data.month === data.today.month && data.year === data.today.year;
        const tasks = data.calendarTasks && data.calendarTasks[day] ? data.calendarTasks[day] : [];
        // Thêm "has-task" (CSS đã có sẵn .calendar-day.has-task) để ngày có deadline được tô nền nhẹ.
        cell.className = 'calendar-day' + (isToday ? ' today' : '') + (tasks.length ? ' has-task' : '');

        const dayNumber = document.createElement('strong');
        dayNumber.textContent = day;
        cell.appendChild(dayNumber);

        const cellDate = new Date(data.year, data.month - 1, day);
        const isPast = cellDate < new Date(new Date().toDateString());

        tasks.slice(0, 2).forEach(function(task) {
            const badge = document.createElement('span');
            // Tô màu theo trạng thái: Hoàn thành = xanh lá, quá hạn = đỏ, còn lại = xanh dương
            // (dùng đúng modifier .primary/.success/.danger đã có sẵn trong CSS cho .calendar-task).
            const badgeClass = task.status === 'Completed' ? 'success' : (isPast ? 'danger' : 'primary');
            badge.className = 'calendar-task ' + badgeClass;
            badge.textContent = `#${task.task_id} ${task.title}`;
            cell.appendChild(badge);
        });

        if (tasks.length > 2) {
            const more = document.createElement('span');
            // Đúng tên class CSS thật ("more-task"), trước đây dùng "calendar-more" không có style nào áp dụng.
            more.className = 'more-task';
            more.textContent = `+${tasks.length - 2} task`;
            cell.appendChild(more);
        }

        grid.appendChild(cell);
    }
}

function renderChart(chartData) {
    const canvas = document.getElementById('statusChart');
    if (!canvas || typeof Chart === 'undefined') return;

    if (statusChart) {
        statusChart.destroy();
    }

    const total = Array.isArray(chartData)
        ? chartData.reduce((sum, value) => sum + Number(value || 0), 0)
        : 0;

    const chartLabels = total > 0
        ? ['Chờ xử lý', 'Đang thực hiện', 'Review', 'Hoàn thành']
        : ['Chưa có task'];

    const chartValues = total > 0
        ? chartData
        : [1];

    const chartColors = total > 0
        ? ['#94a3b8', '#4f6df5', '#ff9800', '#10c490']
        : ['#e2e8f0'];

    statusChart = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: chartLabels,
            datasets: [{
                data: chartValues,
                backgroundColor: chartColors,
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        usePointStyle: true
                    }
                }
            },
            cutout: '68%'
        }
    });
}
</script>
<script src="assets/js/ajax-app.js"></script>
<script>window.CURRENT_STAFF_ID = <?= (int)$staffId ?>;</script>
<script src="assets/js/staff_modals.js?v=<?= time() ?>"></script>
</body>
</html>