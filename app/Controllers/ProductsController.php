<?php
namespace App\Controllers;

class ProductsController {
    public function index() {
        require __DIR__ . '/../Views/products.php';
    }
}
