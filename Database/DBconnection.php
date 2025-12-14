<?php

class DBconnection
{
    private $connectionString = "mysql:host=localhost;dbname=salah_tracker;charset=utf8mb4";

    private $dbUser = 'root';
    private $dbPass = '';

    private $conn = null;

    public function __construct()
    {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->conn = new PDO($this->connectionString, $this->dbUser, $this->dbPass, $options);
    }

    public function DmlQuery($query)
    {
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function DQlQuery($query)
    {
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();   
    }
}
