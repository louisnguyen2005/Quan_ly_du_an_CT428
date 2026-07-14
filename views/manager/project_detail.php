<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../../config/database.php';
$db = new Database();
$pdo = $db->connect();
$managerId = (int)($_SESSION['user_id'] ?? 0);
$manager = manager_fetch_current($pdo, $managerId);
$id = (int)($_GET['id'] ?? 0);
$owned = manager_owned_project_condition('p', ':mid');
$sidebarCounts = manager_sidebar_counts($pdo, $managerId);
$sidebarProjectCount = $sidebarCounts['projects'];
$sidebarTaskCount = $sidebarCounts['tasks'];
$sidebarNotifCount = $sidebarCounts['notifications'];
$stmt = $pdo->prepare("SELECT p.*, COALESCE(u.username,'manager') manager_name,(SELECT COUNT(*) FROM tasks t WHERE t.project_id=p.project_id AND t.is_deleted=0) total_tasks,(SELECT COUNT(*) FROM tasks t WHERE t.project_id=p.project_id AND t.status='Completed' AND t.is_deleted=0) completed_tasks FROM projects p LEFT JOIN users u ON u.user_id=COALESCE(p.manager_id,p.created_by) WHERE p.project_id=:id AND p.is_deleted=0 AND $owned LIMIT 1");
$stmt->execute(['id' => $id, 'mid' => $managerId]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) {
    die('Dự án không tồn tại hoặc bạn không có quyền xem.');
}
$progress = $project['total_tasks'] > 0 ? round($project['completed_tasks'] / $project['total_tasks'] * 100) : (int)($project['progress'] ?? 0);
$stmt = $pdo->prepare("SELECT u.user_id,u.username,pm.role_in_project FROM project_members pm JOIN users u ON u.user_id=pm.user_id WHERE pm.project_id=? ORDER BY pm.role_in_project DESC,u.username");
$stmt->execute([$id]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
$memberIds = array_map(fn($m) => (int)$m['user_id'], $members);
$staffs = $pdo->query("SELECT u.user_id,u.username FROM users u JOIN roles r ON r.role_id=u.role_id WHERE r.role_code='STAFF' AND u.is_deleted=0 ORDER BY u.username")->fetchAll(PDO::FETCH_ASSOC);
function statusVN($status)
{
    return match ($status) {
        'Planning' => 'Lập kế hoạch',
        'In Progress' => 'Đang thực hiện',
        'Completed' => 'Hoàn thành',
        default => $status
    };
}

function priorityVN($priority)
{
    return match ($priority) {
        'Low' => 'Thấp',
        'Medium' => 'Trung bình',
        'High' => 'Cao',
        default => $priority
    };
}
manager_page_head('Chi tiết dự án'); ?>
<div class="manager-shell">
    <?php manager_sidebar($manager, 'projects', $sidebarProjectCount, $sidebarTaskCount, $sidebarNotifCount); ?>
    <main class="manager-main-content"><?php manager_topbar('Chi tiết dự án', $manager, $sidebarNotifCount); ?>
        <section class="manager-content-area">
            <div class="project-detail-header">
                <div class="project-detail-breadcrumb">
                    <a href="index.php?page=projects">Dự án</a>
                    <i class="bi bi-chevron-right"></i>
                    <span>Chi tiết dự án</span>
                </div>
                <div class="project-detail-actions">
                    <button class="manager-btn light" onclick="openModal('editProjectModal')">
                        <i class="bi bi-pencil-square"></i>
                        Chỉnh sửa
                    </button>
                    <button type="button" class="manager-btn danger" onclick="openDeleteProjectModal(<?= $id ?>)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="project-detail-hero">
                <div class="project-detail-header">
                    <div class="project-detail-folder"><i class="bi bi-folder-fill"></i></div>
                    <div class="project-detail-info">
                        <div class="project-detail-title-row">
                            <h1><?= mh($project['name']) ?></h1>
                            <div class="project-tags">
                                <span class="badge-pill <?= mclass($project['status']) ?>">
                                    <?= statusVN($project['status']) ?>
                                </span>
                                <span class="priority-badge priority-<?= strtolower($project['priority']) ?>">
                                    <?= priorityVN($project['priority']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="project-detail-code">Mã dự án: <strong><?= mh($project['project_code']) ?></strong>
                        </div>
                    </div>
                </div>
                <div class="project-detail-meta">
                    <div class="project-detail-meta-item">
                        <div class="project-detail-label">Ngày bắt đầu</div>
                        <div class="project-detail-value"><?= md($project['start_date']) ?></div>
                    </div>
                    <div class="project-detail-meta-item">
                        <div class="project-detail-label">Ngày kết thúc</div>
                        <div class="project-detail-value"><?= md($project['end_date']) ?></div>
                    </div>
                    <div class="project-detail-meta-item">
                        <div class="project-detail-label">Quản lý dự án</div>
                        <div class="project-detail-value"><?= mh($project['manager_name']) ?></div>
                    </div>
                    <div class="project-detail-meta-item">
                        <div class="project-detail-label">Thành viên</div>
                        <div class="project-detail-value"><?= count($members) ?> người</div>
                    </div>
                </div>
                <div class="project-detail-progress">
                    <div class="project-detail-progress-header">
                        <span>Tiến độ dự án</span>
                        <strong><?= $progress ?>%</strong>
                    </div>
                    <div class="project-detail-progress-bar">
                        <span style="width:<?= $progress ?>%"></span>
                    </div>
                </div>
            </div>
            <div class="project-detail-grid">

                <div>

                    <div class="project-detail-card">

                        <h2>Mô tả dự án</h2>

                        <p><?= nl2br(mh($project['description'])) ?></p>

                    </div>

                </div>

                <div>

                    <div class="project-detail-card">

                        <h2>Thành viên dự án</h2>

                        <?php foreach ($members as $m): ?>

                            <div class="project-detail-member">

                                <div class="project-detail-avatar">
                                    <?= strtoupper(substr($m['username'], 0, 1)) ?>
                                </div>

                                <div>

                                    <strong><?= mh($m['username']) ?></strong>

                                    <div class="project-detail-role">
                                        <?= mh($m['role_in_project']) ?>
                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>
        </section>
    </main>
</div>
<div class="manager-modal-backdrop" id="editProjectModal">
    <div class="manager-modal">
        <div class="manager-modal-header">
            <h2>Chỉnh sửa dự án</h2><button class="manager-btn light icon" data-close-modal="editProjectModal"><i
                    class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="index.php?action=managerUpdateProject"><input type="hidden" name="project_id"
                value="<?= $id ?>">
            <div class="manager-modal-body">
                <div class="form-grid">
                    <div class="form-group full"><label>Tên dự án</label><input name="name"
                            value="<?= mh($project['name']) ?>" required></div>
                    <div class="form-group full"><label>Mô tả dự án</label><textarea name="description"
                            rows="5"><?= mh($project['description']) ?></textarea></div>
                    <div class="form-group"><label>Ngày bắt đầu</label><input type="date" name="start_date"
                            value="<?= mh($project['start_date']) ?>"></div>
                    <div class="form-group"><label>Ngày kết thúc</label><input type="date" name="end_date"
                            value="<?= mh($project['end_date']) ?>"></div>
                    <div class="form-group full">
                        <label>Thành viên dự án</label>

                        <div class="staff-check-list">

                            <?php foreach ($staffs as $s): ?>

                                <label class="staff-item">

                                    <div class="staff-info">

                                        <input type="checkbox" class="edit-member" name="members[]"
                                            value="<?= $s['user_id'] ?>">

                                        <div class="staff-avatar">
                                            <?= strtoupper(substr($s['username'], 0, 1)) ?>
                                        </div>

                                        <span><?= mh($s['username']) ?></span>

                                    </div>

                                </label>

                            <?php endforeach; ?>

                        </div>

                    </div>
                </div>
            </div>
            <div class="manager-modal-footer"><button type="button" class="manager-btn light"
                    data-close-modal="editProjectModal">Hủy</button><button class="manager-btn primary">Cập
                    nhật</button></div>
        </form>
    </div>
</div>
<div class="manager-modal-backdrop" id="deleteProjectModal">
    <div class="manager-modal delete-modal">
        <div class="manager-modal-header">
            <h2>Xóa dự án</h2>
        </div>
        <div class="manager-modal-body">
            <div class="delete-icon">
                <i class="bi bi-trash"></i>
            </div>
            <h3>Bạn có chắc muốn xóa dự án này?</h3>
            <p>
                Dự án và các dữ liệu liên quan sẽ bị xóa khỏi hệ thống.
                Hành động này không thể hoàn tác.
            </p>
        </div>
        <div class="manager-modal-footer">
            <button type="button" class="manager-btn light" data-close-modal="deleteProjectModal">
                Hủy
            </button>
            <a id="deleteProjectLink" class="manager-btn danger">
                Xóa dự án
            </a>
        </div>
    </div>
</div>
<script>
    function openDeleteProjectModal(id) {

        document.getElementById("deleteProjectLink").href =
            "index.php?action=managerDeleteProject&id=" + id;

        openModal("deleteProjectModal");

    }
</script>
<?php manager_layout_script(); ?>