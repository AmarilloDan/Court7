<?php
namespace App\Models;
use App\Core\Database;

class Reservation {
    protected $db;
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    // Add reservation-related methods here
}
