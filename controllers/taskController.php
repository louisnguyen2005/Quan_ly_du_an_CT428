<?php

require_once __DIR__ . '/../models/task.php';

class TaskController
{
    private Task $task;

    public function __construct()
    {
        $this->task = new Task();
    }

    public function index(): array
    {
        $roleId = $_SESSION['role_id'] ?? 0;
        $userId = $_SESSION['user_id'] ?? 0;

        if ($roleId == 1) {
            return $this->task->getAll();
        }

        if ($roleId == 2) {
            return $this->task->getByManager($userId);
        }

        if ($roleId == 3) {
            return $this->task->getByStaff($userId);
        }

        return [];
    }

    public function detail($id): ?array
    {
        return $this->task->find((int)$id);
    }

    public function store(): bool
    {
        return $this->task->create($_POST);
    }

    public function update($id): bool
    {
        return $this->task->update((int)$id, $_POST);
    }

    public function destroy($id): bool
    {
        return $this->task->delete((int)$id);
    }

    public function getByProject($projectId): array
    {
        return $this->task->getByProject((int)$projectId);
    }

    public function updateStatus(): bool
    {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $status = $_POST['status'] ?? 'Pending';
        $userId = $_SESSION['user_id'] ?? 0;

        return $this->task->updateStatus($taskId, $status, $userId);
    }

    public function addComment(): bool
    {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $userId = $_SESSION['user_id'] ?? 0;

        if ($taskId <= 0 || $content === '') {
            return false;
        }

        return $this->task->addComment($taskId, $userId, $content);
    }

    public function uploadReport(): bool
    {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $userId = $_SESSION['user_id'] ?? 0;

        if ($taskId <= 0 || empty($_FILES['report_file'])) {
            return false;
        }

        return $this->task->addAttachment($taskId, $userId, $_FILES['report_file']);
    }

    public function countAll(): int
    {
        return $this->task->countAll();
    }

    public function countCompleted(): int
    {
        return $this->task->countCompleted();
    }

    public function countByManager($managerId): int
    {
        return $this->task->countByManager((int)$managerId);
    }

    public function countCompletedByManager($managerId): int
    {
        return $this->task->countCompletedByManager((int)$managerId);
    }

    public function countByStaff($staffId): int
    {
        return $this->task->countByStaff((int)$staffId);
    }

    public function countCompletedByStaff($staffId): int
    {
        return $this->task->countCompletedByStaff((int)$staffId);
    }
}
