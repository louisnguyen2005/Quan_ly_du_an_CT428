<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../../config/database.php';
$db = new Database();
$pdo = $db->connect();
$managerId = (int)($_SESSION['user_id'] ?? 0);
$manager = manager_fetch_current($pdo, $managerId);
$owned = manager_owned_project_condition('p', 'mid');
$sidebarCounts = manager_sidebar_counts($pdo, $managerId);
$sidebarProjectCount = $sidebarCounts['projects'];
$sidebarTaskCount = $sidebarCounts['tasks'];
$sidebarNotifCount = $sidebarCounts['notifications'];
$sidebarPendingApprovalCount = $sidebarCounts['pending_approvals'];
$projectsStmt = $pdo->prepare("SELECT p.* FROM projects p WHERE p.is_deleted=0 AND $owned ORDER BY p.created_at DESC");
$projectsStmt->execute(manager_owned_project_params($managerId));
$projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare("SELECT t.*,p.name project_name,p.start_date,p.end_date,COALESCE(u.username,'-') assignee_name FROM tasks t JOIN projects p ON p.project_id=t.project_id LEFT JOIN users u ON u.user_id=t.assignee_id WHERE t.is_deleted=0 AND p.is_deleted=0 AND $owned ORDER BY p.project_id,t.due_date ASC,t.task_id DESC");
$stmt->execute(manager_owned_project_params($managerId));
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
$tasksByProject = [];
foreach ($tasks as $t) {
    $tasksByProject[$t['project_id']][] = $t;
}
$staffs = $pdo->query("SELECT u.user_id,u.username FROM users u JOIN roles r ON r.role_id=u.role_id WHERE r.role_code='STAFF' AND u.is_deleted=0 ORDER BY u.username")->fetchAll(PDO::FETCH_ASSOC);
$total = count($tasks);
$pending = count(array_filter($tasks, fn($t) => $t['status'] === 'Pending'));
$doing = count(array_filter($tasks, fn($t) => $t['status'] === 'In Progress'));
$completed = count(array_filter($tasks, fn($t) => $t['status'] === 'Completed'));
$overdue = count(array_filter($tasks, fn($t) => !empty($t['due_date']) && $t['due_date'] < date('Y-m-d') && $t['status'] !== 'Completed'));
manager_page_head('Nhiệm vụ'); ?>
<div class="manager-shell">
    <?php manager_sidebar($manager, 'tasks', $sidebarProjectCount, $sidebarTaskCount, $sidebarNotifCount, $sidebarPendingApprovalCount); ?><main
        class="manager-main-content"><?php manager_topbar('Nhiệm vụ', $manager, $sidebarNotifCount); ?>
        <section class="manager-content-area">
            <div class="manager-stat-row task-stat-row">
                <div class="task-stat-card blue">
                    <div class="task-stat-header">
                        <div class="task-stat-icon blue">
                            <i class="bi bi-card-checklist"></i>
                        </div>
                        <div class="task-stat-number"><?= $total ?></div>
                    </div>
                    <div class="task-stat-body">
                        <div class="task-stat-title">Tổng nhiệm vụ</div>
                        <div class="task-stat-sub">Tất cả nhiệm vụ</div>
                    </div>
                </div>
                <div class="task-stat-card orange">
                    <div class="task-stat-header">
                        <div class="task-stat-icon orange">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="task-stat-number"><?= $pending ?></div>
                    </div>
                    <div class="task-stat-body">
                        <div class="task-stat-title">Chưa bắt đầu</div>
                        <div class="task-stat-sub">Đang chờ thực hiện</div>
                    </div>
                </div>
                <div class="task-stat-card cyan">
                    <div class="task-stat-header">
                        <div class="task-stat-icon cyan">
                            <i class="bi bi-play-fill"></i>
                        </div>
                        <div class="task-stat-number"><?= $doing ?></div>
                    </div>
                    <div class="task-stat-body">
                        <div class="task-stat-title">Đang thực hiện</div>
                        <div class="task-stat-sub">Công việc đang xử lý</div>
                    </div>
                </div>
                <div class="task-stat-card green">
                    <div class="task-stat-header">
                        <div class="task-stat-icon green">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="task-stat-number"><?= $completed ?></div>
                    </div>
                    <div class="task-stat-body">
                        <div class="task-stat-title">Hoàn thành</div>
                        <div class="task-stat-sub">Nhiệm vụ đã hoàn tất</div>
                    </div>
                </div>
                <div class="task-stat-card red">
                    <div class="task-stat-header">
                        <div class="task-stat-icon red">
                            <i class="bi bi-exclamation-circle-fill"></i>
                        </div>
                        <div class="task-stat-number"><?= $overdue ?></div>
                    </div>
                    <div class="task-stat-body">
                        <div class="task-stat-title">Quá hạn</div>
                        <div class="task-stat-sub">Cần xử lý ngay</div>
                    </div>
                </div>
            </div>
            <div class="manager-filter-row">

                <select id="projectFilter">
                    <option value="">Tất cả dự án</option>
                    <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['project_id'] ?>">
                        <?= mh($p['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <select id="staffFilter">
                    <option value="">Tất cả nhân viên</option>
                    <?php foreach ($staffs as $s): ?>
                    <option value="<?= $s['user_id'] ?>">
                        <?= mh($s['username']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <select id="statusFilter">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Pending">Mới giao (chưa làm)</option>
                    <option value="In Progress">Đang thực hiện</option>
                    <option value="Pending Approval">Chờ duyệt</option>
                    <option value="Completed">Hoàn thành</option>
                </select>

                <select id="priorityFilter">
                    <option value="">Tất cả ưu tiên</option>
                    <option value="Low">Thấp</option>
                    <option value="Medium">Trung bình</option>
                    <option value="High">Cao</option>
                </select>

                <button class="manager-btn light" onclick="resetTaskFilter()">
                    <i class="bi bi-arrow-clockwise"></i>
                    Làm mới
                </button>

            </div>
            <?php foreach ($projects as $p):
                $ptasks = $tasksByProject[$p['project_id']] ?? [];
            ?>
            <div class="task-project-block" data-project="<?= $p['project_id'] ?>">
                <div class="task-project-head">
                    <div class="task-project-title"><span class="toggle-tasks"
                            onclick="this.closest('.task-project-block').classList.toggle('open')"><i
                                class="bi bi-chevron-down"></i></span><i class="bi bi-folder-fill"
                            style="font-size:30px;color:#2563eb"></i>
                        <div>
                            <h3><?= mh($p['name']) ?></h3><span><?= count($ptasks) ?> nhiệm vụ •
                                <?= md($p['start_date']) ?> - <?= md($p['end_date']) ?></span>
                        </div>
                    </div><button class="manager-btn primary" onclick="prepareAddTask(<?= (int)$p['project_id'] ?>)"><i
                            class="bi bi-plus-circle"></i>Thêm nhiệm vụ</button>
                </div>
                <div class="task-items">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Nhiệm vụ</th>
                                <th>Người thực hiện</th>
                                <th>Trạng thái</th>
                                <th>Ưu tiên</th>
                                <th>Deadline</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($ptasks as $t): ?><tr data-staff="<?= $t['assignee_id'] ?>"
                                data-status="<?= $t['status'] ?>" data-priority="<?= $t['priority'] ?>">
                                <td><strong><?= mh($t['title']) ?></strong></td>
                                <td><?= mh($t['assignee_name']) ?></td>
                                <td><span
                                        class="badge-pill <?= mtaskclass($t['status']) ?>"><?= mstatus($t['status']) ?></span>
                                </td>
                                <td><span
                                        class="badge-pill <?= mclass($t['priority']) ?>"><?= mprio($t['priority']) ?></span>
                                </td>
                                <td><?= md($t['due_date']) ?></td>
                                <td>
                                    <div class="task-action-row"><button class="manager-btn light icon"
                                            data-view-task-id="<?= (int)$t['task_id'] ?>"
                                            onclick='showTaskDetail(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i
                                                class="bi bi-eye"></i></button><button class="manager-btn light icon"
                                            onclick='fillTaskEdit(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i
                                                class="bi bi-pencil-square"></i></button><button type="button"
                                            class="manager-btn danger icon"
                                            onclick="openDeleteTaskModal(<?= $t['task_id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button></div>
                                </td>
                            </tr><?php endforeach; ?></tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </section>
    </main>
</div>
<div class="manager-modal-backdrop" id="addTaskModal">
    <div class="manager-modal">
        <div class="manager-modal-header">
            <h2>Thêm nhiệm vụ</h2><button class="manager-btn light icon" data-close-modal="addTaskModal"><i
                    class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="index.php?action=managerCreateTask" enctype="multipart/form-data"><input
                type="hidden" name="project_id" id="add_task_project_id">
            <div class="manager-modal-body">
                <div class="form-grid">
                    <div class="form-group full"><label>Tên nhiệm vụ</label><input name="title" required></div>
                    <div class="form-group full"><label>Mô tả</label><textarea name="description" rows="4"></textarea>
                    </div>
                    <div class="form-group"><label>Người thực hiện</label><select name="assignee_id">
                            <option value="">-- Chọn --</option><?php foreach ($staffs as $s): ?><option
                                value="<?= $s['user_id'] ?>"><?= mh($s['username']) ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="form-group"><label>Ưu tiên</label><select name="priority">
                            <option>Low</option>
                            <option selected>Medium</option>
                            <option>High</option>
                            <option>Critical</option>
                        </select></div>
                    <div class="form-group">
                        <label>Tài liệu đính kèm</label>
                        <input type="file" name="attachment"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.png,.jpg,.jpeg">
                    </div>
                    <div class="form-group"><label>Deadline</label><input type="date" name="due_date"></div>
                </div>
            </div>
            <div class="manager-modal-footer"><button type="button" class="manager-btn light"
                    data-close-modal="addTaskModal">Hủy</button><button class="manager-btn primary">Lưu</button></div>
        </form>
    </div>
</div>
<div class="manager-modal-backdrop" id="editTaskModal">
    <div class="manager-modal">
        <div class="manager-modal-header">
            <h2>Chỉnh sửa nhiệm vụ</h2><button class="manager-btn light icon" data-close-modal="editTaskModal"><i
                    class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="index.php?action=managerUpdateTask" enctype="multipart/form-data"><input
                type="hidden" name="task_id" id="edit_task_id"><input type="hidden" name="project_id"
                id="edit_task_project_id">
            <div class="manager-modal-body">
                <div class="form-grid">
                    <div class="form-group full"><label>Tên nhiệm vụ</label><input name="title" id="edit_task_title"
                            required></div>
                    <div class="form-group full"><label>Mô tả</label><textarea name="description"
                            id="edit_task_description" rows="4"></textarea></div>
                    <div class="form-group"><label>Người thực hiện</label><select name="assignee_id"
                            id="edit_task_assignee">
                            <option value="">-- Chọn --</option><?php foreach ($staffs as $s): ?><option
                                value="<?= $s['user_id'] ?>"><?= mh($s['username']) ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="form-group"><label>Ưu tiên</label><select name="priority" id="edit_task_priority">
                            <option>Low</option>
                            <option>Medium</option>
                            <option>High</option>
                            <option>Critical</option>
                        </select></div>
                    <div class="form-group">
                        <label>Thêm tài liệu</label>
                        <input type="file" name="attachment"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.png,.jpg,.jpeg">
                    </div>
                    <div class="form-group"><label>Deadline</label><input type="date" name="due_date"
                            id="edit_task_due"></div>
                </div>
            </div>
            <div class="manager-modal-footer"><button type="button" class="manager-btn light"
                    data-close-modal="editTaskModal">Hủy</button><button class="manager-btn primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>
<div class="manager-modal-backdrop" id="taskDetailModal">

    <div class="manager-modal task-detail-modal">

        <div class="manager-modal-header">

            <h2>
                <i class="bi bi-list-task"></i>
                Chi tiết nhiệm vụ
            </h2>

            <button class="manager-btn light icon" data-close-modal="taskDetailModal">
                <i class="bi bi-x-lg"></i>
            </button>

        </div>

        <div class="manager-modal-body">

            <h2 id="detail_title" class="task-detail-title"></h2>

            <div class="task-detail-grid">

                <div class="task-info-card">

                    <div class="task-info-label">

                        <i class="bi bi-person-circle"></i>

                        Người thực hiện

                    </div>

                    <div id="detail_assignee" class="task-info-value"></div>

                </div>

                <div class="task-info-card">

                    <div class="task-info-label">

                        <i class="bi bi-calendar-event"></i>

                        Hạn hoàn thành

                    </div>

                    <div id="detail_due" class="task-info-value"></div>

                </div>

                <div class="task-info-card">

                    <div class="task-info-label">

                        <i class="bi bi-flag"></i>

                        Trạng thái

                    </div>

                    <div>

                        <span id="detail_status" class="badge-pill"></span>

                    </div>

                </div>

                <div class="task-info-card">

                    <div class="task-info-label">

                        <i class="bi bi-lightning-charge"></i>

                        Mức ưu tiên

                    </div>

                    <div>

                        <span id="detail_priority" class="badge-pill"></span>

                    </div>

                </div>

            </div>

            <div class="task-section">

                <h3>

                    <i class="bi bi-file-text"></i>

                    Mô tả nhiệm vụ

                </h3>

                <div id="detail_description" class="task-detail-box"></div>

            </div>

            <div class="task-section">

                <div class="comment-header">

                    <div class="comment-title">

                        <i class="bi bi-chat-dots"></i>

                        Trao đổi

                        <span class="comment-count">0</span>

                    </div>

                </div>

                <div class="comment-list">

                    <div class="comment-empty">

                        <i class="bi bi-chat-square-text"></i>

                        <p>Chưa có bình luận nào.</p>

                    </div>

                </div>

                <form id="commentForm">

                    <input type="hidden" name="task_id" id="comment_task_id">

                    <div class="comment-input">

                        <textarea id="commentContent" name="content" rows="4"
                            placeholder="Nhập nội dung trao đổi với thành viên..."></textarea>

                    </div>

                    <div class="comment-action">

                        <button class="manager-btn primary">

                            <i class="bi bi-send-fill"></i>

                            Gửi bình luận

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>
<div class="manager-modal-backdrop" id="deleteTaskModal">

    <div class="manager-modal delete-modal">

        <div class="manager-modal-header">

            <h2>Xóa nhiệm vụ</h2>

        </div>


        <div class="manager-modal-body">

            <div class="delete-icon">

                <i class="bi bi-trash"></i>

            </div>


            <h3>Bạn có chắc muốn xóa nhiệm vụ này?</h3>


            <p>
                Nhiệm vụ và các bình luận liên quan sẽ bị xóa khỏi hệ thống.
                Hành động này không thể hoàn tác.
            </p>

        </div>


        <div class="manager-modal-footer">


            <button type="button" class="manager-btn light" data-close-modal="deleteTaskModal">

                Hủy

            </button>


            <a id="deleteTaskLink" class="manager-btn danger">

                Xóa nhiệm vụ

            </a>


        </div>

    </div>

</div>
<script>
function prepareAddTask(pid) {
    document.getElementById('add_task_project_id').value = pid;
    openModal('addTaskModal')
}

document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const taskId = params.get('task_id');
    if (!taskId) return;

    const button = document.querySelector('[data-view-task-id="' + CSS.escape(taskId) + '"]');
    if (button) {
        const block = button.closest('.task-project-block');
        if (block) block.classList.add('open');
        button.click();

        if (params.get('focus') === 'comments') {
            setTimeout(function () {
                const comments = document.querySelector('#taskDetailModal .comment-header');
                if (comments) comments.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 350);
        }
    }
});

function fillTaskEdit(t) {
    document.getElementById('edit_task_id').value = t.task_id;
    document.getElementById('edit_task_project_id').value = t.project_id;
    document.getElementById('edit_task_title').value = t.title || '';
    document.getElementById('edit_task_description').value = t.description || '';
    document.getElementById('edit_task_assignee').value = t.assignee_id || '';
    document.getElementById('edit_task_priority').value = t.priority || 'Medium';
    document.getElementById('edit_task_due').value = t.due_date || '';
    openModal('editTaskModal')
}

function formatDate(date) {

    const d = new Date(date);

    return d.toLocaleString("vi-VN");

}

function escapeHtml(text) {

    const div = document.createElement("div");

    div.textContent = text;

    return div.innerHTML;

}

function loadComments(taskId) {

    fetch("index.php?action=managerGetTaskComments&task_id=" + taskId)
        .then(res => res.json())
        .then(comments => {

            const list = document.querySelector(".comment-list");
            const count = document.querySelector(".comment-count");

            count.textContent = comments.length;

            if (comments.length === 0) {

                list.innerHTML = `
                <div class="comment-empty">
                    <i class="bi bi-chat-square-text"></i>
                    <p>Chưa có bình luận nào.</p>
                </div>
            `;

                return;
            }

            let html = "";

            comments.forEach(c => {

                const own = c.user_id == <?= $managerId ?>;

                html += `
        <div class="comment-item ${own ? 'own' : ''}">

            ${!own ? `
                <div class="comment-avatar">
                    ${c.username.charAt(0).toUpperCase()}
                </div>
            ` : ''}

            <div class="comment-content">

                <div class="comment-meta">

                    <strong>${c.username}</strong>

                    <span>${formatDate(c.created_at)}</span>

                </div>

                <div class="comment-bubble">

                    ${escapeHtml(c.content)}

                </div>

            </div>

            ${own ? `
                <div class="comment-avatar">
                    ${c.username.charAt(0).toUpperCase()}
                </div>
            ` : ''}

        </div>
    `;

            });

            list.innerHTML = html;

        });

}

document.getElementById("commentForm").addEventListener("submit", function(e) {

    e.preventDefault();

    const taskId = document.getElementById("comment_task_id").value;
    const content = document.getElementById("commentContent").value.trim();

    if (content === "") {
        alert("Vui lòng nhập nội dung bình luận.");
        return;
    }

    fetch("index.php?action=managerAddTaskCommentAjax", {

            method: "POST",

            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },

            body: new URLSearchParams({
                task_id: taskId,
                content: content
            })

        })

        .then(res => res.json())

        .then(data => {

            if (!data.success) {
                alert(data.message);
                return;
            }

            document.getElementById("commentContent").value = "";

            loadComments(taskId);

        })

        .catch(() => {

            alert("Có lỗi xảy ra.");

        });

});

function showTaskDetail(t) {

    const statusMap = {
        "Pending": {
            text: "Đang thực hiện",
            cls: "in-progress"
        },
        "In Progress": {
            text: "Đang thực hiện",
            cls: "in-progress"
        },
        "Pending Approval": {
            text: "Chờ duyệt",
            cls: "pending-approval"
        },
        "Completed": {
            text: "Hoàn thành",
            cls: "completed"
        }
    };

    const priorityMap = {
        "Low": {
            text: "Thấp",
            cls: "low"
        },
        "Medium": {
            text: "Trung bình",
            cls: "medium"
        },
        "High": {
            text: "Cao",
            cls: "high"
        },
        "Critical": {
            text: "Khẩn cấp",
            cls: "critical"
        }
    };

    document.getElementById("detail_title").textContent =
        t.title || "Không có tiêu đề";

    document.getElementById("detail_assignee").textContent =
        t.assignee_name || "Chưa phân công";

    document.getElementById("detail_due").textContent =
        t.due_date || "Chưa có";

    const st = statusMap[t.status] || {
        text: t.status,
        cls: ""
    };

    const pr = priorityMap[t.priority] || {
        text: t.priority,
        cls: ""
    };

    const status = document.getElementById("detail_status");
    status.className = "badge-pill " + st.cls;
    status.textContent = st.text;

    const priority = document.getElementById("detail_priority");
    priority.className = "badge-pill " + pr.cls;
    priority.textContent = pr.text;

    document.getElementById("detail_description").textContent =
        t.description || "Chưa có mô tả.";

    document.getElementById("comment_task_id").value = t.task_id;
    loadComments(t.task_id);

    openModal("taskDetailModal");
}

const projectFilter = document.getElementById("projectFilter");
const staffFilter = document.getElementById("staffFilter");
const statusFilter = document.getElementById("statusFilter");
const priorityFilter = document.getElementById("priorityFilter");

projectFilter.addEventListener("change", filterTasks);
staffFilter.addEventListener("change", filterTasks);
statusFilter.addEventListener("change", filterTasks);
priorityFilter.addEventListener("change", filterTasks);

function filterTasks() {

    document.querySelectorAll(".task-project-block").forEach(project => {

        if (projectFilter.value &&
            project.dataset.project !== projectFilter.value) {

            project.style.display = "none";
            return;
        }

        let visible = 0;

        project.querySelectorAll("tbody tr").forEach(row => {

            let show = true;

            if (staffFilter.value &&
                row.dataset.staff != staffFilter.value)
                show = false;

            if (statusFilter.value &&
                row.dataset.status != statusFilter.value)
                show = false;

            if (priorityFilter.value &&
                row.dataset.priority != priorityFilter.value)
                show = false;

            row.style.display = show ? "table-row" : "none";

            if (show) visible++;

        });

        project.style.display = visible ? "block" : "none";

    });

}

function resetTaskFilter() {
    document.getElementById("projectFilter").value = "";
    document.getElementById("staffFilter").value = "";
    document.getElementById("statusFilter").value = "";
    document.getElementById("priorityFilter").value = "";
    filterTasks();
}

function openDeleteTaskModal(id) {

    document.getElementById("deleteTaskLink").href =
        "index.php?action=managerDeleteTask&id=" + id;

    openModal("deleteTaskModal");

}

// Cho phép nhảy thẳng vào đúng nhiệm vụ khi đến từ link thông báo (index.php?page=tasks&task_id=X):
// bấm hộ nút "xem chi tiết" của đúng dòng task đó, tận dụng luôn dữ liệu đã render sẵn trong bảng.
(function () {
    const params = new URLSearchParams(window.location.search);
    const taskId = params.get("task_id");
    if (!taskId) return;
    const btn = document.querySelector('[data-view-task-id="' + taskId + '"]');
    if (btn) btn.click();
})();
</script>
<?php manager_layout_script(); ?>
