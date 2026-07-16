<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../../config/database.php';
$db = new Database();
$pdo = $db->connect();
$managerId = (int)($_SESSION['user_id'] ?? 0);
$manager = manager_fetch_current($pdo, $managerId);
$owned = manager_owned_project_condition('p', ':mid');
$sidebarCounts = manager_sidebar_counts($pdo, $managerId);
$sidebarProjectCount = $sidebarCounts['projects'];
$sidebarTaskCount = $sidebarCounts['tasks'];
$sidebarNotifCount = $sidebarCounts['notifications'];
$sidebarPendingApprovalCount = $sidebarCounts['pending_approvals'];
$cnt = $pdo->prepare("SELECT COUNT(*) FROM projects p WHERE p.is_deleted=0 AND $owned");
$cnt->execute(['mid' => $managerId]);
$total = (int)$cnt->fetchColumn();
$doing = $pdo->prepare("SELECT COUNT(*) FROM projects p WHERE p.is_deleted=0 AND $owned AND CURDATE() BETWEEN p.start_date AND p.end_date");
$doing->execute(['mid' => $managerId]);
$doing = (int)$doing->fetchColumn();
$future = $pdo->prepare("SELECT COUNT(*) FROM projects p WHERE p.is_deleted=0 AND $owned AND p.start_date > CURDATE()");
$future->execute(['mid' => $managerId]);
$future = (int)$future->fetchColumn();
$done = $pdo->prepare("SELECT COUNT(*) FROM projects p WHERE p.is_deleted=0 AND $owned AND p.end_date < CURDATE()");
$done->execute(['mid' => $managerId]);
$done = (int)$done->fetchColumn();
$stmt = $pdo->prepare("SELECT p.*, COALESCE(u.username,'manager') AS manager_name,(SELECT COUNT(*) FROM tasks t WHERE t.project_id=p.project_id AND t.is_deleted=0) total_tasks,(SELECT COUNT(*) FROM tasks t WHERE t.project_id=p.project_id AND t.status='Completed' AND t.is_deleted=0) completed_tasks FROM projects p LEFT JOIN users u ON u.user_id=COALESCE(p.manager_id,p.created_by) WHERE p.is_deleted=0 AND $owned ORDER BY p.created_at DESC");
$stmt->execute(['mid' => $managerId]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
$staffStmt = $pdo->query("SELECT u.user_id,u.username FROM users u JOIN roles r ON r.role_id=u.role_id WHERE r.role_code='STAFF' AND u.is_deleted=0 ORDER BY u.username");
$staffs = $staffStmt->fetchAll(PDO::FETCH_ASSOC);
manager_page_head('Dự án'); ?>
<div class="manager-shell"><?php manager_sidebar($manager, 'projects', $sidebarProjectCount, $sidebarTaskCount, $sidebarNotifCount, $sidebarPendingApprovalCount); ?><main
        class="manager-main-content"><?php manager_topbar('Dự án', $manager, $sidebarNotifCount); ?>
        <section class="manager-content-area">
            <div class="manager-stat-row">

                <div class="project-stat-card border-blue">
                    <div class="stat-icon stat-blue">
                        <i class="bi bi-folder2-open"></i>
                    </div>

                    <div class="stat-content">
                        <div class="stat-title">Tổng dự án</div>
                        <div class="stat-number"><?= $total ?></div>
                        <div class="stat-sub">Tất cả dự án</div>
                    </div>
                </div>

                <div class="project-stat-card border-orange">
                    <div class="stat-icon stat-yellow">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>

                    <div class="stat-content">
                        <div class="stat-title">Đang thực hiện</div>
                        <div class="stat-number"><?= $doing ?></div>
                        <div class="stat-sub">Dự án đang triển khai</div>
                    </div>
                </div>

                <div class="project-stat-card border-purple">
                    <div class="stat-icon stat-purple">
                        <i class="bi bi-pause-circle-fill"></i>
                    </div>

                    <div class="stat-content">
                        <div class="stat-title">Chưa bắt đầu</div>
                        <div class="stat-number"><?= $future ?></div>
                        <div class="stat-sub">Đang chờ thực hiện</div>
                    </div>
                </div>

                <div class="project-stat-card border-green">
                    <div class="stat-icon stat-green">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <div class="stat-content">
                        <div class="stat-title">Hoàn thành</div>
                        <div class="stat-number"><?= $done ?></div>
                        <div class="stat-sub">Dự án đã hoàn thành</div>
                    </div>
                </div>

            </div>
            <div class="manager-filter-row">

                <input id="projectSearch" placeholder="Tìm kiếm dự án...">

                <select id="projectStatus" class="filter-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Planning">Lập kế hoạch</option>
                    <option value="In Progress">Đang thực hiện</option>
                    <option value="Completed">Hoàn thành</option>
                </select>

                <select id="projectSort" class="filter-select">
                    <option value="new">Mới tạo gần đây</option>
                    <option value="old">Tạo lâu nhất</option>
                    <option value="az">Tên A → Z</option>
                    <option value="za">Tên Z → A</option>
                </select>

                <button class="manager-btn light" id="refreshProject">
                    <i class="bi bi-arrow-clockwise"></i>
                    Làm mới
                </button>

                <button class="manager-btn add-project-btn" onclick="openModal('addProjectModal')">
                    <i class="bi bi-plus-lg"></i>
                    Thêm dự án
                </button>

            </div>
            <div class="project-card-grid" id="projectGrid">
                <?php foreach ($projects as $p):

                    $progress = $p['total_tasks'] > 0
                        ? round($p['completed_tasks'] / $p['total_tasks'] * 100)
                        : (int)($p['progress'] ?? 0);

                    $st = $p['status'] ?: (strtotime($p['end_date']) < time() ? 'Completed' : 'In Progress');

                    $statusText = match ($st) {
                        'Planning' => 'Lập kế hoạch',
                        'In Progress' => 'Đang thực hiện',
                        'Completed' => 'Hoàn thành',
                        default => $st
                    };

                    $priorityText = match ($p['priority']) {
                        'Low' => 'Thấp',
                        'Medium' => 'Trung bình',
                        'High' => 'Cao',
                        default => $p['priority']
                    };

                ?>
                    <div class="manager-project-card" data-text="<?= mh(strtolower($p['name'] . ' ' . $st)) ?>"
                        data-status="<?= mh($st) ?>">
                        <div class="project-card-head">
                            <div class="project-folder"><i class="bi bi-folder-fill"></i></div>
                            <div class="project-info">

                                <h3><?= mh($p['name']) ?></h3>

                                <div class="project-code">
                                    Mã dự án:
                                    <strong class="danger-text">
                                        <?= mh($p['project_code']) ?>
                                    </strong>
                                </div>

                                <div class="project-tags">

                                    <span class="badge-pill <?= mclass($st) ?>">
                                        <?= $statusText ?>
                                    </span>

                                    <span class="priority-badge priority-<?= strtolower($p['priority']) ?>">
                                        <?= $priorityText ?>
                                    </span>

                                </div>

                            </div>
                        </div>
                        <div class="project-meta-grid">
                            <div>
                                <div class="meta-label">Ngày bắt đầu</div>
                                <div class="meta-val"><?= md($p['start_date']) ?></div>
                            </div>
                            <div>
                                <div class="meta-label">Ngày kết thúc</div>
                                <div class="meta-val"><?= md($p['end_date']) ?></div>
                            </div>
                        </div>
                        <div style="display:flex;gap:12px;align-items:center;margin:18px 0">
                            <div class="avatar-circle"><?= strtoupper(substr($p['manager_name'], 0, 1)) ?></div>
                            <div>
                                <div class="meta-label">Quản lý dự án</div>
                                <div class="meta-val"><?= mh($p['manager_name']) ?></div>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between"><span>Tiến độ:
                                <?= (int)$p['completed_tasks'] ?> / <?= (int)$p['total_tasks'] ?> nhiệm
                                vụ</span><strong><?= $progress ?>%</strong></div>
                        <div class="progress-line"><span style="width:<?= $progress ?>%"></span></div>
                        <div class="project-actions"><a class="project-detail-btn"
                                href="index.php?page=project-detail&id=<?= $p['project_id'] ?>">
                                <i class="bi bi-arrow-right-circle-fill"></i>
                                <span>Xem chi tiết</span>
                            </a><button class="manager-btn light"
                                onclick='fillProjectEdit(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i
                                    class="bi bi-pencil-square"></i></button><button type="button"
                                class="manager-btn danger" onclick="openDeleteProjectModal(<?= $p['project_id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>
<div class="manager-modal-backdrop" id="addProjectModal">
    <div class="manager-modal">
        <div class="manager-modal-header">
            <h2>Thêm dự án mới</h2><button class="manager-btn light icon" data-close-modal="addProjectModal"><i
                    class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="index.php?action=managerCreateProject">
            <div class="manager-modal-body">
                <div class="form-grid">
                    <div class="form-group full"><label>Tên dự án</label><input name="name" required
                            placeholder="Nhập tên dự án"></div>
                    <div class="form-group full"><label>Mô tả dự án</label><textarea name="description" rows="4"
                            placeholder="Nhập mô tả dự án"></textarea></div>
                    <div class="form-group"><label>Ngày bắt đầu</label><input type="date" name="start_date" required>
                    </div>
                    <div class="form-group"><label>Ngày kết thúc</label><input type="date" name="end_date" required>
                    </div>
                    <div class="form-group full">
                        <label>Thành viên dự án</label>
                        <div class="staff-check-list">
                            <?php foreach ($staffs as $s): ?>
                                <label class="staff-item">
                                    <div class="staff-info">
                                        <input type="checkbox" name="members[]" value="<?= $s['user_id'] ?>">
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
                    data-close-modal="addProjectModal">Hủy</button><button class="manager-btn primary">Lưu dự
                    án</button></div>
        </form>
    </div>
</div>
<div class="manager-modal-backdrop" id="editProjectModal">
    <div class="manager-modal">
        <div class="manager-modal-header">
            <h2>Chỉnh sửa dự án</h2><button class="manager-btn light icon" data-close-modal="editProjectModal"><i
                    class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="index.php?action=managerUpdateProject"><input type="hidden" name="project_id"
                id="edit_project_id">
            <div class="manager-modal-body">
                <div class="form-grid">
                    <div class="form-group full"><label>Tên dự án</label><input name="name" id="edit_project_name"
                            required></div>
                    <div class="form-group full"><label>Mô tả dự án</label><textarea name="description"
                            id="edit_project_description" rows="4"></textarea></div>
                    <div class="form-group"><label>Ngày bắt đầu</label><input type="date" name="start_date"
                            id="edit_start_date"></div>
                    <div class="form-group"><label>Ngày kết thúc</label><input type="date" name="end_date"
                            id="edit_end_date"></div>
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
    function fillProjectEdit(p) {
        document.getElementById('edit_project_id').value = p.project_id;
        document.getElementById('edit_project_name').value = p.name || '';
        document.getElementById('edit_project_description').value = p.description || '';
        document.getElementById('edit_start_date').value = p.start_date || '';
        document.getElementById('edit_end_date').value = p.end_date || '';
        openModal('editProjectModal')
    }
    const ps = document.getElementById('projectSearch'),
        pf = document.getElementById('projectStatus'),
        pt = document.getElementById('projectSort');
    const grid = document.getElementById("projectGrid");
    const originalCards = [...grid.children];

    function filterProjects() {

        let q = ps.value.toLowerCase();
        let s = pf.value;
        let sort = pt.value;

        let cards = [...originalCards];

        if (sort === "az") {
            cards.sort((a, b) =>
                a.querySelector("h3").textContent.localeCompare(
                    b.querySelector("h3").textContent
                )
            );
        }

        if (sort === "za") {
            cards.sort((a, b) =>
                b.querySelector("h3").textContent.localeCompare(
                    a.querySelector("h3").textContent
                )
            );
        }

        if (sort === "old") {
            cards.reverse();
        }

        grid.innerHTML = "";

        cards.forEach(card => {

            let show = true;

            if (q && !card.dataset.text.includes(q))
                show = false;

            if (s && card.dataset.status !== s)
                show = false;

            card.style.display = show ? "flex" : "none";

            grid.appendChild(card);

        });

    }
    ps.addEventListener('input', filterProjects);
    pf.addEventListener('change', filterProjects);
    pt.addEventListener('change', filterProjects);

    document.getElementById("refreshProject").addEventListener("click", function() {

        ps.value = "";
        pf.value = "";
        pt.value = "new";

        filterProjects();

    });

    function openDeleteProjectModal(id) {

        document.getElementById("deleteProjectLink").href =
            "index.php?action=managerDeleteProject&id=" + id;

        openModal("deleteProjectModal");

    }
</script>
<?php manager_layout_script(); ?>
