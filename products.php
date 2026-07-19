<?php
session_start();
require_once 'db_connect.php';

$filename = basename(__FILE__);
mysqli_query($conn, "INSERT INTO file_logs (filename) VALUES ('$filename')");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$supplies = mysqli_query($conn, "
SELECT *
FROM supplies
ORDER BY id DESC
");

$total = mysqli_query($conn, "
SELECT COUNT(*) AS total
FROM supplies
");

$total = mysqli_fetch_assoc($total);
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Products</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f3f3f3;
}

.card{
    border:none;
    border-radius:12px;
    box-shadow:0 0 10px rgba(0,0,0,.08);
}

.card-header{
    background:white;
    font-weight:bold;
}

.table thead{
    background:#4F46E5;
    color:white;
}

.badge{
    font-size:14px;
}

</style>

</head>

<body>

<div class="container mt-4">

<h3 class="mb-4">

Products / Supplies

</h3>

<div class="row mb-3">

<div class="col-md-4">

<div class="card">

<div class="card-body text-center">

<h6>Total Supplies</h6>

<h2 class="text-primary">

<?= $total['total']; ?>

</h2>

</div>

</div>

</div>

<div class="col-md-8 text-end">

<input
type="text"
id="search"
class="form-control"
placeholder="Search supply..."
>

</div>

</div>

<div class="card">

<div class="card-header">

Supply Inventory

</div>

<div class="card-body">

<table class="table table-bordered table-hover" id="supplyTable">

<thead>

<tr>

<th>ID</th>

<th>Name</th>

<th>Quantity</th>

<th>Category</th>

<th>Date Added</th>

<th width="180">Action</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($supplies)){ ?>

<tr>

<td><?= $row['id']; ?></td>

<td><?= htmlspecialchars($row['name']); ?></td>

<td>

<?php

if($row['quantity'] <= 5){

echo "<span class='badge bg-danger'>{$row['quantity']}</span>";

}elseif($row['quantity'] <=15){

echo "<span class='badge bg-warning text-dark'>{$row['quantity']}</span>";

}else{

echo "<span class='badge bg-success'>{$row['quantity']}</span>";

}

?>

</td>

<td><?= htmlspecialchars($row['category']); ?></td>

<td><?= $row['date_added']; ?></td>

<td>

<a href="#" class="btn btn-sm btn-primary">

Edit

</a>

<a href="#" class="btn btn-sm btn-danger">

Delete

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<div class="mt-3">

<a href="index.php" class="btn btn-dark">

Back to Home

</a>

</div>

</div>

<script>

const search=document.getElementById("search");

search.addEventListener("keyup",function(){

let filter=this.value.toLowerCase();

let rows=document.querySelectorAll("#supplyTable tbody tr");

rows.forEach(function(row){

let text=row.innerText.toLowerCase();

row.style.display=text.includes(filter)?"":"none";

});

});

</script>

</body>
</html>