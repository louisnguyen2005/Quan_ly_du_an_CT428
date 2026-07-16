<?php

require_once __DIR__ . '/../config/database.php';

class Role
{
    private PDO $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAll(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM roles ORDER BY role_id ASC"
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $roleName, string $roleCode = ''): bool
    {
        $roleCode = $roleCode !== ''
            ? strtoupper($roleCode)
            : strtoupper(preg_replace('/[^A-Z0-9_]/', '_', $roleName));

        $stmt = $this->conn->prepare(
            "INSERT INTO roles(role_code, name) VALUES(?, ?)"
        );

        return $stmt->execute([$roleCode, $roleName]);
    }

    public function update(int $id, string $name): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE roles SET name = ? WHERE role_id = ?"
        );

        return $stmt->execute([$name, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM roles WHERE role_id = ?"
        );

        return $stmt->execute([$id]);
    }
}
