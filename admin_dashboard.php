<?php
session_start();
require_once 'db_connect.php';

$filename = basename(__FILE__);
if ($conn && !empty($conn->thread_id)) {
    @mysqli_query($conn, "INSERT INTO file_logs (filename) VALUES ('" . mysqli_real_escape_string($conn, $filename) . "')");
}

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* ==========================
   DASHBOARD COUNTS
========================== */

// Total Supplies
$supplies = @mysqli_query($conn, "SELECT COUNT(*) AS total FROM supplies");
$supplies = $supplies ? mysqli_fetch_assoc($supplies) : ['total' => 0];

// Total Reservations
$reservations = @mysqli_query($conn, "SELECT COUNT(*) AS total FROM reservations");
$reservations = $reservations ? mysqli_fetch_assoc($reservations) : ['total' => 0];

// Today's Sales
$sales = @mysqli_query($conn, "
SELECT IFNULL(SUM(total_sales),0) AS total
FROM reports
WHERE report_date = CURDATE()
");
$sales = $sales ? mysqli_fetch_assoc($sales) : ['total' => 0];

/* ==========================
   WEEKLY SALES
========================== */

$days = ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
$data = [];

for($i=6;$i>=0;$i--)
{
    $date = date("Y-m-d", strtotime("-$i days"));

    $query = @mysqli_query($conn, "
    SELECT IFNULL(SUM(total_sales),0) AS total
    FROM reports
    WHERE report_date='$date'
    ");

    $row = $query ? mysqli_fetch_assoc($query) : null;

    $data[] = $row['total'];
}

/* ==========================
   UPCOMING BOOKINGS
========================== */

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

$upcoming = @mysqli_query($conn, "
SELECT *
FROM reservations
WHERE $dateColumn >= CURDATE()
ORDER BY $dateColumn ASC
LIMIT 5
");

/* ==========================
   RECENT BOOKINGS
========================== */

$recentOrder = 'reservation_date DESC';
if (in_array('id', $columns, true)) {
    $recentOrder = 'id DESC';
}

$recent = @mysqli_query($conn, "
SELECT *
FROM reservations
ORDER BY $recentOrder
LIMIT 5
");

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
    background:#f3f3f3;
}

.dashboard-card{
    border:none;
    border-radius:12px;
    box-shadow:0 0 10px rgba(0,0,0,.08);
}

.card-header{
    font-weight:bold;
    background:white;
}

.table thead{
    background:#4F46E5;
    color:white;
}

.value{
    font-size:30px;
    font-weight:bold;
    color:#4F46E5;
}

</style>

</head>

<body>

<div class="container mt-4">

<h3 class="mb-4">
Admin Dashboard
</h3>

<div class="row">

<div class="col-md-4">

<div class="card dashboard-card">

<div class="card-body text-center">

<h6>Total Supplies</h6>

<div class="value">
<?= $supplies['total']; ?>
</div>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card dashboard-card">

<div class="card-body text-center">

<h6>Total Reservations</h6>

<div class="value">
<?= $reservations['total']; ?>
</div>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card dashboard-card">

<div class="card-body text-center">

<h6>Today's Sales</h6>

<div class="value">
₱<?= number_format($sales['total'],2); ?>
</div>

</div>

</div>

</div>

</div>

<br>

<div class="card dashboard-card">

<div class="card-header">

Weekly Sales

</div>

<div class="card-body">

<canvas id="weeklyChart" height="100"></canvas>

</div>

</div>

<br>

<div class="card dashboard-card">

<div class="card-header">

Upcoming Bookings

</div>

<div class="card-body">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>Name</th>

<th>Date</th>

<th>Revenue</th>

<th>Court</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($upcoming))
{

?>

<tr>

<td><?= htmlspecialchars($row['name']) ?></td>

<td><?= $row['date'] ?></td>

<td>₱<?= number_format($row['revenue'],2) ?></td>

<td><?= $row['court_id'] ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<br>

<div class="card dashboard-card">

<div class="card-header">

Recent Bookings

</div>

<div class="card-body">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>Name</th>

<th>Date</th>

<th>Revenue</th>

<th>Court</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($recent))
{

?>

<tr>

<td><?= htmlspecialchars($row['name']) ?></td>

<td><?= $row['date'] ?></td>

<td>₱<?= number_format($row['revenue'],2) ?></td>

<td><?= $row['court_id'] ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<br>

<div class="text-center">

<a href="index.php" class="btn btn-dark">
Back to Home Page
</a>

</div>

</div>

<script>

const ctx=document.getElementById('weeklyChart');

new Chart(ctx,{

type:'bar',

data:{

labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],

datasets:[{

label:'Sales',

data:[
<?= implode(",",$data); ?>
],

borderWidth:1

}]

},

options:{

responsive:true,

plugins:{
legend:{
display:false
}
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