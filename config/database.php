<?php

function db(): mysqli {
    static $conn = null;

    if ($conn === null) {
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $db   = 'court7';

        $conn = mysqli_connect($host, $user, $pass, $db);

        if (!$conn) {
            $fallback = mysqli_connect($host, $user, $pass);
            if (!$fallback) {
                die('Database connection failed: ' . mysqli_connect_error());
            }
            mysqli_query($fallback, 'CREATE DATABASE IF NOT EXISTS court7');
            mysqli_select_db($fallback, 'court7');
            $conn = $fallback;
        }

        mysqli_set_charset($conn, 'utf8mb4');
    }

    return $conn;
}