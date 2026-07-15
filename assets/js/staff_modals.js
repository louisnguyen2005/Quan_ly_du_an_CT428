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

    const renderAttachments = (files) => {
        if (!files || files.length === 0) return 'Không có file đính kèm.';
        return `<ul>${files.map(file => `<li><a href="${escapeHtml(file.file_path)}" target="_blank">${escapeHtml(file.file_name)}</a></li>`).join('')}</ul>`;
    };

    const renderProject = (project) => {
        const members = (project.members || []).map(member => escapeHtml(member.username)).join(', ') || '--';
        const total = Number(project.total_tasks || 0);
        const done = Number(project.completed_tasks || 0);
        const progress = Number(project.progress || (total ? Math.round(done / total * 100) : 0));
        const tasks = (project.tasks || []).map(task => `
            <tr>
                <td><button type="button" data-project-task-id="${Number(task.task_id)}">${escapeHtml(task.title)}</button></td>
                <td>${escapeHtml(task.priority)}</td>
                <td>${escapeHtml(task.status)}</td>
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
                <div class="staff-detail-item"><span>Status</span><strong>${escapeHtml(project.status || '--')}</strong></div>
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

    const renderTask = (task) => {
        qs('#staffTaskModalTitle').textContent = task.title || task.task_name || 'Chi tiết nhiệm vụ';
        qs('#staffTaskDetailBody').innerHTML = `
            <div class="staff-detail-grid">
                <div class="staff-detail-item"><span>Task Name</span><strong>${escapeHtml(task.title || task.task_name)}</strong></div>
                <div class="staff-detail-item"><span>Project</span><strong>${escapeHtml(task.project_name)}</strong></div>
                <div class="staff-detail-item"><span>Priority</span><strong>${escapeHtml(task.priority)}</strong></div>
                <div class="staff-detail-item"><span>Status</span><strong>${escapeHtml(task.status)}</strong></div>
                <div class="staff-detail-item"><span>Deadline</span><strong>${formatDate(task.deadline || task.due_date)}</strong></div>
                <div class="staff-detail-item"><span>Assigned Date</span><strong>${formatDate(task.assigned_date || task.created_at)}</strong></div>
                <div class="staff-detail-item"><span>Manager</span><strong>${escapeHtml(task.manager_name)}</strong></div>
                <div class="staff-detail-item"><span>Progress</span><strong>${Number(task.progress_percent || task.progress || 0)}%</strong></div>
            </div>
            <div class="staff-detail-section"><span>Description</span><p>${escapeHtml(task.description || 'Không có mô tả.')}</p></div>
            <div class="staff-detail-section"><span>File đính kèm</span>${renderAttachments(task.attachments)}</div>
            <div class="staff-detail-section"><span>Comment</span><p>${escapeHtml(task.progress_comment || 'Chưa có comment.')}</p></div>
            ${task.rejected_reason ? `<div class="staff-detail-section"><span>Lý do từ chối</span><p>${escapeHtml(task.rejected_reason)}</p></div>` : ''}
        `;

        qs('#staffProgressTaskId').value = task.task_id;
        qs('#staffProgressPercent').value = Number(task.progress_percent || task.progress || 0);
        qs('#staffProgressStatus').value = ['Pending', 'In Progress', 'Completed', 'Rejected'].includes(task.status) ? task.status : 'In Progress';
        qs('#staffProgressComment').value = task.progress_comment || '';
        qs('#staffProgressFile').value = '';
        openStaffModal('staffTaskModal');
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

    const draftBtn = qs('#staffSaveDraftBtn');
    if (draftBtn) {
        draftBtn.addEventListener('click', () => submitProgress('saveTaskProgressDraft'));
    }

    const progressForm = qs('#staffTaskProgressForm');
    if (progressForm) {
        progressForm.addEventListener('submit', (event) => {
            event.preventDefault();
            submitProgress('submitTaskProgress');
        });
    }
})();
