<?php

require_once __DIR__ . '/../models/staffDashboard.php';

class StaffDashboardController
{
    private StaffDashboardModel $model;

    public function __construct()
    {
        $this->model = new StaffDashboardModel();
    }

    /**
     * Builds everything the Dashboard view needs, scoped to the
     * currently logged-in staff user. Returns null if not authenticated
     * as staff (the view/router is expected to have already enforced this,
     * this is a defense-in-depth check).
     */
    public function buildViewModel(): ?array
    {
        $staffId = (int)($_SESSION['user_id'] ?? 0);
        $roleId  = (int)($_SESSION['role_id'] ?? 0);

        if ($staffId <= 0 || $roleId !== ROLE_STAFF) {
            return null;
        }

        $counters = $this->model->getCounters($staffId);

        return [
            'staff_id'          => $staffId,
            'counters'          => $counters,
            'completion_rate'   => $counters['total_tasks'] > 0
                ? (int)round($counters['completed_tasks'] / $counters['total_tasks'] * 100)
                : 0,
            'upcoming_deadlines' => $this->model->getUpcomingDeadlines($staffId, 7, 5),
            'recent_tasks'       => $this->model->getRecentAssignedTasks($staffId, 5),
            'notifications'      => $this->model->getRecentNotifications($staffId, 5),
            'activities'         => $this->model->getRecentActivities($staffId, 6),
            'projects'           => $this->model->getMyProjectsProgress($staffId, 4),
        ];
    }
}
