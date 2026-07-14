<?php

require_once 'controllers/taskController.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$taskController = new TaskController();
$task = $taskController->detail($id);

if (!$task) {
    die("<div style='padding:20px;text-align:center;'>Task không tồn tại hoặc đã bị xóa.</div>");
}

$comments = $task['comments'] ?? [];
$attachments = $task['attachments'] ?? [];
$roleId = $_SESSION['role_id'] ?? 0;
$status = $task['status'] ?? 'Pending';
$priority = $task['priority'] ?? 'Medium';
$progress = (int)($task['progress'] ?? 0);

?>

<div class="task-detail-wrapper" style="padding: 24px; margin-top: 90px;">

    <a href="index.php?page=tasks" class="back-to-list-btn">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại danh sách
    </a>

    <div class="detail-header-section">
        <div class="title-and-badges">
            <h1 class="main-detail-title">
                <?= htmlspecialchars($task['task_name']) ?>
            </h1>

            <div class="detail-badge-group">
                <span class="badge-status-lg <?= strtolower(str_replace(' ', '-', $status)) ?>">
                    <?= htmlspecialchars($status) ?>
                </span>

                <span class="badge-priority-lg <?= strtolower($priority) ?>">
                    <?= htmlspecialchars($priority) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="detail-main-grid">

        <div class="detail-content-left">

            <div class="detail-card">
                <h2 class="card-section-title">Mô tả chi tiết</h2>
                <p class="task-description-content">
                    <?= nl2br(htmlspecialchars($task['description'] ?? 'Chưa có mô tả cụ thể.')) ?>
                </p>
            </div>

            <?php if ($roleId == 3): ?>
                <div class="detail-card">
                    <h2 class="card-section-title">Cập nhật trạng thái</h2>

                    <form
                        class="staff-status-form"
                        method="POST"
                        action="index.php?action=updateTaskStatus">

                        <input
                            type="hidden"
                            name="task_id"
                            value="<?= $task['task_id'] ?>">

                        <select name="status">
                            <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="In Progress" <?= $status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Review" <?= $status === 'Review' ? 'selected' : '' ?>>Review</option>
                            <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        </select>

                        <button type="submit" class="btn-primary">
                            Lưu trạng thái
                        </button>
                    </form>
                </div>

                <div class="detail-card">
                    <h2 class="card-section-title">Upload báo cáo</h2>

                    <form
                        method="POST"
                        action="index.php?action=uploadTaskReport"
                        enctype="multipart/form-data">

                        <input
                            type="hidden"
                            name="task_id"
                            value="<?= $task['task_id'] ?>">

                        <input
                            type="file"
                            name="report_file"
                            required>

                        <button type="submit" class="btn-primary" style="margin-top:12px;">
                            Gửi báo cáo
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="detail-card">
                <h2 class="card-section-title">
                    File đính kèm (<?= count($attachments) ?>)
                </h2>

                <?php if (empty($attachments)): ?>
                    <p class="task-description-content">Chưa có file đính kèm.</p>
                <?php else: ?>
                    <?php foreach ($attachments as $file): ?>
                        <div class="attachment-item">
                            <i class="fa-solid fa-paperclip"></i>
                            <a href="<?= htmlspecialchars($file['file_path']) ?>" target="_blank">
                                <?= htmlspecialchars($file['file_name']) ?>
                            </a>
                            <span>
                                bởi <?= htmlspecialchars($file['uploader_name'] ?? 'Unknown') ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="detail-card">
                <h2 class="card-section-title">
                    Bình luận (<?= count($comments) ?>)
                </h2>

                <form
                    class="comment-write-block"
                    method="POST"
                    action="index.php?action=addTaskComment">

                    <div class="avatar">
                        <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                    </div>

                    <div class="textarea-container">
                        <input
                            type="hidden"
                            name="task_id"
                            value="<?= $task['task_id'] ?>">

                        <textarea
                            name="content"
                            placeholder="Viết bình luận của bạn tại đây..."
                            required></textarea>

                        <button class="btn-submit-comment" type="submit">
                            <i class="fa-solid fa-paper-plane"></i>
                            Gửi
                        </button>
                    </div>
                </form>

                <div class="comments-history-list">
                    <?php if (empty($comments)): ?>
                        <p class="task-description-content">Chưa có bình luận nào.</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-row-item">
                                <div class="avatar">
                                    <?= strtoupper(substr($comment['author'], 0, 1)) ?>
                                </div>

                                <div class="comment-body-text">
                                    <div class="comment-info-header">
                                        <span class="comment-user-name">
                                            <?= htmlspecialchars($comment['author']) ?>
                                        </span>

                                        <span class="comment-timestamp">
                                            <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                                        </span>
                                    </div>

                                    <p class="comment-message">
                                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="detail-content-right">
            <div class="detail-card">
                <h2 class="card-section-title">Thông tin nhiệm vụ</h2>

                <div class="meta-info-row">
                    <i class="fa-regular fa-user meta-icon"></i>
                    <div class="meta-info-body">
                        <span class="meta-item-label">Người thực hiện</span>
                        <div class="meta-profile-item">
                            <div class="avatar">
                                <?= strtoupper(substr($task['assigned_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div>
                                <div class="meta-profile-name">
                                    <?= htmlspecialchars($task['assigned_name'] ?? 'Unassigned') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="meta-info-row">
                    <i class="fa-regular fa-user meta-icon"></i>
                    <div class="meta-info-body">
                        <span class="meta-item-label">Người tạo</span>
                        <span class="meta-item-value text-dark-bold">
                            <?= htmlspecialchars($task['manager_name'] ?? 'System') ?>
                        </span>
                    </div>
                </div>

                <div class="meta-info-row">
                    <i class="fa-regular fa-calendar meta-icon"></i>
                    <div class="meta-info-body">
                        <span class="meta-item-label">Deadline</span>
                        <span class="meta-item-value text-dark-bold">
                            <?= !empty($task['due_date'])
                                ? date('d/m/Y', strtotime($task['due_date']))
                                : 'Chưa có' ?>
                        </span>
                    </div>
                </div>

                <div class="meta-info-row">
                    <i class="fa-regular fa-folder meta-icon"></i>
                    <div class="meta-info-body">
                        <span class="meta-item-label">Dự án</span>
                        <span class="meta-item-value text-dark-bold">
                            <?= htmlspecialchars($task['project_name'] ?? 'Không rõ') ?>
                        </span>
                    </div>
                </div>

                <div class="meta-info-row">
                    <i class="fa-solid fa-chart-line meta-icon"></i>
                    <div class="meta-info-body">
                        <span class="meta-item-label">Tiến độ</span>
                        <span class="meta-item-value text-dark-bold">
                            <?= $progress ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
