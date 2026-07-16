<?php

class Database
{
    private $host = "localhost";
    private $dbname = "project_management";
    private $username = "root";
    private $password = "";

   public ?PDO $conn;

    public function connect()
    {
        $this->conn = null;

        try {

            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            $this->conn->setAttribute(
                PDO::ATTR_EMULATE_PREPARES,
                true
            );

        } catch(PDOException $e) {

            die("Database Error: " . $e->getMessage());

        }

        return $this->conn;
    }
}