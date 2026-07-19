<?php
namespace App\Models;
use App\Core\Database;

class User {
    protected $db;
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    // Add user-related methods here
}
