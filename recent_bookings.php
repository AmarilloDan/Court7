<?php
session_start();
require_once 'db_connect.php';

$filename = basename(__FILE__);
mysqli_query($conn,"INSERT INTO file_logs(filename) VALUES('$filename')");

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

$dateColumn = 'reservation_date';
$revenueColumn = null;
$columnsResult = mysqli_query($conn, 'SHOW COLUMNS FROM reservations');
if ($columnsResult) {
    $columns = [];
    while ($column = mysqli_fetch_assoc($columnsResult)) {
        $columns[] = $column['Field'];
    }

    if (!in_array('reservation_date', $columns, true) && in_array('date', $columns, true)) {
        $dateColumn = 'date';
    }

    if (in_array('revenue', $columns, true)) {
        $revenueColumn = 'revenue';
    } elseif (in_array('amount', $columns, true)) {
        $revenueColumn = 'amount';
    }
}

$orderClause = "$dateColumn DESC";
if (in_array('id', $columns, true)) {
    $orderClause .= ', id DESC';
}

/* Latest 20 bookings */
$recent = mysqli_query($conn, "
SELECT *
FROM reservations
ORDER BY $orderClause
LIMIT 20
");

/* Total Reservations */
$total = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM reservations
");

$total = mysqli_fetch_assoc($total);

/* Total Revenue */
if ($revenueColumn) {
    $revenue = mysqli_query($conn, "
        SELECT IFNULL(SUM($revenueColumn),0) AS total
        FROM reservations
    ");
    $revenue = mysqli_fetch_assoc($revenue);
} else {
    $revenue = ['total' => 0];
}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Recent Bookings</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f5f5;
}

.card{
    border:none;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,.1);
}

.table thead{
    background:#000;
    color:#fff;
}

.value{
    font-size:30px;
    font-weight:bold;
    color:#0d6efd;
}

</style>

</head>

<body>

<div class="container mt-4">

<h2 class="mb-4">

Recent Bookings

</h2>

<div class="row mb-4">

<div class="col-md-4">

<div class="card">

<div class="card-body text-center">

<h6>Total Reservations</h6>

<div class="value">

<?= $total['total']; ?>

</div>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card">

<div class="card-body text-center">

<h6>Total Revenue</h6>

<div class="value">

₱<?= number_format($revenue['total'],2); ?>

</div>

</div>

</div>

</div>

<div class="col-md-4">

<input
type="text"
id="search"
class="form-control"
placeholder="Search customer...">

</div>

</div>

<div class="card">

<div class="card-header bg-dark text-white">

Recent Booking History

</div>

<div class="card-body">

<table class="table table-bordered table-hover" id="bookingTable">

<thead>

<tr>

<th>ID</th>
<th>Customer</th>
<th>Date</th>
<th>Court</th>
<th>Revenue</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($recent)){ ?>

<tr>

<td><?= $row['id']; ?></td>

<td><?= htmlspecialchars($row['name'] ?? '-'); ?></td>

<td><?= !empty($row[$dateColumn]) ? date("F d, Y", strtotime($row[$dateColumn])) : '-'; ?></td>

<td>Court <?= $row['court_id'] ?? $row['court'] ?? '-'; ?></td>

<td>

₱<?= number_format(isset($row[$revenueColumn]) ? (float) $row[$revenueColumn] : 0, 2); ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<br>

<div class="d-flex justify-content-between">

<a href="index.php" class="btn btn-dark">

Back to Home

</a>

<button onclick="window.print()" class="btn btn-success">

Print Report

</button>

</div>

</div>

<script>

const search=document.getElementById("search");

search.addEventListener("keyup",function(){

let value=this.value.toLowerCase();

let rows=document.querySelectorAll("#bookingTable tbody tr");

rows.forEach(function(row){

row.style.display=row.innerText.toLowerCase().includes(value)
? ""
: "none";

});

});

</script>

</body>
</html>