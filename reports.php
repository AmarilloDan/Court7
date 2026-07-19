<?php
require_once 'db_connect.php';

$startDate = isset($_GET['start_date']) && $_GET['start_date'] !== '' ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) && $_GET['end_date'] !== '' ? $_GET['end_date'] : date('Y-m-d');
$startDate = date('Y-m-d', strtotime($startDate));
$endDate = date('Y-m-d', strtotime($endDate));
$daysInRange = max(1, ((strtotime($endDate) - strtotime($startDate)) / 86400) + 1);

$tables = ['reservations', 'users', 'payments', 'courts'];
$missingTables = [];
foreach ($tables as $table) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows === 0) {
        $missingTables[] = $table;
    }
}

$total_revenue = 0;
$total_players = 0;
$total_down_payments = 0;
$total_down_payments_count = 0;
$court_utilization = 0;
$daily_revenues = [];
$player_rows = [];
$court_rows = [];
$payment_rows = [];

$reservationDateColumn = 'reservation_date';
$revenueColumn = 'revenue';
$courtColumn = 'court_id';

if (!in_array('reservations', $missingTables, true)) {
    $reservationColumnsResult = $conn->query('SHOW COLUMNS FROM reservations');
    $reservationColumns = [];
    if ($reservationColumnsResult) {
        while ($column = $reservationColumnsResult->fetch_assoc()) {
            $reservationColumns[] = $column['Field'];
        }
    }

    if (!in_array('reservation_date', $reservationColumns, true) && in_array('date', $reservationColumns, true)) {
        $reservationDateColumn = 'date';
    }

    if (!in_array('revenue', $reservationColumns, true)) {
        $revenueColumn = '';
    }

    if (!in_array('court_id', $reservationColumns, true) && in_array('court', $reservationColumns, true)) {
        $courtColumn = 'court';
    }

    $whereClause = "WHERE $reservationDateColumn BETWEEN '$startDate' AND '$endDate'";
    $reservationQuery = $conn->query("SELECT * FROM reservations $whereClause ORDER BY $reservationDateColumn DESC, id DESC");
    $reservationRows = [];
    while ($row = $reservationQuery->fetch_assoc()) {
        $reservationRows[] = $row;
    }

    $revenueValue = 0;
    if ($revenueColumn !== '') {
        $revenueQuery = $conn->query("SELECT COALESCE(SUM($revenueColumn), 0) AS total_revenue FROM reservations $whereClause");
        $revenueData = $revenueQuery ? $revenueQuery->fetch_assoc() : null;
        $revenueValue = (float) ($revenueData['total_revenue'] ?? 0);
    }
    $total_revenue = $revenueValue;

    $dailyQuery = $conn->query("SELECT $reservationDateColumn AS booking_date, COALESCE(SUM($revenueColumn), 0) AS daily_total FROM reservations $whereClause GROUP BY $reservationDateColumn ORDER BY $reservationDateColumn ASC");
    while ($row = $dailyQuery->fetch_assoc()) {
        $daily_revenues[] = [
            'date' => $row['booking_date'],
            'daily_total' => (float) ($row['daily_total'] ?? 0)
        ];
    }

    $playerNames = [];
    if (!in_array('users', $missingTables, true)) {
        $userQuery = $conn->query('SELECT id, name FROM users');
        while ($userRow = $userQuery->fetch_assoc()) {
            $playerNames[(int) $userRow['id']] = $userRow['name'];
        }
    }

    $playerMap = [];
    foreach ($reservationRows as $row) {
        $playerKey = null;
        $playerName = '';

        if (!empty($row['user_id'])) {
            $playerKey = 'user:' . (int) $row['user_id'];
            $playerName = $playerNames[(int) $row['user_id']] ?? ('User #' . (int) $row['user_id']);
        } else {
            $playerName = trim((string) ($row['name'] ?? ''));
            if ($playerName === '') {
                continue;
            }
            $playerKey = 'name:' . strtolower($playerName);
        }

        if (!isset($playerMap[$playerKey])) {
            $playerMap[$playerKey] = [
                'name' => $playerName,
                'sessions' => 0,
                'last_booking' => null
            ];
        }

        $playerMap[$playerKey]['sessions'] += 1;
        $playerMap[$playerKey]['last_booking'] = $row[$reservationDateColumn] ?? $playerMap[$playerKey]['last_booking'];
    }

    $player_rows = array_values($playerMap);
    usort($player_rows, function ($a, $b) {
        return [$b['sessions'], strtolower($a['name'])] <=> [$a['sessions'], strtolower($b['name'])];
    });
    $total_players = count($player_rows);

    $courtTotal = 0;
    if (!in_array('courts', $missingTables, true)) {
        $courtCountQuery = $conn->query('SELECT COUNT(*) AS total_courts FROM courts');
        $courtCountData = $courtCountQuery ? $courtCountQuery->fetch_assoc() : null;
        $courtTotal = (int) ($courtCountData['total_courts'] ?? 0);
    }

    $courtTotal = max(1, $courtTotal);
    $courtUtilization = 0;
    if ($courtColumn === 'court_id') {
        $courtQuery = $conn->query("SELECT r.court_id AS court_id, COALESCE(c.name, CONCAT('Court ', r.court_id)) AS court_name, COUNT(*) AS bookings FROM reservations r LEFT JOIN courts c ON c.id = r.court_id $whereClause GROUP BY r.court_id, c.name ORDER BY bookings DESC");
    } else {
        $courtQuery = $conn->query("SELECT r.$courtColumn AS court_key, COUNT(*) AS bookings FROM reservations r $whereClause GROUP BY r.$courtColumn ORDER BY bookings DESC");
    }

    while ($courtRow = $courtQuery->fetch_assoc()) {
        $courtName = $courtRow['court_name'] ?? ($courtRow['court_key'] ?? 'Unknown');
        $bookings = (int) ($courtRow['bookings'] ?? 0);
        $utilization = $daysInRange > 0 ? round(($bookings / $daysInRange) * 100, 1) : 0;
        $court_rows[] = [
            'name' => $courtName,
            'bookings' => $bookings,
            'utilization' => $utilization
        ];
    }

    $court_utilization = $daysInRange > 0 && count($reservationRows) > 0 ? round((count($reservationRows) / ($daysInRange * $courtTotal)) * 100, 1) : 0;
}

if (!in_array('payments', $missingTables, true)) {
    $paymentQuery = $conn->query("SELECT id, amount, method, reference, status, created_at FROM payments WHERE DATE(created_at) BETWEEN '$startDate' AND '$endDate' ORDER BY created_at DESC");
    while ($paymentRow = $paymentQuery->fetch_assoc()) {
        $payment_rows[] = [
            'amount' => (float) ($paymentRow['amount'] ?? 0),
            'method' => $paymentRow['method'] ?? 'N/A',
            'reference' => $paymentRow['reference'] ?? '-',
            'status' => $paymentRow['status'] ?? 'pending',
            'created_at' => $paymentRow['created_at'] ?? '-'
        ];
        $total_down_payments += (float) ($paymentRow['amount'] ?? 0);
    }
    $total_down_payments_count = count($payment_rows);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="UTF-8">

	<meta name="viewport"
		  content="width=device-width, initial-scale=1.0">

	<title>Reports</title>

	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
			justify-content:space-between;
			align-items:center;
			box-shadow:0 2px 10px rgba(0,0,0,0.08);
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
			padding:10px 15px;
			border-radius:8px;
			transition:0.3s;
			font-size:14px;
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
			margin-bottom:25px;
			color:#222;
		}

		/* CARDS */

		.summary-grid{
			display:grid;
			grid-template-columns:
			repeat(auto-fit,minmax(250px,1fr));
			gap:20px;
			margin-bottom:30px;
		}

		.card{
			background:white;
			padding:25px;
			border-radius:15px;
			box-shadow:0 4px 15px rgba(0,0,0,0.08);
		}

		.card-title{
			font-size:16px;
			color:#777;
			margin-bottom:10px;
		}

		.card-value{
			font-size:32px;
			font-weight:bold;
			color:#111;
		}

		.revenue{
			color:#28a745;
		}

		.debt{
			color:#f44336;
		}

		/* CHART */

		.chart-container{
			margin-top:20px;
		}

		canvas{
			width:100% !important;
			max-height:400px;
		}

		/* TABLE */

		.table-container{
			overflow-x:auto;
			margin-top:30px;
		}

		table{
			width:100%;
			border-collapse:collapse;
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

		.amount{
			color:#f44336;
			font-weight:bold;
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

			<a href="index.php"
			   class="nav-link">
				Dashboard
			</a>

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

			<a href="reports.php"
			   class="nav-link active">
				Reports
			</a>

			<a href="logout.php"
			   class="nav-link">
				Logout
			</a>

		</nav>

	</div>


	<div class="container">

		<h1 class="page-title">
			Reports Dashboard
		</h1>


		<form method="get" class="card" style="margin-bottom:20px;">
			<div style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
				<div>
					<label for="start_date">Start Date</label>
					<input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" class="form-control">
				</div>
				<div>
					<label for="end_date">End Date</label>
					<input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" class="form-control">
				</div>
				<div>
					<button type="submit" class="btn" style="background:#111;color:white;padding:10px 16px;border:none;border-radius:8px;cursor:pointer;">Apply</button>
				</div>
			</div>
		</form>

		<div class="summary-grid">

			<div class="card">
				<div class="card-title">Total Players (By Session)</div>
				<div class="card-value"><?php echo number_format($total_players); ?></div>
			</div>

			<div class="card">
				<div class="card-title">Total Revenue</div>
				<div class="card-value revenue">₱<?php echo number_format($total_revenue, 2); ?></div>
			</div>

			<div class="card">
				<div class="card-title">Down Payments</div>
				<div class="card-value debt">₱<?php echo number_format($total_down_payments, 2); ?></div>
			</div>

			<div class="card">
				<div class="card-title">Court Utilization</div>
				<div class="card-value"><?php echo number_format($court_utilization, 1); ?>%</div>
			</div>

		</div>

		<!-- DAILY REVENUE CHART -->

		<div class="card">

			<h2>
				Daily Revenue
			</h2>
			<p style="color:#666; margin-bottom:12px;">
				Showing activity from <?php echo htmlspecialchars($startDate); ?> to <?php echo htmlspecialchars($endDate); ?>.
			</p>

			<div class="chart-container">

				<canvas id="reportChart"></canvas>

			</div>

		</div>

		<div class="card table-container">
			<h2 style="margin-bottom:20px;">Player Sessions</h2>
			<table>
				<thead>
					<tr><th>Player</th><th>Sessions</th><th>Last Booking</th></tr>
				</thead>
				<tbody>
					<?php foreach ($player_rows as $player) { ?>
					<tr>
						<td><?php echo htmlspecialchars($player['name'] ?? 'Unknown'); ?></td>
						<td><?php echo (int) $player['sessions']; ?></td>
						<td><?php echo htmlspecialchars($player['last_booking'] ?? '-'); ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>

		<div class="card table-container">
			<h2 style="margin-bottom:20px;">Court Utilization</h2>
			<table>
				<thead>
					<tr><th>Court</th><th>Bookings</th><th>Utilization</th></tr>
				</thead>
				<tbody>
					<?php foreach ($court_rows as $court) { ?>
					<tr>
						<td><?php echo htmlspecialchars($court['name'] ?? '-'); ?></td>
						<td><?php echo (int) $court['bookings']; ?></td>
						<td><?php echo number_format($court['utilization'], 1); ?>%</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>

		<div class="card table-container">
			<h2 style="margin-bottom:20px;">Down Payments</h2>
			<table>
				<thead>
					<tr><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Date</th></tr>
				</thead>
				<tbody>
					<?php foreach ($payment_rows as $payment) { ?>
					<tr>
						<td>₱<?php echo number_format($payment['amount'], 2); ?></td>
						<td><?php echo htmlspecialchars($payment['method']); ?></td>
						<td><?php echo htmlspecialchars($payment['reference']); ?></td>
						<td><?php echo htmlspecialchars($payment['status']); ?></td>
						<td><?php echo htmlspecialchars($payment['created_at']); ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>

	</div>

	<script>

		const ctx =
		document.getElementById('reportChart');

		const reportChart =
		new Chart(ctx, {

			type: 'bar',

			data: {

				labels: [

					<?php
					foreach($daily_revenues as $day){

						echo "'" .
						$day['date'] .
						"',";

					}
					?>

				],

				datasets: [{

					label: 'Daily Revenue',

					data: [

						<?php
						foreach($daily_revenues as $day){

							echo $day['daily_total']
							. ",";

						}
						?>

					],

					borderWidth: 1

				}]

			},

			options: {

				responsive: true,

				scales: {

					y: {

						beginAtZero: true

					}

				}

			}

		});

	</script>

</body>
</html>