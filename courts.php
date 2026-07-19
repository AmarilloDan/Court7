<?php
require_once 'db_connect.php';
$filename = basename(__FILE__);
mysqli_query($conn, "INSERT INTO file_logs (filename) VALUES ('$filename')");
header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $result = $conn->query('SELECT * FROM courts');
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
} elseif ($action === 'add') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $stmt = $conn->prepare('INSERT INTO courts (name, type) VALUES (?, ?)');
    $stmt->bind_param('ss', $name, $type);
    $stmt->execute();
    echo json_encode(['success' => true]);
} elseif ($action === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $type = $_POST['type'];
    $stmt = $conn->prepare('UPDATE courts SET name=?, type=? WHERE id=?');
    $stmt->bind_param('ssi', $name, $type, $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
} elseif ($action === 'delete') {
    $id = $_POST['id'];
    $stmt = $conn->prepare('DELETE FROM courts WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Invalid action']);
}
?>