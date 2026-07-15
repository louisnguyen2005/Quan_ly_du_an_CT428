<?php

require_once __DIR__ . '/../models/staffTask.php';

class StaffTaskController
{
    private StaffTaskModel $model;

    public function __construct()
    {
        $this->model = new StaffTaskModel();
    }

    private function currentStaffId(): int
    {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    private function isStaff(): bool
    {
        return $this->currentStaffId() > 0 && (int)($_SESSION['role_id'] ?? 0) === ROLE_STAFF;
    }

    /**
     * Builds the full view-model for views/staff/tasks/index.php from $_GET.
     */
    public function buildViewModel(array $query): ?array
    {
        if (!$this->isStaff()) {
            return null;
        }

        $staffId = $this->currentStaffId();

        $filters = [
            'tab'        => $query['tab'] ?? 'all',
            'search'     => trim($query['search'] ?? ''),
            'project_id' => $query['project_id'] ?? '',
            'priority'   => $query['priority'] ?? '',
            'sort'       => $query['sort'] ?? 'deadline',
        ];

        return [
            'filters'  => $filters,
            'tasks'    => $this->model->getFilteredTasks($staffId, $filters),
            'counts'   => $this->model->getTabCounts($staffId),
            'projects' => $this->model->getStaffProjectOptions($staffId),
        ];
    }

    public function startTask(int $taskId): bool
    {
        return $this->isStaff() && $this->model->startTask($taskId, $this->currentStaffId());
    }

    public function pauseTask(int $taskId): bool
    {
        return $this->isStaff() && $this->model->pauseTask($taskId, $this->currentStaffId());
    }

    public function resumeTask(int $taskId): bool
    {
        return $this->isStaff() && $this->model->resumeTask($taskId, $this->currentStaffId());
    }

    public function submitForReview(int $taskId): bool
    {
        return $this->isStaff() && $this->model->submitForReview($taskId, $this->currentStaffId());
    }

    public function updateProgress(int $taskId, int $progress): bool
    {
        return $this->isStaff() && $this->model->updateProgress($taskId, $this->currentStaffId(), $progress);
    }
}
