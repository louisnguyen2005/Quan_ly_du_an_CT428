(() => {
  const qs = (selector) => document.querySelector(selector);
  let activeTaskId = 0;

  const escapeHtml = (value) => {
    const div = document.createElement("div");
    div.textContent = value ?? "";
    return div.innerHTML;
  };

  const postForm = async (url, data) => {
    const response = await fetch(url, {
      method: "POST",
      body: data,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      credentials: "same-origin",
    });
    return response.json();
  };

  const renderTask = (task) => {
    const attachments = (task.attachments || [])
      .map(
        (file) => `
        <div class="attachment-item">
            <a href="${escapeHtml(file.file_path)}" target="_blank">
                ${escapeHtml(file.file_name)}
            </a>
        </div>
      `,
      )
      .join("");

    qs("#approvalDetailBody").innerHTML = `
        <h2 class="task-detail-title">
            ${escapeHtml(task.title || task.task_name)}
        </h2>

        <div class="task-detail-grid">

            <div class="task-info-card">
                <div class="task-info-label">
                    <i class="bi bi-folder-fill"></i>
                    Dự án
                </div>
                <div class="task-info-value">
                    ${escapeHtml(task.project_name || "--")}
                </div>
            </div>

            <div class="task-info-card">
                <div class="task-info-label">
                    <i class="bi bi-person-circle"></i>
                    Nhân viên
                </div>
                <div class="task-info-value">
                    ${escapeHtml(task.assigned_name || "--")}
                </div>
            </div>

            <div class="task-info-card">
                <div class="task-info-label">
                    <i class="bi bi-bar-chart-fill"></i>
                    Tiến độ
                </div>
                <div class="task-info-value">
                    ${Number(task.progress_percent || task.progress || 0)}%
                </div>
            </div>

            <div class="task-info-card">
                <div class="task-info-label">
                    <i class="bi bi-calendar-event"></i>
                    Deadline
                </div>
                <div class="task-info-value">
                    ${escapeHtml(task.deadline || task.due_date || "--")}
                </div>
            </div>

        </div>

        <div class="task-section">
            <h3>
                <i class="bi bi-file-text"></i>
                Mô tả nhiệm vụ
            </h3>

            <div class="approval-description-box">
                ${escapeHtml((task.description || "Không có mô tả.").trim())}
            </div>
        </div>

        <div class="task-section">
            <h3>
                <i class="bi bi-chat-left-text"></i>
                Ghi chú của nhân viên
            </h3>

            <div class="approval-comment-box">
                ${escapeHtml((task.progress_comment || "Chưa có ghi chú.").trim())}
            </div>
        </div>

        <div class="task-section">
            <h3>
                <i class="bi bi-paperclip"></i>
                File đính kèm
            </h3>

            <div class="attachment-box">
                ${attachments || "<p>Không có file đính kèm.</p>"}
            </div>
        </div>
    `;
  };

  const openApprovalDetail = async (taskId) => {
    const response = await fetch(
      `index.php?action=ajaxTaskDetail&id=${taskId}`,
      {
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        credentials: "same-origin",
      },
    );
    const data = await response.json();
    if (!data.success) {
      alert(data.message || "Không tải được task.");
      return;
    }
    activeTaskId = Number(taskId);
    renderTask(data.task);
    openModal("approvalDetailModal");
  };

  const approveTask = async (taskId) => {
    const data = new FormData();
    data.append("task_id", taskId);
    const result = await postForm("index.php?action=managerApproveTask", data);
    alert(
      result.message || (result.success ? "Đã duyệt." : "Không thể duyệt."),
    );
    if (result.success) {
      window.location.reload();
    }
  };

  const openReject = (taskId) => {
    qs("#rejectTaskId").value = taskId;
    qs("#rejectReason").value = "";
    qs("#rejectReasonCount").textContent = "0";
    qs("#rejectReason").classList.remove("error");
    qs("#rejectReasonError").classList.remove("show");
    openModal("rejectReasonModal");
    setTimeout(() => qs("#rejectReason").focus(), 50);
  };

  const rejectReasonInput = qs("#rejectReason");
  if (rejectReasonInput) {
    rejectReasonInput.addEventListener("input", () => {
      qs("#rejectReasonCount").textContent = rejectReasonInput.value.length;
      if (rejectReasonInput.value.trim()) {
        rejectReasonInput.classList.remove("error");
        qs("#rejectReasonError").classList.remove("show");
      }
    });
  }

  document.addEventListener("click", (event) => {
    const detailBtn = event.target.closest("[data-open-approval]");
    if (detailBtn) openApprovalDetail(detailBtn.dataset.openApproval);

    const approveBtn = event.target.closest("[data-approve-task]");
    if (approveBtn) approveTask(approveBtn.dataset.approveTask);

    const rejectBtn = event.target.closest("[data-reject-task]");
    if (rejectBtn) openReject(rejectBtn.dataset.rejectTask);
  });

  const modalApprove = qs("#approvalApproveBtn");
  if (modalApprove) {
    modalApprove.addEventListener("click", () => approveTask(activeTaskId));
  }

  const modalReject = qs("#approvalRejectBtn");
  if (modalReject) {
    modalReject.addEventListener("click", () => openReject(activeTaskId));
  }

  const confirmReject = qs("#confirmRejectBtn");
  if (confirmReject) {
    confirmReject.addEventListener("click", async () => {
      const taskId = qs("#rejectTaskId").value;
      const reason = qs("#rejectReason").value.trim();
      if (!reason) {
        qs("#rejectReason").classList.add("error");
        qs("#rejectReasonError").classList.add("show");
        qs("#rejectReason").focus();
        return;
      }

      const data = new FormData();
      data.append("task_id", taskId);
      data.append("rejected_reason", reason);
      const result = await postForm("index.php?action=managerRejectTask", data);
      alert(
        result.message ||
          (result.success ? "Đã từ chối." : "Không thể từ chối."),
      );
      if (result.success) {
        window.location.reload();
      }
    });
  }
})();
