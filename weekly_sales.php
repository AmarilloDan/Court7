<?php
session_start();
require_once 'db_connect.php';

$filename = basename(__FILE__);
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS file_logs (id INT AUTO_INCREMENT PRIMARY KEY, filename VARCHAR(255) NOT NULL, accessed_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
mysqli_query($conn, "INSERT INTO file_logs (filename) VALUES ('" . mysqli_real_escape_string($conn, $filename) . "')");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$labels = [];
$sales = [];
$bookings = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date("Y-m-d", strtotime("-$i days"));

    $sales = $sales ?? [];
    $bookings = $bookings ?? [];

    $salesValue = 0;
    $bookingValue = 0;

    $salesQuery = mysqli_query($conn, "
        SELECT IFNULL(SUM(total_sales),0) AS sales
        FROM reports
        WHERE report_date='$date'
    ");
    if ($salesQuery) {
        $salesRow = mysqli_fetch_assoc($salesQuery);
        $salesValue = (float)($salesRow['sales'] ?? 0);
    }

    $bookingColumn = 'reservation_date';
    $bookingColumnsResult = mysqli_query($conn, 'SHOW COLUMNS FROM reservations');
    if ($bookingColumnsResult) {
        $bookingColumns = [];
        while ($col = mysqli_fetch_assoc($bookingColumnsResult)) {
            $bookingColumns[] = $col['Field'];
        }
        if (!in_array('reservation_date', $bookingColumns, true) && in_array('date', $bookingColumns, true)) {
            $bookingColumn = 'date';
        }
    }

    $bookingQuery = mysqli_query($conn, "
        SELECT COUNT(*) AS bookings
        FROM reservations
        WHERE $bookingColumn='$date'
    ");
    if ($bookingQuery) {
        $bookingRow = mysqli_fetch_assoc($bookingQuery);
        $bookingValue = (int)($bookingRow['bookings'] ?? 0);
    }

    $labels[] = date("D", strtotime($date));
    $sales[] = $salesValue;
    $bookings[] = $bookingValue;
}

$totalSales = array_sum($sales);
$totalBookings = array_sum($bookings);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Weekly Sales</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body style="background:#f5f5f5;">

<div class="container mt-4">

<h2>Weekly Sales Report</h2>

<div class="row mb-4">

<div class="col-md-6">
<div class="card">
<div class="card-body text-center">
<h5>Total Weekly Sales</h5>
<h2 class="text-success">
₱<?= number_format($totalSales,2); ?>
</h2>
</div>
</div>
</div>

<div class="col-md-6">
<div class="card">
<div class="card-body text-center">
<h5>Total Weekly Bookings</h5>
<h2 class="text-primary">
<?= $totalBookings; ?>
</h2>
</div>
</div>
</div>

</div>

<div class="card">

<div class="card-header bg-dark text-white">
Weekly Sales Chart
</div>

<div class="card-body">

<canvas id="chart"></canvas>

</div>

</div>

<br>

<table class="table table-bordered">

<thead class="table-dark">

<tr>
<th>Day</th>
<th>Bookings</th>
<th>Sales</th>
</tr>

</thead>

<tbody>

<?php for($i=0;$i<count($labels);$i++){ ?>

<tr>

<td><?= $labels[$i] ?></td>

<td><?= $bookings[$i] ?></td>

<td>₱<?= number_format($sales[$i],2) ?></td>

</tr>

<?php } ?>

</tbody>

</table>

<a href="index.php" class="btn btn-dark">Back to Home</a>

</div>

<script>

new Chart(document.getElementById('chart'),{

type:'bar',

data:{
labels: <?= json_encode($labels); ?>,
datasets:[{
label:'Sales',
data: <?= json_encode($sales); ?>,
backgroundColor:'#0d6efd'
}]
},

options:{
responsive:true,
plugins:{
legend:{display:false}
},
scales:{
y:{
beginAtZero:true
}
}
}

});

</script>

</body>
</html>