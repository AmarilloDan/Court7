<?php
namespace App\Controllers;

class UserDashboardController {
	public function index() {
		require __DIR__ . '/../Views/user_dashboard.php';
	}
}
