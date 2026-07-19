<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['admin'])) {
	header("Location: login.php");
	exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="UTF-8">

	<meta name="viewport"
		  content="width=device-width, initial-scale=1.0">

	<title>Court 7 ADMIN</title>

	<style>

		*{
			margin:0;
			padding:0;
			box-sizing:border-box;
			font-family:Arial,sans-serif;
		}

		body{
			background:
				url('images/yeah.png') center/cover no-repeat;
			min-height:100vh;
		}

		.header{
			width:100%;
			background:white;
			display:flex;
			align-items:center;
			justify-content:space-between;
			padding:18px 40px;
			box-shadow:0 2px 10px rgba(0,0,0,0.08);
			position:sticky;
			top:0;
			z-index:1000;
		}

		.title{
			font-size:28px;
			font-weight:700;
			color:#111;
		}

		/* NAVBAR */

		.navbar{
			display:flex;
			align-items:center;
			gap:14px;
			flex-wrap:wrap;
		}

		.nav-link,
		.dropbtn{
			text-decoration:none;
			color:#333;
			font-size:15px;
			border:none;
			background:none;
			cursor:pointer;
			padding:12px 18px;
			border-radius:12px;
			transition:all 0.3s ease;
			font-weight:500;
		}

		/* ACTIVE */

		.active{
			background:#000;
			color:white;
			box-shadow:0 4px 12px rgba(0,0,0,0.15);
		}

		/* HOVER */

		.nav-link:hover,
		.dropbtn:hover{
			background:#000;
			color:white;
		}

		/* DROPDOWN */

		.dropdown{
			position:relative;
		}

		.dropdown-content{
			display:none;
			position:absolute;
			top:58px;
			left:0;
			background:white;
			min-width:260px;
			border-radius:14px;
			overflow:hidden;
			box-shadow:0 10px 25px rgba(0,0,0,0.15);
			z-index:999;
			animation:fadeIn 0.2s ease;
		}

		.dropdown-content a{
			display:block;
			padding:15px 18px;
			text-decoration:none;
			color:#222;
			font-size:14px;
			font-weight:500;
			transition:0.3s;
		}

		.dropdown-content a:hover{
			background:#f5f5f5;
			padding-left:24px;
		}

		.show{
			display:block;
		}

		/* MAIN SECTION */

		.main-bg{
			width:100%;
			height:calc(100vh - 85px);
			background:
			linear-gradient(
				rgba(0,0,0,0.45),
				rgba(0,0,0,0.45)
			),
			url('court7.jpg')
			center/cover no-repeat;

			display:flex;
			align-items:center;
			justify-content:center;
			padding:40px;
		}

		/* HERO CARD */

		.hero-card{
			background:rgba(255,255,255,0.95);
			padding:50px;
			border-radius:24px;
			max-width:700px;
			text-align:center;
			box-shadow:0 15px 35px rgba(0,0,0,0.2);
			backdrop-filter:blur(8px);
			animation:fadeUp 0.5s ease;
		}

		.hero-card h1{
			font-size:48px;
			color:#111;
			margin-bottom:20px;
		}

		.hero-card p{
			font-size:18px;
			color:#555;
			line-height:1.7;
			margin-bottom:30px;
		}

		.hero-btn{
			display:inline-block;
			text-decoration:none;
			background:#000;
			color:white;
			padding:14px 28px;
			border-radius:12px;
			font-size:16px;
			font-weight:bold;
			transition:0.3s;
		}

		.hero-btn:hover{
			background:#222;
			transform:translateY(-2px);
		}


		@keyframes fadeIn{

			from{
				opacity:0;
				transform:translateY(-10px);
			}

			to{
				opacity:1;
				transform:translateY(0);
			}

		}

		@keyframes fadeUp{

			from{
				opacity:0;
				transform:translateY(30px);
			}

			to{
				opacity:1;
				transform:translateY(0);
			}

		}

		/* MOBILE */

		@media(max-width:768px){

			.header{
				flex-direction:column;
				align-items:flex-start;
				gap:15px;
				padding:20px;
			}

			.navbar{
				width:100%;
				gap:10px;
			}

			.nav-link,
			.dropbtn{
				width:100%;
				text-align:left;
			}

			.dropdown-content{
				position:static;
				width:100%;
				margin-top:8px;
			}

			.hero-card{
				padding:35px 25px;
			}

			.hero-card h1{
				font-size:34px;
			}

			.hero-card p{
				font-size:16px;
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

			<div class="dropdown">

				<button class="dropbtn active"
						id="homeBtn">

					Home Page ▼

				</button>

				<div class="dropdown-content"
					 id="homeDropdown">

					<a href="admin_dashboard.php">
						Dashboard
					</a>

					<a href="products.php">
						Products
					</a>

					<a href="sales.php">
						Sales
					</a>

					<a href="weekly_sales.php">
						Weekly Sales
					</a>

					<a href="upcoming_bookings.php">
						Upcoming Bookings
					</a>

					<a href="recent_bookings.php">
						Recent Bookings
					</a>

				</div>

			</div>

			<a href="manage_reservation.php"
			   class="nav-link">

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


			<div class="dropdown">

				<button class="dropbtn"
						id="reportBtn">

					Reports ▼

				</button>

				<div class="dropdown-content"
					 id="reportDropdown">

					<a href="start_end_date.php">
						Start End Date
					</a>

					<a href="total_players.php">
						Total Players (By Session)
					</a>

					<a href="court_utilization.php">
						Court Utilization
					</a>

					<a href="downpayments.php">
						Downpayments
					</a>

				</div>

			</div>

			<!-- LOGOUT -->

			<a href="logout.php"
			   class="nav-link">

				Logout

			</a>

		</nav>

	</div>

		</div>

	</div>

	<!-- SCRIPT -->

	<script>

		/* HOME DROPDOWN */

		const homeBtn =
		document.getElementById(
			'homeBtn'
		);

		const homeDropdown =
		document.getElementById(
			'homeDropdown'
		);

		/* REPORT DROPDOWN */

		const reportBtn =
		document.getElementById(
			'reportBtn'
		);

		const reportDropdown =
		document.getElementById(
			'reportDropdown'
		);

		/* HOME CLICK */

		homeBtn.addEventListener(
		'click',

		function(e){

			e.stopPropagation();

			homeDropdown.classList.toggle(
				'show'
			);

			reportDropdown.classList.remove(
				'show'
			);

		});

		/* REPORT CLICK */

		reportBtn.addEventListener(
		'click',

		function(e){

			e.stopPropagation();

			reportDropdown.classList.toggle(
				'show'
			);

			homeDropdown.classList.remove(
				'show'
			);

		});

		/* CLOSE DROPDOWNS */

		window.addEventListener(
		'click',

		function(){

			homeDropdown.classList.remove(
				'show'
			);

			reportDropdown.classList.remove(
				'show'
			);

		});

	</script>

</body>
</html>