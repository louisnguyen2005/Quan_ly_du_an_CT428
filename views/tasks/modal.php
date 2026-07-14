<?php
/** @var array $projects */
/** @var array $users */

$staffUsers = array_filter(
    $users,
    fn($u) => (int)$u['role_id'] === 3
);
?>

<div id="taskModal" class="modal">

    <div class="modal-content">

        <div class="modal-header">
            <h2>Create Task</h2>
            <span class="close-task-modal" style="font-size: 20px;">
                &times;
            </span>
        </div>

        <form
            method="POST"
            action="index.php?action=createTask">

            <div class="form-group">
                <label>Task Name</label>
                <input
                    type="text"
                    name="title"
                    required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea
                    name="description"
                    rows="4"></textarea>
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label>Dự án</label>

                    <select name="project_id" required>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['project_id'] ?>">
                                <?= htmlspecialchars($project['project_name'] ?? $project['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Assignee</label>

                    <select name="assignee_id">
                        <option value="">Chưa phân công</option>

                        <?php foreach ($staffUsers as $user): ?>
                            <option value="<?= $user['user_id'] ?>">
                                <?= htmlspecialchars($user['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="form-row">

                <div class="form-group">
                    <label>Priority</label>

                    <select name="priority">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>

                    <select name="status">
                        <option value="Pending" selected>Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Review">Review</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>

            </div>

            <div class="form-group">
                <label>Due Date</label>
                <input
                    type="date"
                    name="due_date">
            </div>

            <button class="btn-primary save-btn" type="submit">
                Save Task
            </button>

        </form>

    </div>
</div>
