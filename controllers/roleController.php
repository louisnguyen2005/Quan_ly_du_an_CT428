<?php

require_once __DIR__.'/../models/role.php';

class RoleController
{
    private $role;

    public function __construct()
    {
        $this->role = new Role();
    }

    public function index()
    {
        return $this->role->getAll();
    }
}