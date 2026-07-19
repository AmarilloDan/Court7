<?php
declare(strict_types=1);

function require_role($role) {
    if (!isset($_SESSION['role'])) {
        header("Location: index.php");
        exit;
    }

    if ($_SESSION['role'] !== $role) {
        header("Location: index.php");
        exit;
    }
}

function current_user_id(): int {
    return (int)($_SESSION['id'] ?? 0);
}

function current_user_name(): string {
    return (string)($_SESSION['name'] ?? '');
}

function current_user_role(): string {
    return (string)($_SESSION['role'] ?? '');
}
