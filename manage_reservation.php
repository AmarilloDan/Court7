<?php
require_once 'db_connect.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="UTF-8">

	<meta name="viewport"
		  content="width=device-width, initial-scale=1.0">

	<title>Manage Reservation</title>

	<style>

		*{
			margin:0;
			padding:0;
			box-sizing:border-box;
			font-family:Arial, sans-serif;
		}

		body{
			background:#f4f6f9;
		}

		/* HEADER */

		.header{
			width:100%;
			background:white;
			padding:15px 30px;
			display:flex;
			align-items:center;
			justify-content:space-between;
			box-shadow:0 2px 10px rgba(0,0,0,0.1);
			position:sticky;
			top:0;
			z-index:1000;
		}

		.title{
			font-size:24px;
			font-weight:bold;
			color:#222;
		}

		.navbar{
			display:flex;
			gap:15px;
			flex-wrap:wrap;
		}

		.nav-link{
			text-decoration:none;
			color:#444;
			font-size:15px;
			padding:10px 15px;
			border-radius:8px;
			transition:0.3s;
		}

		.nav-link:hover{
			background:#111;
			color:white;
		}

		.active{
			background:#111;
			color:white;
		}

		/* MAIN */

		.container{
			padding:30px;
		}

		.page-title{
			font-size:32px;
			font-weight:bold;
			color:#222;
			margin-bottom:25px;
		}

		/* CARDS */

		.card{
			background:white;
			padding:25px;
			border-radius:15px;
			box-shadow:0 4px 15px rgba(0,0,0,0.08);
			margin-bottom:25px;
		}

		/* BUTTONS */

		.button-group{
			display:flex;
			flex-wrap:wrap;
			gap:15px;
			margin-bottom:20px;
		}

		.btn{
			border:none;
			padding:12px 18px;
			border-radius:10px;
			cursor:pointer;
			font-size:14px;
			font-weight:bold;
			transition:0.3s;
		}

		.btn-primary{
			background:#111;
			color:white;
		}

		.btn-primary:hover{
			background:#333;
		}

		.btn-success{
			background:#28a745;
			color:white;
		}

		.btn-success:hover{
			background:#218838;
		}

		.btn-warning{
			background:#ff9800;
			color:white;
		}

		.btn-warning:hover{
			background:#e68900;
		}

		/* FILTERS */

		.filters{
			display:grid;
			grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
			gap:20px;
			margin-top:20px;
		}

		.filter-box label{
			display:block;
			margin-bottom:8px;
			font-weight:bold;
			color:#333;
		}

		input,
		select{
			width:100%;
			padding:12px;
			border:1px solid #ccc;
			border-radius:10px;
			outline:none;
			font-size:14px;
		}

		input:focus,
		select:focus{
			border-color:#111;
		}

		.revenue-display{
			margin-top:10px;
			font-size:18px;
			font-weight:bold;
			color:#28a745;
		}

		/* TABLE */

		.table-container{
			overflow-x:auto;
		}

		table{
			width:100%;
			border-collapse:collapse;
			margin-top:20px;
			background:white;
			border-radius:10px;
			overflow:hidden;
		}

		thead{
			background:#111;
			color:white;
		}

		th,
		td{
			padding:15px;
			text-align:left;
			border-bottom:1px solid #eee;
		}

		tbody tr:hover{
			background:#f9f9f9;
		}

		.action-btn{
			padding:8px 12px;
			border:none;
			border-radius:8px;
			cursor:pointer;
			font-size:13px;
			font-weight:bold;
			margin-right:5px;
		}

		.edit-btn{
			background:#2196f3;
			color:white;
		}

		.delete-btn{
			background:#f44336;
			color:white;
		}

		.edit-btn:hover{
			background:#1976d2;
		}

		.delete-btn:hover{
			background:#d32f2f;
		}

		/* MODAL */

		.modal{
			display:none;
			position:fixed;
			z-index:9999;
			left:0;
			top:0;
			width:100%;
			height:100%;
			background:rgba(0,0,0,0.5);
			justify-content:center;
			align-items:center;
			padding:20px;
		}

		.modal-content{
			background:white;
			width:100%;
			max-width:500px;
			border-radius:15px;
			padding:25px;
			animation:fadeIn 0.3s ease;
		}

		.modal-header{
			display:flex;
			justify-content:space-between;
			align-items:center;
			margin-bottom:20px;
		}

		.close-btn{
			font-size:28px;
			cursor:pointer;
			font-weight:bold;
		}

		.form-group{
			margin-bottom:15px;
		}

		.form-group label{
			display:block;
			margin-bottom:8px;
			font-weight:bold;
			color:#333;
		}

		@keyframes fadeIn{

			from{
				transform:translateY(-20px);
				opacity:0;
			}

			to{
				transform:translateY(0);
				opacity:1;
			}

		}

		/* MOBILE */

		@media(max-width:768px){

			.header{
				flex-direction:column;
				align-items:flex-start;
				gap:15px;
			}

			.navbar{
				width:100%;
			}

			.page-title{
				font-size:24px;
			}

		}

	</style>

</head>

<body>

	<div class="header">

		<div class="title">
			Court 7 ADMIN
		</div>

		<nav class="navbar">
			<a href="index.php" class="nav-link">
				Home Page
			</a>

			<a href="manage_reservation.php"
			   class="nav-link active">
				Manage Reservation
			</a>

			<a href="supplies.php"
			   class="nav-link">
				Supplies
			</a>

			<a href="debts.php"
			   class="nav-link">
				Debts
			</a>

			<a href="court_management.php"
			   class="nav-link">
				Court Management
			</a>

			<a href="reports.php"
			   class="nav-link">
				Reports
			</a>

			<a href="logout.php"
			   class="nav-link">
				Logout
			</a>
		</nav>

	</div>

	<!-- MAIN -->

	<div class="container">

		<h1 class="page-title">
			Manage Reservations
		</h1>

		<div class="card">

			<div class="button-group">

				<button class="btn btn-primary"
						id="add-reservation">

					+ Add Reservation

				</button>

				<button class="btn btn-success">
					Print Bookings
				</button>

				<button class="btn btn-warning">
					Sort by Name
				</button>

			</div>

			<div class="filters">

				<div class="filter-box">

					<label>
						Revenue for Date
					</label>

					<input type="date">

					<button class="btn btn-primary"
							style="margin-top:10px;width:100%;">

						Show Revenue

					</button>

					<div class="revenue-display">
						₱0.00
					</div>

				</div>

				<div class="filter-box">

					<label>
						Filter by Category
					</label>

					<select>

						<option value="">
							All Categories
						</option>

						<option>
							Badminton Court 1
						</option>

						<option>
							Badminton Court 2
						</option>

						<option>
							Badminton Court 3
						</option>

						<option>
							Badminton Court 4
						</option>

					</select>

				</div>

			</div>

		</div>

		<!-- TABLE -->

		<div class="card table-container">

			<table id="reservation-table">

				<thead>

					<tr>

						<th>Name</th>
						<th>Date</th>
						<th>Category</th>
						<th>Revenue</th>
						<th>Actions</th>

					</tr>

				</thead>

				<tbody></tbody>

			</table>

		</div>

	</div>


	<div class="modal"
		 id="reservationModal">

		<div class="modal-content">

			<div class="modal-header">

				<h2>Add Reservation</h2>

				<span class="close-btn"
					  id="closeModal">

					&times;

				</span>

			</div>

			<form id="reservationForm">

				<div class="form-group">

					<label>
						Customer Name
					</label>

					<input type="text"
						   id="customer-name"
						   required>

				</div>

				<div class="form-group">

					<label>
						Date
					</label>

					<input type="date"
						   id="reservation-date"
						   required>

				</div>

				<div class="form-group">

					<label>
						Court Category
					</label>

					<select id="reservation-category"
							required>

						<option value="">
							Select Court
						</option>

						<option value="Badminton Court 1">
							Badminton Court 1
						</option>

						<option value="Badminton Court 2">
							Badminton Court 2
						</option>

						<option value="Badminton Court 3">
							Badminton Court 3
						</option>

						<option value="Badminton Court 4">
							Badminton Court 4
						</option>

					</select>

				</div>

				<div class="form-group">

					<label>
						Revenue
					</label>

					<input type="number"
						   id="reservation-revenue"
						   required>

				</div>

				<button type="submit"
						class="btn btn-primary"
						style="width:100%;">

					Save Reservation

				</button>

			</form>

		</div>

	</div>

	<!-- EDIT MODAL -->

	<div class="modal"
		 id="editModal">

		<div class="modal-content">

			<div class="modal-header">

				<h2>Edit Reservation</h2>

				<span class="close-btn"
					  id="closeEditModal">

					&times;

				</span>

			</div>

			<form id="editReservationForm">

				<div class="form-group">

					<label>
						Customer Name
					</label>

					<input type="text"
						   id="edit-name"
						   required>

				</div>

				<div class="form-group">

					<label>
						Date
					</label>

					<input type="date"
						   id="edit-date"
						   required>

				</div>

				<div class="form-group">

					<label>
						Court Category
					</label>

					<select id="edit-category"
							required>

						<option value="Badminton Court 1">
							Badminton Court 1
						</option>

						<option value="Badminton Court 2">
							Badminton Court 2
						</option>

						<option value="Badminton Court 3">
							Badminton Court 3
						</option>

						<option value="Badminton Court 4">
							Badminton Court 4
						</option>

					</select>

				</div>

				<div class="form-group">

					<label>
						Revenue
					</label>

					<input type="number"
						   id="edit-revenue"
						   required>

				</div>

				<button type="submit"
						class="btn btn-success"
						style="width:100%;">

					Update Reservation

				</button>

			</form>

		</div>

	</div>

	<script>

		const addBtn =
		document.getElementById('add-reservation');

		const reservationModal =
		document.getElementById('reservationModal');

		const closeModal =
		document.getElementById('closeModal');

		const reservationForm =
		document.getElementById('reservationForm');

		const reservationTable =
		document.querySelector(
			'#reservation-table tbody'
		);

		const editModal =
		document.getElementById('editModal');

		const closeEditModal =
		document.getElementById('closeEditModal');

		const editReservationForm =
		document.getElementById(
			'editReservationForm'
		);

		let currentRow = null;


		addBtn.onclick = () => {

			reservationModal.style.display = 'flex';

		}

		closeModal.onclick = () => {

			reservationModal.style.display = 'none';

		}

		closeEditModal.onclick = () => {

			editModal.style.display = 'none';

		}

		window.onclick = (e) => {

			if(e.target == reservationModal){

				reservationModal.style.display =
				'none';

			}

			if(e.target == editModal){

				editModal.style.display =
				'none';

			}

		}

		reservationForm.addEventListener(
		'submit',

		function(e){

			e.preventDefault();

			const name =
			document.getElementById(
				'customer-name'
			).value;

			const date =
			document.getElementById(
				'reservation-date'
			).value;

			const category =
			document.getElementById(
				'reservation-category'
			).value;

			const revenue =
			document.getElementById(
				'reservation-revenue'
			).value;

			const row =
			reservationTable.insertRow();

			row.innerHTML = `

				<td>${name}</td>

				<td>${date}</td>

				<td>${category}</td>

				<td>₱${parseFloat(revenue).toFixed(2)}</td>

				<td>

					<button class="action-btn edit-btn">
						Edit
					</button>

					<button class="action-btn delete-btn">
						Delete
					</button>

				</td>

			`;

			reservationForm.reset();

			reservationModal.style.display =
			'none';

		});

		/* TABLE ACTIONS */

		reservationTable.addEventListener(
		'click',

		function(e){

			const row =
			e.target.closest('tr');

			/* DELETE */

			if(
				e.target.classList.contains(
					'delete-btn'
				)
			){

				if(confirm('Delete reservation?')){

					row.remove();

				}

			}

			/* EDIT */

			if(
				e.target.classList.contains(
					'edit-btn'
				)
			){

				currentRow = row;

				const cells =
				row.querySelectorAll('td');

				document.getElementById(
					'edit-name'
				).value = cells[0].textContent;

				document.getElementById(
					'edit-date'
				).value = cells[1].textContent;

				document.getElementById(
					'edit-category'
				).value = cells[2].textContent;

				document.getElementById(
					'edit-revenue'
				).value =
				cells[3].textContent.replace(
					'₱',
					''
				);

				editModal.style.display = 'flex';

			}

		});

		/* UPDATE RESERVATION */

		editReservationForm.addEventListener(
		'submit',

		function(e){

			e.preventDefault();

			const cells =
			currentRow.querySelectorAll('td');

			cells[0].textContent =
			document.getElementById(
				'edit-name'
			).value;

			cells[1].textContent =
			document.getElementById(
				'edit-date'
			).value;

			cells[2].textContent =
			document.getElementById(
				'edit-category'
			).value;

			cells[3].textContent =
			'₱' +
			parseFloat(
				document.getElementById(
					'edit-revenue'
				).value
			).toFixed(2);

			editModal.style.display = 'none';

		});

	</script>

</body>
</html>