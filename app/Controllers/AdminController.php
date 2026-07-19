<?php
namespace App\Controllers;

class AdminController {
    public function dashboard() {
        require __DIR__ . '/../Views/admin_dashboard.php';
    }
    public function bookings() {
        require __DIR__ . '/../Views/admin_bookings.php';
    }
    public function checkTime() {
        require __DIR__ . '/../Views/admin_check_time.php';
    }
    public function login() {
        require __DIR__ . '/../Views/admin_login.php';
    }
}
