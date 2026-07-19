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
$columnsResult = mysqli_query($conn, 'SHOW COLUMNS FROM reservations');
if ($columnsResult) {
    $columns = [];
    while ($column = mysqli_fetch_assoc($columnsResult)) {
        $columns[] = $column['Field'];
    }

    if (!in_array('reservation_date', $columns, true) && in_array('date', $columns, true)) {
        $dateColumn = 'date';
    }
}

$bookings = mysqli_query($conn, "
SELECT *
FROM reservations
WHERE $dateColumn >= CURDATE()
ORDER BY $dateColumn ASC
");

$totalResult = mysqli_query($conn, "
SELECT COUNT(*) AS total
FROM reservations
WHERE $dateColumn >= CURDATE()
");

$total = mysqli_fetch_assoc($totalResult);
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Upcoming Bookings</title>

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
    color:white;
}

.value{
    font-size:32px;
    font-weight:bold;
    color:#0d6efd;
}

</style>

</head>

<body>

<div class="container mt-4">

<h2 class="mb-4">

Upcoming Bookings

</h2>

<div class="row mb-4">

<div class="col-md-4">

<div class="card">

<div class="card-body text-center">

<h6>Total Upcoming Bookings</h6>

<div class="value">

<?= $total['total']; ?>

</div>

</div>

</div>

</div>

<div class="col-md-4">

<input
type="text"
class="form-control"
id="search"
placeholder="Search customer...">

</div>

<div class="col-md-4 text-end">

<button class="btn btn-success">

Print

</button>

<button class="btn btn-primary">

Refresh

</button>

</div>

</div>

<div class="card">

<div class="card-header bg-dark text-white">

Upcoming Booking List

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

<th>Status</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($bookings)){ ?>

<tr>

<td><?= $row['id']; ?></td>

<td><?= htmlspecialchars($row['name'] ?? '-'); ?></td>

<td><?= !empty($row[$dateColumn]) ? date("F d, Y", strtotime($row[$dateColumn])) : '-'; ?></td>

<td>Court <?= $row['court_id'] ?? $row['court'] ?? '-'; ?></td>

<td>₱<?= isset($row['revenue']) ? number_format($row['revenue'],2) : '0.00'; ?></td>

<td>

<span class="badge bg-success">

Upcoming

</span>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<br>

<a href="index.php" class="btn btn-dark">

Back to Home

</a>

</div>

<script>

const search=document.getElementById("search");

search.addEventListener("keyup",function(){

let value=this.value.toLowerCase();

let rows=document.querySelectorAll("#bookingTable tbody tr");

rows.forEach(function(row){

row.style.display=row.innerText.toLowerCase().includes(value)
?"":"none";

});

});

</script>

</body>
</html>