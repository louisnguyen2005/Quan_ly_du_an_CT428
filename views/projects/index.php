<?php

require_once 'controllers/projectController.php';

$projectController = new ProjectController();

$projects = $projectController->index();

$roleId = $_SESSION['role_id'] ?? 0;

?>

<div class="page-container">

    <div class="projects-header">
        <div>
            <h1>Quản lý dự án</h1>

            <?php if ($roleId == 1): ?>
                <p>Tất cả dự án trong hệ thống</p>
            <?php elseif ($roleId == 2): ?>
                <p>Danh sách dự án bạn đang quản lý</p>
            <?php else: ?>
                <p>Danh sách dự án bạn đang tham gia</p>
            <?php endif; ?>
        </div>

        <?php if ($roleId == 2): ?>
            <button id="openProjectModal" class="btn-primary">
                <i class="fa-solid fa-plus"></i>
                Tạo dự án mới
            </button>
        <?php endif; ?>
    </div>

    <div class="project-toolbar">
        <div class="project-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input
                id="projectSearch"
                type="text"
                placeholder="Tìm kiếm dự án...">
        </div>

        <select id="projectFilter">
            <option value="all">Tất cả trạng thái</option>
            <option value="Planning">Planning</option>
            <option value="In Progress">In Progress</option>
            <option value="Review">Review</option>
            <option value="Completed">Completed</option>
        </select>
    </div>

    <div class="project-grid">

        <?php if (empty($projects)): ?>

            <div class="card" style="padding:24px;">
                <p>Không có dự án nào.</p>
            </div>

        <?php else: ?>

            <?php foreach ($projects as $project): ?>

                <a
                    href="index.php?page=project-detail&id=<?= $project['project_id'] ?>"
                    class="project-card"
                    id="project-<?= $project['project_id'] ?>"
                    data-status="<?= htmlspecialchars($project['status'] ?? '') ?>">

                    <div class="project-card-header">

                        <div class="project-icon-box">
                            <i class="fa-solid fa-folder-closed"></i>
                        </div>

                        <div class="project-title-area">
                            <h3>
                                <?= htmlspecialchars($project['project_name']) ?>
                            </h3>

                            <div class="project-badges">
                                <span class="status-badge-project <?= strtolower(str_replace(' ', '-', $project['status'] ?? 'planning')) ?>">
                                    <?= htmlspecialchars($project['status'] ?? 'Planning') ?>
                                </span>

                                <span class="priority-badge <?= strtolower($project['priority'] ?? 'medium') ?>">
                                    <?= htmlspecialchars($project['priority'] ?? 'Medium') ?>
                                </span>
                            </div>
                        </div>

                    </div>

                    <p class="project-desc">
                        <?= htmlspecialchars($project['description'] ?? 'Chưa có mô tả') ?>
                    </p>

                    <div class="project-meta-grid">

                        <div class="meta-item">
                            <i class="fa-regular fa-calendar"></i>
                            <div class="meta-text">
                                <span class="meta-label">Deadline</span>
                                <span class="meta-value">
                                    <?= !empty($project['end_date'])
                                        ? date('d/m/Y', strtotime($project['end_date']))
                                        : 'Chưa có' ?>
                                </span>
                            </div>
                        </div>

                        <div class="meta-item">
                            <i class="fa-solid fa-users"></i>
                            <div class="meta-text">
                                <span class="meta-label">Team</span>
                                <span class="meta-value">
                                    <?= (int)$project['team_count'] ?> thành viên
                                </span>
                            </div>
                        </div>

                        <div class="meta-item">
                            <div class="avatar">
                                <?= strtoupper(substr($project['manager_name'], 0, 1)) ?>
                            </div>

                            <div class="meta-text">
                                <span class="meta-label">Manager</span>
                                <span class="meta-value">
                                    <?= htmlspecialchars($project['manager_name']) ?>
                                </span>
                            </div>
                        </div>

                    </div>

                    <div class="progress-section">
                        <div class="progress-header">
                            <span>Tiến độ</span>
                            <span><?= (int)($project['progress'] ?? 0) ?>%</span>
                        </div>

                        <div class="progress-bar">
                            <div
                                class="progress-fill"
                                style="width: <?= (int)($project['progress'] ?? 0) ?>%">
                            </div>
                        </div>
                    </div>

                </a>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>

<?php if ($roleId == 1 || $roleId == 2): ?>
    <?php include 'views/projects/modal.php'; ?>
<?php endif; ?>

<script src="assets/js/projects.js"></script>