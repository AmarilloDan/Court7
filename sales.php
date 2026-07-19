<?php
session_start();
require_once 'db_connect.php';

$filename = basename(__FILE__);
mysqli_query($conn, "INSERT INTO file_logs (filename) VALUES ('$filename')");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* Sales Reports */
$reports = mysqli_query($conn, "
SELECT id, report_date, total_sales
FROM reports
ORDER BY report_date DESC
");

/* Today's Sales */
$today = mysqli_query($conn, "
SELECT IFNULL(SUM(total_sales),0) AS total
FROM reports
WHERE report_date = CURDATE()
");
$today = mysqli_fetch_assoc($today);

/* Total Sales */
$total = mysqli_query($conn, "
SELECT IFNULL(SUM(total_sales),0) AS total
FROM reports
");
$total = mysqli_fetch_assoc($total);

/* Total Reservations */
$reservations = mysqli_query($conn, "
SELECT COUNT(*) AS total
FROM reservations
");
$reservations = mysqli_fetch_assoc($reservations);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background:#f5f5f5;}
        .card{border:none;box-shadow:0 2px 8px rgba(0,0,0,.1);}
        .table thead{background:#000;color:#fff;}
        .value{font-size:30px;font-weight:bold;color:#0d6efd;}
    </style>
</head>
<body>

<div class="container mt-4">

<h2 class="mb-4">Sales Report</h2>

<div class="row mb-4">

<div class="col-md-4">
<div class="card">
<div class="card-body text-center">
<h6>Today's Sales</h6>
<div class="value">
₱<?= number_format($today['total'],2); ?>
</div>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card">
<div class="card-body text-center">
<h6>Total Sales</h6>
<div class="value">
₱<?= number_format($total['total'],2); ?>
</div>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card">
<div class="card-body text-center">
<h6>Total Reservations</h6>
<div class="value">
<?= $reservations['total']; ?>
</div>
</div>
</div>
</div>

</div>

<div class="card">

<div class="card-header">
Sales History
</div>

<div class="card-body">

<table class="table table-bordered table-hover">

<thead>
<tr>
<th>ID</th>
<th>Report Date</th>
<th>Total Sales</th>
</tr>
</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($reports)){ ?>

<tr>
<td><?= $row['id']; ?></td>
<td><?= $row['report_date']; ?></td>
<td>₱<?= number_format($row['total_sales'],2); ?></td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<br>

<a href="index.php" class="btn btn-dark">Back to Home</a>

</div>

</body>
</html>