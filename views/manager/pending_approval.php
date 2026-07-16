<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../../controllers/taskController.php';
require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$pdo = $db->connect();
$managerId = (int)($_SESSION['user_id'] ?? 0);
$manager = manager_fetch_current($pdo, $managerId);
$sidebarCounts = manager_sidebar_counts($pdo, $managerId);
$controller = new TaskController();
$pendingTasks = $controller->pendingApprovalByManager($managerId);

manager_page_head('Chờ duyệt');
?>
<div class="manager-shell">
    <?php manager_sidebar($manager, 'pending-approval', $sidebarCounts['projects'], $sidebarCounts['tasks'], $sidebarCounts['notifications'], $sidebarCounts['pending_approvals']); ?>
    <main class="manager-main-content">
        <?php manager_topbar('Chờ duyệt', $manager, $sidebarCounts['notifications']); ?>

        <section class="manager-content-area">
            <div class="manager-stat-row" style="grid-template-columns:repeat(1, minmax(220px, 320px));">
                <div class="manager-stat">
                    <div class="icon orange-bg"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <h2><?= count($pendingTasks) ?></h2>
                        <strong>Task chờ duyệt</strong><br>
                        <span>Staff đã submit hoàn thành</span>
                    </div>
                </div>
            </div>

            <table class="manager-table" id="pendingApprovalTable">
                <thead>
                    <tr>
                        <th>Nhiệm vụ</th>
                        <th>Dự án</th>
                        <th>Staff</th>
                        <th>Progress</th>
                        <th>Deadline</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingTasks as $task): ?>
                        <tr data-task-id="<?= (int)$task['task_id'] ?>">
                            <td>
                                <strong><?= mh($task['title']) ?></strong>
                                <small style="display:block;color:#64748b"><?= mh($task['progress_comment'] ?? '') ?></small>
                            </td>
                            <td><?= mh($task['project_name']) ?></td>
                            <td><?= mh($task['assigned_name']) ?></td>
                            <td><?= (int)($task['progress_percent'] ?? $task['progress'] ?? 0) ?>%</td>
                            <td><?= md($task['due_date']) ?></td>
                            <td>
                                <div class="task-action-row">
                                    <button class="manager-btn light" type="button" data-open-approval="<?= (int)$task['task_id'] ?>"><i class="bi bi-eye"></i> Xem</button>
                                    <button class="manager-btn primary" type="button" data-approve-task="<?= (int)$task['task_id'] ?>"><i class="bi bi-check2-circle"></i> Approve</button>
                                    <button class="manager-btn danger" type="button" data-reject-task="<?= (int)$task['task_id'] ?>"><i class="bi bi-x-circle"></i> Reject</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$pendingTasks): ?>
                        <tr><td colspan="6" style="text-align:center;color:#64748b;padding:32px">Không có task chờ duyệt.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<div class="manager-modal-backdrop" id="approvalDetailModal">
    <div class="manager-modal task-detail-modal">
        <div class="manager-modal-header">
            <h2><i class="bi bi-list-check"></i> Chi tiết duyệt task</h2>
            <button class="manager-btn light icon" data-close-modal="approvalDetailModal"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="manager-modal-body" id="approvalDetailBody"></div>
        <div class="manager-modal-footer">
            <button type="button" class="manager-btn light" data-close-modal="approvalDetailModal">Đóng</button>
            <button type="button" class="manager-btn primary" id="approvalApproveBtn"><i class="bi bi-check2-circle"></i> Approve</button>
            <button type="button" class="manager-btn danger" id="approvalRejectBtn"><i class="bi bi-x-circle"></i> Reject</button>
        </div>
    </div>
</div>

<div class="manager-modal-backdrop" id="rejectReasonModal">
    <div class="manager-modal reject-reason-modal">
        <div class="manager-modal-header">
            <h2><i class="bi bi-x-octagon"></i> Từ chối nhiệm vụ</h2>
            <button class="manager-btn light icon" data-close-modal="rejectReasonModal"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="manager-modal-body">
            <input type="hidden" id="rejectTaskId">
            <p class="reject-reason-hint"><i class="bi bi-info-circle"></i> Lý do càng rõ ràng, Staff càng dễ sửa và nộp lại nhanh hơn.</p>
            <div class="form-group full">
                <label for="rejectReason">Lý do từ chối <span class="required">*</span></label>
                <textarea id="rejectReason" rows="5" maxlength="500" placeholder="Ví dụ: Thiếu số liệu chi phí quý 3, vui lòng bổ sung file báo cáo..."></textarea>
                <div class="reject-reason-footer-row">
                    <span class="reject-reason-error" id="rejectReasonError"><i class="bi bi-exclamation-circle"></i> Vui lòng nhập lý do từ chối.</span>
                    <span class="reject-reason-count"><span id="rejectReasonCount">0</span>/500</span>
                </div>
            </div>
        </div>
        <div class="manager-modal-footer">
            <button type="button" class="manager-btn light" data-close-modal="rejectReasonModal">Hủy</button>
            <button type="button" class="manager-btn danger" id="confirmRejectBtn"><i class="bi bi-x-circle"></i> Từ chối</button>
        </div>
    </div>
</div>

<script src="assets/js/approval.js"></script>
<?php manager_layout_script(); ?>
