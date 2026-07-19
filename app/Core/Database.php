<?php
namespace App\Core;

class Database {
    private $conn;
    public function __construct() {
        $this->conn = new \mysqli('localhost', 'root', '', 'court7');
        if ($this->conn->connect_error) {
            $fallback = new \mysqli('localhost', 'root', '');
            if ($fallback->connect_error) {
                die('Database connection failed: ' . $fallback->connect_error);
            }
            $fallback->query('CREATE DATABASE IF NOT EXISTS court7');
            $fallback->select_db('court7');
            $this->conn = $fallback;
        }
        $this->conn->set_charset('utf8mb4');
    }
    public function getConnection() {
        return $this->conn;
    }
}
