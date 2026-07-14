<?php
require_once 'controllers/projectController.php';
require_once 'controllers/taskController.php';

$id = $_GET['id'] ?? 0;

$projectController = new ProjectController();
$taskController = new TaskController();

$project = $projectController->detail($id);

if (!$project) {
    die("<div class='detail-container'><p style='color:red; font-weight:bold;'>Dự án không tồn tại hoặc đã bị xóa!</p></div>");
}

$members = $projectController->members($id);
$tasks = $taskController->getByProject($id);
?>

<style>
.detail-container{
    width:100%;
    max-width:1200px;
    margin:0 auto;
    background:var(--bg-card);
    color:var(--text-main);
    padding:24px;
    border-radius:12px;
    border:1px solid var(--border-color);
    box-sizing:border-box;
}

.btn-back{
    display:inline-flex;
    align-items:center;
    gap:8px;
    text-decoration:none;
    color:var(--text-sub);
    font-weight:600;
    margin-bottom:20px;
    font-size:14px;
}

.btn-back:hover{
    color:#2563eb;
}

.header-section{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    border-bottom:1px solid var(--border-color);
    padding-bottom:20px;
    margin-bottom:25px;
    margin-top:4rem;
}

.header-section h1{
    margin:0 0 12px 0;
    font-size:26px;
    font-weight:700;
    color:var(--text-main);
}

.badges-row{
    display:flex;
    gap:10px;
}

.project-detail-badge{
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
    text-transform:uppercase;
    display:inline-flex;
    align-items:center;
}

/* STATUS */
.status-in-progress{
    background:#dbeafe;
    color:#1d4ed8;
}

.status-planning{
    background:#ffedd5;
    color:#ea580c;
}

.status-review{
    background:#f3e8ff;
    color:#9333ea;
}

.status-completed{
    background:#dcfce7;
    color:#16a34a;
}

/* PRIORITY */
.priority-high{
    background:#fee2e2;
    color:#dc2626;
}

.priority-critical{
    background:#fee2e2;
    color:#dc2626;
}

.priority-medium{
    background:#fef3c7;
    color:#d97706;
}

.priority-low{
    background:#e2e8f0;
    color:#475569;
}

.main-layout{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:24px;
    width:100%;
}

.left-column,
.right-column{
    min-width:0;
}

.desc-box{
    background:var(--bg-app);
    padding:20px;
    border-radius:8px;
    margin-bottom:25px;
    border-left:4px solid #2563eb;
}

.desc-box h3,
.task-section h3,
.sidebar-card h3{
    margin-top:0;
    margin-bottom:15px;
    font-size:16px;
    font-weight:700;
    color:var(--text-main);
}

.desc-box p{
    line-height:1.6;
    color:var(--text-sub);
    margin:0;
    font-size:14px;
}

/* TABLE */
.table-responsive{
    width:100%;
    overflow-x:auto;
    margin-top:15px;
    border:1px solid var(--border-color);
    border-radius:8px;
}

.task-table{
    width:100%;
    border-collapse:collapse;
    text-align:left;
    font-size:14px;
    min-width:500px;
    background:var(--bg-card);
}

.task-table th,
.task-table td{
    padding:12px 16px;
    border-bottom:1px solid var(--border-color);
}

.task-table th{
    background:var(--bg-app);
    color:var(--text-main);
    font-weight:600;
}

.task-table td{
    color:var(--text-main);
}

/* SIDEBAR */
.sidebar-card{
    background:var(--bg-card);
    border:1px solid var(--border-color);
    padding:20px;
    border-radius:8px;
    margin-bottom:20px;
}

.meta-list-item{
    display:flex;
    justify-content:space-between;
    padding:12px 0;
    border-bottom:1px solid var(--border-color);
    font-size:14px;
}

.meta-list-item:last-child{
    border:none;
}

.meta-label{
    color:var(--text-sub);
    display:flex;
    align-items:center;
    gap:8px;
}

.meta-val{
    font-weight:600;
    color:var(--text-main);
}

/* PROGRESS */
.progress-container{
    margin:15px 0;
}

.progress-info{
    display:flex;
    justify-content:space-between;
    font-weight:600;
    font-size:13px;
    margin-bottom:8px;
}

.progress-bar-bg{
    background:var(--border-color);
    height:8px;
    border-radius:999px;
    overflow:hidden;
}

.progress-bar-fill{
    background:#2563eb;
    height:100%;
    border-radius:999px;
}

/* MANAGER */
.manager-info{
    display:flex;
    align-items:center;
    gap:12px;
    margin-top:10px;
}

.manager-avatar-text{
    width:40px;
    height:40px;
    border-radius:50%;
    background:var(--bg-app);
    border:1px solid var(--border-color);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
    color:var(--text-main);
}

.manager-name{
    font-weight:600;
    font-size:15px;
    margin:0;
    color:var(--text-main);
}

.manager-role{
    font-size:12px;
    color:var(--text-sub);
    margin:2px 0 0 0;
}

@media(max-width:768px){
    .main-layout{
        grid-template-columns:1fr;
    }

    .header-section{
        flex-direction:column;
        gap:12px;
    }
}
</style>
<div class="detail-container">
    
    <a href="index.php?page=projects" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách dự án
    </a>

    <div class="header-section">
        <div>
            <h1><?= htmlspecialchars($project['project_name'] ?? '') ?></h1>
            <div class="badges-row">
                <span class="project-detail-badge status-<?= strtolower(str_replace(' ','-', $project['status'] ?? 'planning')) ?>">
                    <i class="fa-solid fa-circle-dot" style="font-size: 8px; margin-right: 6px;"></i><?= htmlspecialchars($project['status'] ?? 'Planning') ?>
                </span>
                <span class="project-detail-badge priority-<?= strtolower($project['priority'] ?? 'medium') ?>">
                    Thứ tự: <?= htmlspecialchars($project['priority'] ?? 'Medium') ?>
                </span>
            </div>
        </div>
    </div>

    <div class="main-layout">
        
        <div class="left-column">
            <div class="desc-box">
                <h3><i class="fa-solid fa-file-lines"></i> Mô tả dự án</h3>
                <p><?= nl2br(htmlspecialchars($project['description'] ?? 'Chưa có mô tả cụ thể cho dự án này.')) ?></p>
            </div>

            <div class="task-section">
                <h3><i class="fa-solid fa-list-check"></i> Danh sách nhiệm vụ thuộc dự án</h3>
                <?php if(empty($tasks)): ?>
                    <p style="color: #94a3b8; font-style: italic; margin-top: 15px; font-size: 14px;">Dự án này chưa được phân bổ nhiệm vụ nào.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="task-table">
                            <thead>
                                <tr>
                                    <th>Mã NV</th>
                                    <th>Tên nhiệm vụ</th>
                                    <th>Người thực hiện</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($tasks as $task): ?>
                                <tr>
                                    <td style="font-weight: 600; color: #2563eb;">#<?= $task['task_id'] ?></td>
                                    <td><?= htmlspecialchars($task['task_name'] ?? '') ?></td>
                                    <td><i class="fa-regular fa-user" style="margin-right: 6px; font-size: 12px; color:#94a3b8;"></i><?= htmlspecialchars($task['assigned_name'] ?? 'Chưa phân công') ?></td>
                                    <td>
                                        <span class="project-detail-badge status-<?= strtolower(str_replace(' ','-', $task['status'] ?? 'planning')) ?>" style="padding: 4px 10px; font-size: 11px;">
                                            <?= htmlspecialchars($task['status'] ?? '') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="right-column">
            
            <div class="sidebar-card">
                <h3>Thông tin chung</h3>
                <div class="progress-container">
                    <div class="progress-info">
                        <span class="meta-label">Tiến độ hoàn thành</span>
                        <span><?= (int)($project['progress'] ?? 0) ?>%</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?= (int)($project['progress'] ?? 0) ?>%"></div>
                    </div>
                </div>
                
                <div class="meta-list-item" style="margin-top: 15px;">
                    <span class="meta-label"><i class="fa-regular fa-calendar-check"></i> Hạn cuối:</span>
                    <span class="meta-val" style="color: #dc2626;"><?= isset($project['end_date']) ? date('d/m/Y', strtotime($project['end_date'])) : '--/--/----' ?></span>
                </div>
                <div class="meta-list-item">
                    <span class="meta-label"><i class="fa-solid fa-users"></i> Nhân sự:</span>
                    <span class="meta-val"><?= (int)($project['team_count'] ?? 0) ?> thành viên</span>
                </div>
            </div>

            <div class="sidebar-card">
                <h3>Người quản lý</h3>
                <div class="manager-info">
                    <div class="manager-avatar-text">
                        <?= strtoupper(substr($project['manager_name'] ?? 'C', 0, 1)) ?>
                    </div>
                    <div>
                        <p class="manager-name"><?= htmlspecialchars($project['manager_name'] ?? 'Chưa phân công') ?></p>
                        <p class="manager-role">Project Manager</p>
                    </div>
                </div>
            </div>

            <div class="sidebar-card">
                <h3>Thành viên dự án</h3>
                <div class="table-responsive">
                    <table class="task-table" style="min-width: 100%;">
                        <thead>
                            <tr>
                                <th>Tên</th>
                                <th>Vai trò</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(empty($members)): ?>
                            <tr>
                                <td colspan="2" style="color: #94a3b8; font-style: italic; text-align: center;">Chưa có thành viên nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($members as $member): ?>
                                <tr>
                                    <td style="font-weight: 500;">
                                        <?= htmlspecialchars($member['username'] ?? '') ?>
                                    </td>
                                    <td style="font-size: 13px;">
                                        <?= htmlspecialchars($member['role_in_project'] ?? 'Thành viên') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>  

        </div>
    </div>
</div>