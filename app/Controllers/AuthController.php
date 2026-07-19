<?php
namespace App\Controllers;

class AuthController {
    public function login() {
        require __DIR__ . '/../Views/login.php';
    }
    public function register() {
        require __DIR__ . '/../Views/register.php';
    }
    public function logout() {
        // Logic for logout
        session_start();
        session_destroy();
        header('Location: /login');
        exit();
    }
}
