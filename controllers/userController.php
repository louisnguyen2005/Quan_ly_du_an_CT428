<?php

require_once __DIR__ . '/../models/user.php';

class UserController
{
    private $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function index()
    {
        return $this->model->getAll();
    }

    public function store()
    {
        return $this->model->create($_POST);
    }

    public function getLastError(): string
    {
        return $this->model->getLastError();
    }

    public function edit($id)
    {
        return $this->model->find($id);
    }

    public function update($id)
    {
        return $this->model->update($id, $_POST);
    }

    public function destroy($id)
    {
        return $this->model->delete($id);
    }
    public function updateRole()
    {
        $userId =
            $_POST['user_id'];

        $roleId =
            $_POST['role_id'];

        return $this->model->updateRole(
            $userId,
            $roleId
        );
    }

    public function toggleStatus()
    {
        $userId = $_GET['id'];

        return $this->model->toggleStatus($userId);
    }

    public function updateProfile()
    {
        $userId = $_SESSION['user_id'];

        return $this->model->updateProfile($userId, $_POST);
    }
}