<?php
namespace App\Controllers;

class LogoutController {
    public function index() {
        session_start();
        session_destroy();
        header('Location: /login');
        exit();
    }
}
