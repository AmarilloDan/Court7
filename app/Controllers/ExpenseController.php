<?php
namespace App\Controllers;

class ExpenseController {
    public function index() {
        require __DIR__ . '/../Views/expenses.php';
    }
}
