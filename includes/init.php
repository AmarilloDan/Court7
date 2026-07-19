<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/view.php';

function require_login() {
    if (!isset($_SESSION['id'])) {
        header("Location: index.php");
        exit;
    }
}