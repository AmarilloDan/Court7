<?php
require_once __DIR__ . '/includes/init.php';
require_role('admin');
$conn = db();

$user_id = (int) $_GET['user_id'];

if (!$user_id) {
    echo json_encode(['error' => 'Invalid user']);
    exit;
}

$debt_q = mysqli_query($conn, "
    SELECT COALESCE(SUM(amount), 0) AS debt
    FROM payments
    WHERE user_id = $user_id AND status = 'pending'
");
$debt = mysqli_fetch_assoc($debt_q)['debt'];

$balance_q = mysqli_query($conn, "
    SELECT COALESCE(SUM(amount), 0) AS balance
    FROM payments
    WHERE user_id = $user_id
");
$balance = mysqli_fetch_assoc($balance_q)['balance'];

$user_q = mysqli_query($conn, "SELECT name FROM users WHERE id = $user_id");
$user_name = mysqli_fetch_assoc($user_q)['name'];

$purchases_q = mysqli_query($conn, "
    SELECT product_name, quantity, total, created_at
    FROM sales
    WHERE customer_name = '" . mysqli_real_escape_string($conn, $user_name) . "'
    ORDER BY created_at DESC
");
$purchases = [];
while ($p = mysqli_fetch_assoc($purchases_q)) {
    $purchases[] = $p;
}

echo json_encode([
    'debt' => $debt,
    'balance' => $balance,
    'purchases' => $purchases
]);
?>