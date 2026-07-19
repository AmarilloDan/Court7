<?php
require_once 'db_connect.php';
$filename = basename(__FILE__);
mysqli_query($conn, "INSERT INTO file_logs (filename) VALUES ('$filename')");
header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $result = $conn->query('SELECT * FROM reservations');
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
} elseif ($action === 'add') {
    $name = $_POST['name'];
    $date = $_POST['date'];
    $revenue = $_POST['revenue'] ?? 0;
    $stmt = $conn->prepare('INSERT INTO reservations (name, date, revenue) VALUES (?, ?, ?)');
    $stmt->bind_param('ssd', $name, $date, $revenue);
    $stmt->execute();
    echo json_encode(['success' => true]);
} elseif ($action === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $date = $_POST['date'];
    $revenue = $_POST['revenue'] ?? 0;
    $stmt = $conn->prepare('UPDATE reservations SET name=?, date=?, revenue=? WHERE id=?');
    $stmt->bind_param('ssdi', $name, $date, $revenue, $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
} elseif ($action === 'delete') {
    $id = $_POST['id'];
    $stmt = $conn->prepare('DELETE FROM reservations WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
} elseif ($action === 'revenue_by_date') {
    $date = $_GET['date'] ?? '';
    if ($date) {
        $stmt = $conn->prepare('SELECT SUM(revenue) as total_revenue FROM reservations WHERE date = ?');
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo json_encode(['date' => $date, 'total_revenue' => $row['total_revenue'] ?? 0]);
    } else {
        echo json_encode(['error' => 'Date required']);
    }
} else {
    echo json_encode(['error' => 'Invalid action']);
}
?>