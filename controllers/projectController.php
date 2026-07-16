<?php

require_once __DIR__ . '/../models/project.php';

class ProjectController
{
    private $project;

    public function __construct()
    {
        $this->project = new Project();
    }

    public function index()
    {
        $roleId = $_SESSION['role_id'] ?? 0;
        $userId = $_SESSION['user_id'] ?? 0;

        if ($roleId == 1) {
            return $this->project->getAll();
        }

        if ($roleId == 2) {
            return $this->project->getByManager($userId);
        }

        if ($roleId == 3) {
            return $this->project->getByStaff($userId);
        }

        return [];
    }

    public function detail($id)
    {
        return $this->project->find($id);
    }

    public function ajaxDetail($id)
    {
        $roleId = (int)($_SESSION['role_id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if ($roleId === 3) {
            return $this->project->findForStaff((int)$id, $userId);
        }

        return $this->project->find((int)$id);
    }

    public function members($id)
    {
        return $this->project->getMembers($id);
    }

    public function store()
    {
        return $this->project->create($_POST);
    }

    public function update($id)
    {
        return $this->project->update($id, $_POST);
    }

    public function destroy($id)
    {
        return $this->project->delete($id);
    }

    public function countAll()
    {
        return $this->project->countAll();
    }

    public function countByManager($managerId)
    {
        return $this->project->countByManager($managerId);
    }
}
