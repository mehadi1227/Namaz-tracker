<?php

class DBconnection
{
    private $dbHost = 'localhost';
    private $dbUser = 'root';
    private $dbPass = '';
    private $dbName = 'salah_tracker';

    public function openConnection()
    {
        $connection = new mysqli($this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);

        if ($connection->connect_error) {
            die("Database connection failed: " . $connection->connect_error);
        }

        $connection->set_charset("utf8mb4");

        return $connection;
    }

    public function userRegistration($connection, $tableName, $name, $email, $password, $timezone)
    {

        $sql = "INSERT INTO `$tableName` (name, email, password, timezone)
                VALUES (?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        if (!$stmt) {
            return 0; 
        }

        $stmt->bind_param("ssss", $name, $email, $password, $timezone);

        $stmt->execute();
        $result = $stmt->affected_rows;

        $stmt->close();
        return $result;
    }

    public function userLogin($connection, $tableName, $email)
    {

        $sql = "SELECT id,password FROM {$tableName} WHERE email=?";

        $stmt = $connection->prepare($sql);
        if (!$stmt) {
            return 0; 
        }

        $stmt->bind_param("s", $email);

        $stmt->execute();
        $result = $stmt->get_result();

        $stmt->close();
        return $result;
    }

    public function closeConnection($connection)
    {
        $connection->close();
    }
}
