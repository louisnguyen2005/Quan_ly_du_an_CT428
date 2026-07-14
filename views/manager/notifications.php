<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$pdo = $db->connect();

$managerId = (int)($_SESSION['user_id'] ?? 0);
if ($managerId <= 0 || (int)($_SESSION['role_id'] ?? 0) !== 2) {
    header('Location:index.php?page=login');
    exit;
}

$manager = manager_fetch_current($pdo, $managerId);

$sidebarCounts = manager_sidebar_counts($pdo, $managerId);
$projectCount = $sidebarCounts['projects'];
$taskCount = $sidebarCounts['tasks'];
$notifCount = $sidebarCounts['notifications'];
$notificationPrefs = $sidebarCounts['preferences'];

$type = $_GET['type'] ?? 'all';
$sql = "SELECT * FROM notifications n WHERE n.user_id = ?";
$params = [$managerId];
if ($type !== 'all') {
    $sql .= " AND n.type = ?";
    $params[] = $type;
}
$sql .= manager_notification_filter_sql($notificationPrefs, 'n');
$sql .= " ORDER BY n.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

manager_page_head('Thông báo');
?>

<style>
.manager-content-area.manager-notifications-page{
    padding:38px;
    background:#f3f6fb;
    min-height:calc(100vh - 86px);
}
.manager-notifications-page .manager-page-title-row{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:20px;
    margin-bottom:28px;
}
.manager-notifications-page .manager-page-title-row h1{
    margin:0 0 8px;
    font-size:34px;
    line-height:1.15;
    font-weight:900;
    color:#0f172a;
}
.manager-notifications-page .manager-page-title-row p{
    margin:0;
    color:#64748b;
    font-size:15px;
}
.manager-filter-inline select{
    min-width:150px;
    height:46px;
    padding:0 16px;
    border:1px solid #dbe3ef;
    border-radius:12px;
    background:#fff;
    color:#0f172a;
    font-weight:700;
    outline:none;
    cursor:pointer;
}
.manager-notification-list{
    display:flex;
    flex-direction:column;
    gap:18px;
}
.manager-notification-card{
    display:flex;
    align-items:flex-start;
    gap:18px;
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:22px;
    padding:24px 26px;
    box-shadow:0 8px 24px rgba(15,23,42,.05);
    transition:.2s ease;
}
.manager-notification-card:hover{
    transform:translateY(-2px);
    box-shadow:0 14px 34px rgba(15,23,42,.09);
}
.manager-notification-card.unread{
    border-color:#bfdbfe;
    background:linear-gradient(90deg,#eff6ff 0,#fff 42%);
}
.manager-notification-icon{
    width:48px;
    height:48px;
    border-radius:16px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
    font-size:21px;
}
.manager-notification-icon.task{background:#dbeafe;color:#2563eb;}
.manager-notification-icon.comment{background:#ffedd5;color:#f97316;}
.manager-notification-icon.system{background:#dcfce7;color:#16a34a;}
.manager-notification-content{flex:1;min-width:0;}
.manager-notification-head{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:7px;
}
.manager-notification-head h4{
    margin:0;
    font-size:18px;
    font-weight:900;
    color:#0f172a;
}
.manager-notification-content p{
    margin:0 0 14px;
    color:#64748b;
    line-height:1.55;
    font-size:15px;
}
.manager-notification-time{
    display:inline-flex;
    align-items:center;
    gap:7px;
    color:#94a3b8;
    font-size:13px;
    font-weight:700;
}
.manager-unread-pill{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:4px 10px;
    border-radius:999px;
    background:#ef4444;
    color:#fff;
    font-size:11px;
    font-weight:900;
}
body.dark-mode .manager-content-area.manager-notifications-page{background:#0f172a;}
body.dark-mode .manager-notification-card{background:#1e293b;border-color:#334155;color:#f8fafc;}
body.dark-mode .manager-notification-card.unread{background:linear-gradient(90deg,#172554 0,#1e293b 42%);border-color:#2563eb;}
body.dark-mode .manager-notification-head h4,
body.dark-mode .manager-notifications-page .manager-page-title-row h1{color:#f8fafc;}
body.dark-mode .manager-notification-content p,
body.dark-mode .manager-notification-time,
body.dark-mode .manager-notifications-page .manager-page-title-row p{color:#94a3b8;}
body.dark-mode .manager-filter-inline select{background:#1e293b;color:#f8fafc;border-color:#334155;}
</style>

<div class="manager-shell">
    <?php manager_sidebar($manager, 'notifications', $projectCount, $taskCount, $notifCount); ?>

    <main class="manager-main-content">
        <?php manager_topbar('Thông báo', $manager, $notifCount); ?>

        <section class="manager-content-area manager-notifications-page">
            <div class="manager-page-title-row">
                <div>
                    <h1>Thông báo</h1>
                    <p>Các thông báo mới nhất dành cho bạn</p>
                </div>

                <form method="GET" class="manager-filter-inline">
                    <input type="hidden" name="page" value="notifications">
                    <select name="type" onchange="this.form.submit()">
                        <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>Tất cả</option>
                        <option value="task" <?= $type === 'task' ? 'selected' : '' ?>>Task</option>
                        <option value="comment" <?= $type === 'comment' ? 'selected' : '' ?>>Comment</option>
                        <option value="system" <?= $type === 'system' ? 'selected' : '' ?>>System</option>
                    </select>
                </form>
            </div>

            <div class="manager-notification-list">
                <?php if (empty($notifications)): ?>
                    <div class="manager-notification-card empty">
                        <div class="manager-notification-content">
                            <h4>Chưa có thông báo</h4>
                            <p>Hiện tại chưa có thông báo nào để hiển thị.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $item): ?>
                        <?php
                            $nType = $item['type'] ?? 'system';
                            $iconClass = 'system';
                            $icon = 'bi-info-circle';
                            if ($nType === 'task') {
                                $iconClass = 'task';
                                $icon = 'bi-list-check';
                            } elseif ($nType === 'comment') {
                                $iconClass = 'comment';
                                $icon = 'bi-chat-dots';
                            }
                        ?>
                        <div class="manager-notification-card <?= ((int)($item['is_read'] ?? 0) === 0) ? 'unread' : '' ?>" data-type="<?= mh($nType) ?>">
                            <div class="manager-notification-icon <?= $iconClass ?>">
                                <i class="bi <?= $icon ?>"></i>
                            </div>

                            <div class="manager-notification-content">
                                <div class="manager-notification-head">
                                    <h4><?= mh($item['title'] ?? '') ?></h4>
                                    <?php if ((int)($item['is_read'] ?? 0) === 0): ?>
                                        <span class="manager-unread-pill">Mới</span>
                                    <?php endif; ?>
                                </div>

                                <p><?= mh($item['content'] ?? '') ?></p>

                                <span class="manager-notification-time">
                                    <i class="bi bi-clock"></i>
                                    <?= !empty($item['created_at']) ? date('d/m/Y H:i', strtotime($item['created_at'])) : '' ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
<?php manager_layout_script(); ?>
