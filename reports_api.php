<?php
require_once 'db_connect.php';
$filename = basename(__FILE__);
mysqli_query($conn, "INSERT INTO file_logs (filename) VALUES ('$filename')");
header('Content-Type: application/json');

$sql = "SELECT date, COUNT(*) as bookings FROM reservations WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY date ORDER BY date ASC";
$result = $conn->query($sql);
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
echo json_encode($rows);
?>