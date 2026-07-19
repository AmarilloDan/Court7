<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'court_system';

mysqli_report(MYSQLI_REPORT_OFF);

$conn = null;

$rootConn = @mysqli_connect($host, $user, $pass);
if ($rootConn) {
    @mysqli_query($rootConn, "CREATE DATABASE IF NOT EXISTS `court_system`");
    @mysqli_close($rootConn);
}

$conn = @mysqli_connect($host, $user, $pass, $db);
if ($conn) {
    @mysqli_query($conn, "SET NAMES utf8mb4");
    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS courts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        type VARCHAR(50) NOT NULL
    )");

    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        reservation_date DATE DEFAULT NULL,
        time_slot VARCHAR(50) DEFAULT NULL,
        court INT DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        name VARCHAR(100) NOT NULL,
        date DATE DEFAULT NULL,
        revenue DECIMAL(10,2) DEFAULT 0.00,
        court_id INT DEFAULT NULL,
        FOREIGN KEY (court_id) REFERENCES courts(id) ON DELETE SET NULL
    )");

    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        address VARCHAR(255) DEFAULT NULL,
        contact_number VARCHAR(50) DEFAULT NULL,
        profile_photo VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    @mysqli_query($conn, "ALTER TABLE users MODIFY COLUMN name VARCHAR(100) NOT NULL");
    @mysqli_query($conn, "ALTER TABLE users MODIFY COLUMN email VARCHAR(100) NOT NULL");
    @mysqli_query($conn, "ALTER TABLE users MODIFY COLUMN username VARCHAR(50) NOT NULL");
    @mysqli_query($conn, "ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL");

    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_date DATE NOT NULL,
        total_bookings INT DEFAULT 0,
        total_sales DECIMAL(10,2) DEFAULT 0.00
    )");

    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS debts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        debt_date DATE NOT NULL,
        description VARCHAR(255)
    )");

    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS supplies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        quantity INT NOT NULL,
        category VARCHAR(50),
        date_added DATE NOT NULL
    )");

    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS file_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        accessed_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reservation_id INT NOT NULL,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        method VARCHAR(50) DEFAULT NULL,
        reference VARCHAR(100) DEFAULT NULL,
        proof VARCHAR(255) DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}
?>