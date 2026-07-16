(() => {
    const qs = (selector) => document.querySelector(selector);

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    };

    const openStaffModal = (id) => {
        const modal = qs(`#${id}`);
        if (modal) {
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        }
    };

    const closeStaffModal = (id) => {
        const modal = qs(`#${id}`);
        if (modal) {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }
    };

    const formatDate = (date) => date ? new Date(date).toLocaleDateString('vi-VN') : '--';

    const getJson = async (url) => {
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        });
        return response.json();
    };

    const renderProject = (project) => {
        const members = (project.members || []).map(member => escapeHtml(member.username)).join(', ') || '--';
        const total = Number(project.total_tasks || 0);
        const done = Number(project.completed_tasks || 0);
        const progress = Number(project.progress || (total ? Math.round(done / total * 100) : 0));
        const tasks = (project.tasks || []).map(task => `
            <tr>
                <td><button type="button" data-project-task-id="${Number(task.task_id)}">${escapeHtml(task.title)}</button></td>
                <td><span class="badge-pill ${task.display_priority_class || 'medium'}">${escapeHtml(task.display_priority || task.priority)}</span></td>
                <td><span class="badge-pill ${task.display_status_class || 'muted'}">${escapeHtml(task.display_status || task.status)}</span></td>
                <td>${formatDate(task.due_date)}</td>
            </tr>
        `).join('');

        qs('#staffProjectModalTitle').textContent = project.name || project.project_name || 'Chi tiết dự án';
        qs('#staffProjectModalBody').innerHTML = `
            <div class="staff-detail-grid">
                <div class="staff-detail-item"><span>Project Name</span><strong>${escapeHtml(project.name || project.project_name)}</strong></div>
                <div class="staff-detail-item"><span>Manager</span><strong>${escapeHtml(project.manager_name)}</strong></div>
                <div class="staff-detail-item"><span>Start Date</span><strong>${formatDate(project.start_date)}</strong></div>
                <div class="staff-detail-item"><span>Deadline</span><strong>${formatDate(project.end_date)}</strong></div>
                <div class="staff-detail-item"><span>Progress</span><strong>${progress}%</strong></div>
                <div class="staff-detail-item"><span>Status</span><strong><span class="badge-pill ${project.display_status_class || 'muted'}">${escapeHtml(project.display_status || project.status || '--')}</span></strong></div>
            </div>
            <div class="staff-detail-section"><span>Description</span><p>${escapeHtml(project.description || 'Không có mô tả.')}</p></div>
            <div class="staff-detail-section"><span>Members</span><p>${members}</p></div>
            <div class="staff-detail-section">
                <span>Tasks</span>
                <table class="staff-project-task-table">
                    <thead><tr><th>Task Name</th><th>Priority</th><th>Status</th><th>Deadline</th></tr></thead>
                    <tbody>${tasks || '<tr><td colspan="4">Không có task.</td></tr>'}</tbody>
                </table>
            </div>
        `;
        openStaffModal('staffProjectModal');
    };

    // Modal chi tiết task bên staff dùng LẠI đúng bộ class HTML của modal chi tiết task bên
    // Manager (task-detail-title/task-detail-grid/task-info-card/task-section/task-detail-box/
    // comment-*, xem assets/css/staff_modals.css) để 2 bên nhìn giống hệt nhau. Staff có thêm
    // 2 phần Manager không có: cảnh báo "Lý do từ chối" và form "Nộp báo cáo tiến độ" (đã có sẵn,
    // nằm tĩnh trong HTML ngay dưới #staffTaskDetailBody).
    const renderTask = (task) => {
        qs('#staffTaskModalTitle').textContent = 'Chi tiết nhiệm vụ';

        const statusClass = task.display_status_class || 'muted';
        const statusText = task.display_status || task.status;
        const priorityClass = task.display_priority_class || 'muted';
        const priorityText = task.display_priority || task.priority;

        const attachmentsHtml = (task.attachments && task.attachments.length)
            ? `<ul class="staff-attachment-list">${task.attachments.map(file => `
                <li><a href="${escapeHtml(file.file_path)}" target="_blank"><i class="bi bi-paperclip"></i> ${escapeHtml(file.file_name)}</a></li>
            `).join('')}</ul>`
            : 'Không có file đính kèm.';

        const comments = task.comments || [];
        const commentsHtml = comments.length ? comments.map(c => {
            const own = Number(c.user_id) === Number(window.CURRENT_STAFF_ID || 0);
            const initial = (c.author || '?').charAt(0).toUpperCase();
            const avatar = `<div class="comment-avatar">${escapeHtml(initial)}</div>`;
            return `
                <div class="comment-item ${own ? 'own' : ''}">
                    ${!own ? avatar : ''}
                    <div class="comment-content">
                        <div class="comment-meta"><strong>${escapeHtml(c.author || '')}</strong><span>${formatDate(c.created_at)}</span></div>
                        <div class="comment-bubble">${escapeHtml(c.content)}</div>
                    </div>
                    ${own ? avatar : ''}
                </div>
            `;
        }).join('') : `<div class="comment-empty"><i class="bi bi-chat-square-text"></i><p>Chưa có bình luận nào.</p></div>`;

        const rejectedHtml = task.rejected_reason ? `
            <div class="staff-alert-danger">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <div>
                    <strong>Lý do từ chối</strong>
                    <p>${escapeHtml(task.rejected_reason)}</p>
                </div>
            </div>
        ` : '';

        qs('#staffTaskDetailBody').innerHTML = `
            <h2 class="task-detail-title">${escapeHtml(task.title || task.task_name)}</h2>

            <div class="task-detail-grid">
                <div class="task-info-card">
                    <div class="task-info-label"><i class="bi bi-folder2"></i> Dự án</div>
                    <div class="task-info-value">${escapeHtml(task.project_name)}</div>
                </div>
                <div class="task-info-card">
                    <div class="task-info-label"><i class="bi bi-calendar-event"></i> Hạn hoàn thành</div>
                    <div class="task-info-value">${formatDate(task.deadline || task.due_date)}</div>
                </div>
                <div class="task-info-card">
                    <div class="task-info-label"><i class="bi bi-flag"></i> Trạng thái</div>
                    <div><span class="badge-pill ${statusClass}">${escapeHtml(statusText)}</span></div>
                </div>
                <div class="task-info-card">
                    <div class="task-info-label"><i class="bi bi-lightning-charge"></i> Mức ưu tiên</div>
                    <div><span class="badge-pill ${priorityClass}">${escapeHtml(priorityText)}</span></div>
                </div>
            </div>

            <div class="task-section">
                <h3><i class="bi bi-file-text"></i> Mô tả nhiệm vụ</h3>
                <div class="task-detail-box">${escapeHtml(task.description || 'Không có mô tả.')}</div>
            </div>

            <div class="task-section">
                <h3><i class="bi bi-paperclip"></i> File đính kèm</h3>
                <div class="task-detail-box">${attachmentsHtml}</div>
            </div>

            <div class="task-section">
                <div class="comment-header">
                    <div class="comment-title"><i class="bi bi-chat-dots"></i> Trao đổi <span class="comment-count">${comments.length}</span></div>
                </div>
                <div class="comment-list">${commentsHtml}</div>
                <form id="staffCommentForm" data-task-id="${Number(task.task_id)}">
                    <div class="comment-input">
                        <textarea id="staffCommentContent" rows="4" placeholder="Nhập nội dung trao đổi với quản lý..."></textarea>
                    </div>
                    <div class="comment-action">
                        <button type="submit" class="staff-primary-btn"><i class="bi bi-send-fill"></i> Gửi bình luận</button>
                    </div>
                </form>
            </div>

            ${rejectedHtml}
        `;

        qs('#staffProgressTaskId').value = task.task_id;
        qs('#staffProgressComment').value = task.progress_comment || '';
        qs('#staffProgressFile').value = '';
        openStaffModal('staffTaskModal');
    };

    const submitStaffComment = async (form) => {
        const taskId = form.dataset.taskId;
        const textarea = qs('#staffCommentContent');
        const content = textarea ? textarea.value.trim() : '';
        if (!content) return;

        const data = new FormData();
        data.append('task_id', taskId);
        data.append('content', content);

        const response = await fetch('index.php?action=addTaskComment', {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        const result = await response.json();
        if (result.success) {
            loadTask(taskId);
        } else {
            alert(result.message || 'Không gửi được bình luận.');
        }
    };

    const loadProject = async (projectId) => {
        const data = await getJson(`index.php?action=ajaxProjectDetail&id=${projectId}`);
        if (!data.success) {
            alert(data.message || 'Không tải được dự án.');
            return;
        }
        renderProject(data.project);
    };

    const loadTask = async (taskId) => {
        const data = await getJson(`index.php?action=ajaxTaskDetail&id=${taskId}`);
        if (!data.success) {
            alert(data.message || 'Không tải được nhiệm vụ.');
            return;
        }
        renderTask(data.task);
    };

    const submitProgress = async (action) => {
        const form = qs('#staffTaskProgressForm');
        const formData = new FormData(form);
        const response = await fetch(`index.php?action=${action}`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        });
        const result = await response.json();
        alert(result.message || (result.success ? 'Đã lưu.' : 'Không thể lưu.'));
        if (result.success) {
            closeStaffModal('staffTaskModal');
            window.location.reload();
        }
    };

    document.addEventListener('submit', (event) => {
        if (event.target && event.target.id === 'staffCommentForm') {
            event.preventDefault();
            submitStaffComment(event.target);
        }
    });

    document.addEventListener('click', (event) => {
        const closeBtn = event.target.closest('[data-staff-close]');
        if (closeBtn) closeStaffModal(closeBtn.dataset.staffClose);

        const taskBtn = event.target.closest('.js-staff-task-link,[data-project-task-id]');
        if (taskBtn) {
            event.preventDefault();
            loadTask(taskBtn.dataset.taskId || taskBtn.dataset.projectTaskId);
            return;
        }

        const projectCard = event.target.closest('.project-card[data-project-id]');
        if (projectCard) {
            loadProject(projectCard.dataset.projectId);
        }
    });

    document.querySelectorAll('.staff-modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeStaffModal(modal.id);
        });
    });

    // Chỉ còn 1 hành động submit ("Nộp báo cáo") - không còn nút Lưu nháp riêng nữa.
    const progressForm = qs('#staffTaskProgressForm');
    if (progressForm) {
        progressForm.addEventListener('submit', (event) => {
            event.preventDefault();
            submitProgress('submitTaskProgress');
        });
    }

    // Cho phép nhảy thẳng vào đúng nhiệm vụ khi đến từ link thông báo
    // (index.php?page=staff-tasks&task_id=X): tự mở modal chi tiết nhiệm vụ đó ngay khi tải trang.
    const autoOpenTaskId = new URLSearchParams(window.location.search).get('task_id');
    if (autoOpenTaskId) {
        loadTask(autoOpenTaskId);
    }
})();
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         