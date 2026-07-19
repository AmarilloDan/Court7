<?php
require_once 'db_connect.php';

if ($conn) {
    echo "DB_OK\n";
    $result = mysqli_query($conn, "SELECT DATABASE() AS db");
    $row = mysqli_fetch_assoc($result);
    echo "DB_NAME=" . ($row['db'] ?? 'none') . "\n";
    $res = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    echo "USERS_TABLE=" . ($res && $res->num_rows > 0 ? 'yes' : 'no') . "\n";
} else {
    echo "DB_FAIL\n";
}
