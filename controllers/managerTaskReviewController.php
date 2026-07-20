<?php

require_once __DIR__ . '/../models/managerTaskReview.php';

class ManagerTaskReviewController
{
    private ManagerTaskReviewModel $model;

    public function __construct()
    {
        $this->model = new ManagerTaskReviewModel();
    }

    private function currentManagerId(): int
    {
        $roleId = (int)($_SESSION['role_id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);

        return ($roleId === ROLE_MANAGER && $userId > 0) ? $userId : 0;
    }

    public function reviewQueue(): array
    {
        $managerId = $this->currentManagerId();

        if ($managerId <= 0) {
            return [];
        }

        return $this->model->reviewQueue($managerId);
    }

    public function approve(int $taskId): bool
    {
        $managerId = $this->currentManagerId();

        return $managerId > 0 && $this->model->approve($taskId, $managerId);
    }

    public function reject(int $taskId, string $note): bool
    {
        $managerId = $this->currentManagerId();

        return $managerId > 0 && $this->model->reject($taskId, $managerId, $note);
    }
}