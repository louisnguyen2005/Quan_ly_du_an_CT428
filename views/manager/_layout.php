<?php
function mh($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function md($d){ return !empty($d) ? date('d/m/Y', strtotime($d)) : '--'; }
function mstatus($s){ return match($s){'Pending'=>'Chưa bắt đầu','In Progress'=>'Đang thực hiện','Review'=>'Đang review','Completed'=>'Hoàn thành','Pending Approval'=>'Chờ duyệt','Approved'=>'Đã duyệt','Rejected'=>'Từ chối','Planning'=>'Đang thực hiện', default=>$s ?: 'Chưa rõ'}; }
function mprio($p){ return match($p){'Critical'=>'Khẩn cấp','High'=>'Cao','Medium'=>'Trung bình','Low'=>'Thấp', default=>$p ?: 'Trung bình'}; }
function mclass($v){ return strtolower(str_replace([' ','_'], '-', (string)($v ?: 'pending'))); }

function manager_fetch_current(PDO $pdo, int $managerId): array {
    $s=$pdo->prepare("SELECT u.*, r.name AS role_name FROM users u LEFT JOIN roles r ON u.role_id=r.role_id WHERE u.user_id=? LIMIT 1");
    $s->execute([$managerId]);
    return $s->fetch(PDO::FETCH_ASSOC) ?: [];
}

function manager_owned_project_condition(string $alias = 'p', string $param = ':mid'): string {
    // PDO native prepared statements do not allow reusing the same named
    // placeholder multiple times in one SQL statement. Generate three unique
    // placeholders for created_by, manager_id and project_members.user_id.
    $base = ltrim($param, ':');
    $createdParam = ':' . $base . '_created';
    $managerParam = ':' . $base . '_manager';
    $memberParam = ':' . $base . '_member';

    return "($alias.created_by = $createdParam OR $alias.manager_id = $managerParam OR EXISTS(
        SELECT 1
        FROM project_members pmx
        WHERE pmx.project_id = $alias.project_id
          AND pmx.user_id = $memberParam
          AND (pmx.role_in_project LIKE '%Manager%' OR pmx.role_in_project = 'Leader' OR pmx.role_in_project = 'Manager')
    ))";
}

function manager_owned_project_params(int $managerId, string $param = ':mid'): array {
    $base = ltrim($param, ':');

    return [
        $base . '_created' => $managerId,
        $base . '_manager' => $managerId,
        $base . '_member' => $managerId,
    ];
}

function manager_notification_preferences(PDO $pdo, int $userId): array {
    $pdo->exec("CREATE TABLE IF NOT EXISTS notification_preferences (
        user_id INT PRIMARY KEY,
        email_notifications TINYINT(1) NOT NULL DEFAULT 1,
        task_notifications TINYINT(1) NOT NULL DEFAULT 1,
        comment_notifications TINYINT(1) NOT NULL DEFAULT 1,
        deadline_notifications TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_notification_preferences_user
            FOREIGN KEY (user_id) REFERENCES users(user_id)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $ins = $pdo->prepare("INSERT IGNORE INTO notification_preferences(user_id) VALUES(?)");
    $ins->execute([$userId]);

    $stmt = $pdo->prepare("SELECT * FROM notification_preferences WHERE user_id=? LIMIT 1");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'email_notifications' => 1,
        'task_notifications' => 1,
        'comment_notifications' => 1,
        'deadline_notifications' => 1,
    ];
}

function manager_notification_filter_sql(array $prefs, string $alias = 'n'): string {
    $conditions = [];

    if ((int)($prefs['task_notifications'] ?? 1) === 0) {
        $conditions[] = "LOWER(COALESCE($alias.type, '')) LIKE '%task%'";
    }

    if ((int)($prefs['comment_notifications'] ?? 1) === 0) {
        $conditions[] = "LOWER(COALESCE($alias.type, '')) LIKE '%comment%'";
    }

    if ((int)($prefs['deadline_notifications'] ?? 1) === 0) {
        $conditions[] = "(LOWER(COALESCE($alias.type, '')) LIKE '%deadline%' OR LOWER(COALESCE($alias.type, '')) LIKE '%due%')";
    }

    if (empty($conditions)) {
        return "";
    }

    return " AND NOT (" . implode(' OR ', $conditions) . ")";
}

function manager_sidebar_counts(PDO $pdo, int $managerId): array {
    $owned = manager_owned_project_condition('p', ':mid');

    $projects = $pdo->prepare("SELECT COUNT(*) FROM projects p WHERE p.is_deleted = 0 AND $owned");
    $projects->execute(manager_owned_project_params($managerId));
    $projectCount = (int)$projects->fetchColumn();

    $tasks = $pdo->prepare("SELECT COUNT(*)
        FROM tasks t
        JOIN projects p ON p.project_id = t.project_id
        WHERE t.is_deleted = 0
          AND p.is_deleted = 0
          AND t.status <> 'Completed'
          AND $owned");
    $tasks->execute(manager_owned_project_params($managerId));
    $taskCount = (int)$tasks->fetchColumn();

    $pendingApprovals = $pdo->prepare("SELECT COUNT(*)
        FROM tasks t
        JOIN projects p ON p.project_id = t.project_id
        WHERE t.is_deleted = 0
          AND p.is_deleted = 0
          AND t.status = 'Pending Approval'
          AND $owned");
    $pendingApprovals->execute(manager_owned_project_params($managerId));
    $pendingApprovalCount = (int)$pendingApprovals->fetchColumn();

    $prefs = manager_notification_preferences($pdo, $managerId);
    $filter = manager_notification_filter_sql($prefs, 'n');
    $notif = $pdo->prepare("SELECT COUNT(*) FROM notifications n WHERE n.user_id = ? AND n.is_read = 0 $filter");
    $notif->execute([$managerId]);
    $notifCount = (int)$notif->fetchColumn();

    return [
        'projects' => $projectCount,
        'tasks' => $taskCount,
        'pending_approvals' => $pendingApprovalCount,
        'notifications' => $notifCount,
        'preferences' => $prefs,
    ];
}

function manager_sidebar(array $manager, string $active='overview', int $projectCount=0, int $taskCount=0, int $notifCount=0, int $pendingApprovalCount=0): void { ?>
<aside class="manager-sidebar">
    <div class="manager-logo-section">
        <div class="manager-logo-box"><i class="bi bi-briefcase-fill"></i></div>
        <div class="manager-logo-text"><h4>ProjectHub</h4><span>Manager workspace</span></div>
    </div>
    <nav class="manager-menu">
        <a href="index.php?page=manager-dashboard" class="manager-menu-item <?= $active==='overview'?'active':'' ?>"><i class="bi bi-house-fill"></i><span>Tổng quan</span></a>
        <a href="index.php?page=projects" class="manager-menu-item <?= $active==='projects'?'active':'' ?>"><i class="bi bi-folder"></i><span>Dự án</span><span class="manager-badge-count"><?= $projectCount ?></span></a>
        <a href="index.php?page=tasks" class="manager-menu-item <?= $active==='tasks'?'active':'' ?>"><i class="bi bi-check2-square"></i><span>Nhiệm vụ</span><span class="manager-badge-count"><?= $taskCount ?></span></a>
        <a href="index.php?page=manager-pending-approval" class="manager-menu-item <?= $active==='pending-approval'?'active':'' ?>"><i class="bi bi-hourglass-split"></i><span>Chờ phê duyệt</span><span class="manager-badge-count red"><?= $pendingApprovalCount ?></span></a>
        <a href="index.php?page=notifications" class="manager-menu-item <?= $active==='notifications'?'active':'' ?>"><i class="bi bi-bell"></i><span>Thông báo</span><span class="manager-badge-count red"><?= $notifCount ?></span></a>
    </nav>
    <div class="manager-sidebar-divider"></div>
    <div class="manager-user-box">
        <div class="manager-avatar"><?= strtoupper(substr($manager['username'] ?? 'M',0,1)) ?></div>
        <div class="manager-user-info"><strong><?= mh($manager['username'] ?? 'manager') ?></strong><small>Manager</small></div>
    </div>
</aside>
<?php }
function manager_page_head(string $title): void { $theme=$_COOKIE['theme']??'light'; ?>
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><?= csrf_meta_tag() ?><title><?= mh($title) ?> - ProjectHub</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="assets/css/dashboard_manager.css"><link rel="stylesheet" href="assets/css/manager_crud.css"><script src="https://cdn.jsdelivr.net/npm/chart.js"></script></head><body class="<?= $theme==='dark'?'dark-mode':'' ?>">
<?php }
function manager_topbar(string $title, array $manager, int $notifCount=0): void { ?>
<header class="manager-topbar"><div class="manager-topbar-left"><h3><?= mh($title) ?></h3></div><div class="manager-topbar-right"><div class="manager-search-box"><i class="bi bi-search"></i><input type="text" id="managerGlobalSearch" placeholder="Tìm kiếm dự án, nhiệm vụ..."></div><a class="manager-notification-btn" href="index.php?page=notifications"><i class="bi bi-bell"></i><?php if($notifCount>0): ?><span class="manager-notification-badge"><?= $notifCount ?></span><?php endif; ?></a><div class="manager-profile-menu" id="managerProfileToggle"><div class="manager-profile-avatar"><?= strtoupper(substr($manager['username'] ?? 'M',0,1)) ?></div><div class="manager-profile-meta"><strong><?= mh($manager['username'] ?? 'manager') ?></strong><span>Manager</span></div><div class="manager-profile-dropdown" id="managerProfileDropdown"><div class="manager-dropdown-head"><strong><?= mh($manager['username'] ?? 'manager') ?></strong><span>Manager</span></div><a href="index.php?page=manager-profile"><i class="bi bi-person"></i> Hồ sơ cá nhân</a><a href="index.php?page=manager-settings"><i class="bi bi-gear"></i> Cài đặt</a><a href="index.php?page=logout" class="logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></div></div></div></header>
<?php }
function manager_layout_script(): void { ?>
<script>
const profileToggle=document.getElementById('managerProfileToggle');
const profileDropdown=document.getElementById('managerProfileDropdown');
if(profileToggle&&profileDropdown){profileToggle.addEventListener('click',e=>{e.stopPropagation();profileDropdown.classList.toggle('show')});document.addEventListener('click',()=>profileDropdown.classList.remove('show'));}
function openModal(id){const m=document.getElementById(id); if(m) m.classList.add('show');}
function closeModal(id){const m=document.getElementById(id); if(m) m.classList.remove('show');}
document.querySelectorAll('[data-close-modal]').forEach(btn=>btn.addEventListener('click',()=>closeModal(btn.dataset.closeModal)));
document.querySelectorAll('.manager-modal-backdrop').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('show')}));
function confirmDelete(msg){return confirm(msg||'Bạn có chắc muốn xóa?');}
</script>
<script src="assets/js/ajax-app.js"></script>
</body></html>
<?php }
?>
