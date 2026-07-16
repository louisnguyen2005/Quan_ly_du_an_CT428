<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../../controllers/managerTaskReviewController.php';
require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$pdo = $db->connect();
$managerId = (int)($_SESSION['user_id'] ?? 0);
$manager = manager_fetch_current($pdo, $managerId);
$sidebarCounts = manager_sidebar_counts($pdo, $managerId);

$controller = new ManagerTaskReviewController();
$reviewTasks = $controller->reviewQueue();

$flash = $_SESSION['manager_task_flash'] ?? null;
unset($_SESSION['manager_task_flash']);

manager_page_head('Duyệt task (mới)');
?>
<div class="manager-shell">
    <?php manager_sidebar($manager, 'task-review', $sidebarCounts['projects'], $sidebarCounts['tasks'], $sidebarCounts['notifications'], $sidebarCounts['pending_approvals'], $sidebarCounts['review_queue']); ?>
    <main class="manager-main-content">
        <?php manager_topbar('Duyệt task (mới)', $manager, $sidebarCounts['notifications']); ?>

        <section class="manager-content-area">
            <?php if ($flash): ?>
                <div class="manager-alert manager-alert-<?= mh($flash['type']) ?>" style="padding:12px 16px;border-radius:8px;margin-bottom:16px;<?= $flash['type'] === 'success' ? 'background:#dcfce7;color:#166534;' : 'background:#fee2e2;color:#991b1b;' ?>">
                    <?= mh($flash['message']) ?>
                </div>
            <?php endif; ?>

            <p style="color:#64748b;margin-bottom:16px;">
                Danh sách task ở trạng thái <strong>Review</strong> — staff đã bấm "Gửi duyệt" từ trang My Tasks
                (luồng làm việc mới: Pending → In Progress → Paused/Review → Completed).
            </p>

            <div class="manager-stat-row" style="grid-template-columns:repeat(1, minmax(220px, 320px));">
                <div class="manager-stat">
                    <div class="icon orange-bg"><i class="bi bi-clipboard2-check"></i></div>
                    <div>
                        <h2><?= count($reviewTasks) ?></h2>
                        <strong>Task chờ duyệt</strong><br>
                        <span>Đang ở trạng thái Review</span>
                    </div>
                </div>
            </div>

            <table class="manager-table">
                <thead>
                    <tr>
                        <th>Nhiệm vụ</th>
                        <th>Dự án</th>
                        <th>Staff</th>
                        <th>Progress</th>
                        <th>Gửi duyệt lúc</th>
                        <th>Deadline</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviewTasks as $task): ?>
                        <tr data-task-id="<?= (int)$task['task_id'] ?>">
                            <td><strong><?= mh($task['title']) ?></strong></td>
                            <td><?= mh($task['project_name']) ?></td>
                            <td><?= mh($task['assignee_name'] ?? 'Chưa gán') ?></td>
                            <td><?= (int)($task['progress'] ?? 0) ?>%</td>
                            <td><?= $task['submitted_at'] ? date('d/m/Y H:i', strtotime($task['submitted_at'])) : '--' ?></td>
                            <td><?= md($task['due_date']) ?></td>
                            <td>
                                <div class="task-action-row" style="display:flex;gap:8px;align-items:flex-start;flex-wrap:wrap;">
                                    <form method="post" action="index.php?action=managerApproveReview" onsubmit="return confirm('Duyệt task này là Completed?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="task_id" value="<?= (int)$task['task_id'] ?>">
                                        <button class="manager-btn primary" type="submit"><i class="bi bi-check2-circle"></i> Approve</button>
                                    </form>

                                    <details>
                                        <summary class="manager-btn danger" style="display:inline-block;cursor:pointer;"><i class="bi bi-x-circle"></i> Reject</summary>
                                        <form method="post" action="index.php?action=managerRejectReview" style="margin-top:8px;min-width:220px;" onsubmit="return confirm('Từ chối task này và yêu cầu staff làm lại?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="task_id" value="<?= (int)$task['task_id'] ?>">
                                            <textarea name="review_note" rows="3" required placeholder="Nhập lý do từ chối..." style="width:100%;margin-bottom:6px;"></textarea>
                                            <button class="manager-btn danger" type="submit">Xác nhận từ chối</button>
                                        </form>
                                    </details>

                                    <a class="manager-btn light" href="index.php?page=task-detail&id=<?= (int)$task['task_id'] ?>"><i class="bi bi-eye"></i> Xem</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$reviewTasks): ?>
                        <tr><td colspan="7" style="text-align:center;color:#64748b;padding:32px">Không có task nào đang chờ duyệt.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<?php manager_layout_script(); ?>
