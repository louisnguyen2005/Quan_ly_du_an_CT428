<?php

require_once 'controllers/userController.php';

$userController = new UserController();

$users = $userController->index();

$managers = array_filter(
    $users,
    fn($u) => (int)$u['role_id'] === 2 || (int)$u['role_id'] === 1
);

?>

<div id="projectModal" class="modal">

    <div class="modal-content">

        <div class="modal-header">
            <h2>Tạo dự án mới</h2>

            <span class="close-project-modal">
                &times;
            </span>
        </div>

        <form
            method="POST"
            action="index.php?action=createProject">

            <div class="form-group">
                <label>Mã dự án</label>
                <input
                    type="text"
                    name="project_code"
                    placeholder="VD: PRJ-004">
            </div>

            <div class="form-group">
                <label>Tên dự án</label>
                <input
                    type="text"
                    name="name"
                    required>
            </div>

            <div class="form-group">
                <label>Mô tả</label>
                <textarea
                    name="description"
                    rows="4"></textarea>
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label>Ngày bắt đầu</label>
                    <input
                        type="date"
                        name="start_date"
                        value="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label>Deadline</label>
                    <input
                        type="date"
                        name="end_date">
                </div>

            </div>

            <div class="form-row">

                <div class="form-group">
                    <label>Manager</label>

                    <select name="manager_id">

                        <?php foreach ($managers as $user): ?>

                            <option value="<?= $user['user_id'] ?>">
                                <?= htmlspecialchars($user['username']) ?>
                                -
                                <?= htmlspecialchars($user['role_name']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Priority</label>

                    <select name="priority">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>

            </div>

            <div class="form-row">

                <div class="form-group">
                    <label>Status</label>

                    <select name="status">
                        <option value="Planning" selected>Planning</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Review">Review</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Progress</label>

                    <input
                        type="number"
                        name="progress"
                        value="0"
                        min="0"
                        max="100">
                </div>

            </div>

            <button
                type="submit"
                class="btn-primary">
                Tạo dự án
            </button>

        </form>

    </div>

</div>