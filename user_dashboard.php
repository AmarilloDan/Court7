<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$conn = db();

$user_id = current_user_id();
$usersFile = __DIR__ . '/uploads/users.json';

function load_users_file($path) {
    $data = @file_get_contents($path);
    if ($data === false || trim($data) === '') {
        return [];
    }

    $decoded = json_decode($data, true);
    return is_array($decoded) ? $decoded : [];
}

function find_user_by_id($users, $id) {
    foreach ($users as $userEntry) {
        if (($userEntry['id'] ?? null) == $id) {
            return $userEntry;
        }
    }

    return null;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {

    $reservation_id = (int) $_POST['reservation_id'];
    $amount = (float) $_POST['amount'];
    $method = mysqli_real_escape_string($conn, $_POST['method']);
    $reference = mysqli_real_escape_string($conn, $_POST['reference'] ?? '');

    $proofPath = '';

    if (!empty($_FILES['proof']['name'])) {
        $uploadDir = "uploads/payments/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES['proof']['name']);
        $target = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['proof']['tmp_name'], $target)) {
            $proofPath = $target;
        }
    }

    mysqli_query($conn, "
        INSERT INTO payments (reservation_id, user_id, amount, method, reference, proof, status)
        VALUES ($reservation_id, $user_id, $amount, '$method', '$reference', '$proofPath', 'pending')
    ");
}


$user = [];
if ($conn) {
    $user_q = mysqli_query($conn, "
        SELECT id, name, email, address, contact_number, profile_photo, created_at
        FROM users
        WHERE id = $user_id
    ");
    $user = mysqli_fetch_assoc($user_q) ?: [];
}

if (empty($user) && file_exists($usersFile)) {
    $users = load_users_file($usersFile);
    $fallbackUser = find_user_by_id($users, $user_id);
    if ($fallbackUser) {
        $user = [
            'id' => $fallbackUser['id'],
            'name' => $fallbackUser['name'] ?? '',
            'email' => $fallbackUser['email'] ?? '',
            'address' => $fallbackUser['address'] ?? '',
            'contact_number' => $fallbackUser['contact_number'] ?? '',
            'profile_photo' => $fallbackUser['profile_photo'] ?? '',
            'created_at' => $fallbackUser['created_at'] ?? date('Y-m-d H:i:s'),
        ];
    }
}

$profile_photo = !empty($user['profile_photo']) && file_exists($user['profile_photo'])
    ? $user['profile_photo']
    : 'images/logoo.png';

$total_q = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM reservations
    WHERE user_id = $user_id
");
$total = mysqli_fetch_assoc($total_q)['total'] ?? 0;

$upcomingResult = mysqli_query($conn, "
    SELECT 
        r.id,
        r.reservation_date,
        r.time_slot,
        r.status,
        c.name AS court_name,
        COALESCE(p.status, 'unpaid') AS payment_status
    FROM reservations r
    LEFT JOIN courts c ON r.court = c.id
    LEFT JOIN (
        SELECT reservation_id, status
        FROM payments
        ORDER BY id DESC
    ) p ON p.reservation_id = r.id
    WHERE r.user_id = $user_id
    AND r.reservation_date >= CURDATE()
    ORDER BY r.reservation_date ASC, r.time_slot ASC
    LIMIT 5
");
$upcoming_rows = mysqli_fetch_all($upcomingResult, MYSQLI_ASSOC);
$upcoming_count = count($upcoming_rows);
$next_booking = $upcoming_rows[0] ?? null;

$recentResult = mysqli_query($conn, "
    SELECT r.reservation_date, r.time_slot, r.status, c.name AS court_name
    FROM reservations r
    LEFT JOIN courts c ON r.court = c.id
    WHERE r.user_id = $user_id
    ORDER BY r.reservation_date DESC, r.time_slot DESC
    LIMIT 5
");
$recent_rows = mysqli_fetch_all($recentResult, MYSQLI_ASSOC);

$courtsResult = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM courts
");
$court_count = mysqli_fetch_assoc($courtsResult)['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>User Dashboard</title>
<meta charset="UTF-8">

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,#e8f5e9,#f5f7fa);}
.wrapper{display:flex;}

.sidebar{
    width:260px;
    min-height:100vh;
    background:#fff;
    padding:25px;
    box-shadow:0 0 20px rgba(0,0,0,0.08);
}

.logo{
    text-align:center;
    margin-bottom:20px;
}

.logo img{
    width:150px;        
    height:auto;
    display:inline-block;
    background:none;
    border:none;
    object-fit:contain;
}

.user-box{
    text-align:center;
    padding:20px;
    background:#87CEEB;
    color:#fff;
    border-radius:20px;
    margin-bottom:30px;
}

.user-box img{
    width:110px;
    height:110px;
    border-radius:50%;
    object-fit:cover;
    margin-bottom:10px;
}

.sidebar a{
    display:block;
    padding:15px;
    margin:10px 0;
    text-decoration:none;
    color:#333;
    border-radius:12px;
}

.sidebar a:hover{
    background:#0c63e4;
    color:#fff;
}

.main{flex:1;padding:30px;}

.card{
    background:#fff;
    padding:25px;
    border-radius:20px;
    margin-bottom:25px;
    box-shadow:0 8px 30px rgba(0,0,0,0.08);
}

.grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-bottom:25px;
}

.stat{text-align:center;font-weight:bold;}
.stat .number{font-size:40px;margin-top:10px;}

table{width:100%;border-collapse:collapse;}
th{background:#87CEEB;color:#fff;padding:15px;}
td{padding:12px;text-align:center;border-bottom:1px solid #eee;}

.badge{
    padding:6px 14px;
    border-radius:20px;
    color:#fff;
    font-size:12px;
    font-weight:bold;
}

.pending{background:#ff9800;}
.reserved{background:#03a9f4;}
.completed{background:#4caf50;}

.modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
}

.modal-content{
    position:absolute;
    top:80px;
    right:40px;
    width:350px;
    background:#fff;
    padding:20px;
    border-radius:15px;
}

.notification-modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.35);
    z-index:999;
}

.notification-box{
    position:absolute;
    top:120px;
    right:40px;
    width:360px;
    background:#fff;
    padding:22px;
    border-radius:18px;
    box-shadow:0 20px 50px rgba(0,0,0,0.15);
}

.notification-box h3{
    margin-bottom:15px;
}

.notification-box p{
    margin-bottom:18px;
    line-height:1.6;
    color:#333;
}

.notification-box button{
    background:#0c63e4;
    color:#fff;
    border:none;
    padding:12px 18px;
    border-radius:12px;
    cursor:pointer;
}
</style>
</head>

<body>
<div class="wrapper">

<div class="sidebar">
    <div class="logo">
        <img src="images/yeah.png" alt="Court 7 Logo">
    </div>
    <a href="user_dashboard.php" class="active">🏠 Dashboard</a>
    <a href="profile.php">👤 My Profile</a>
    <a href="booking_calendar.php">📅 Booking Calendar</a>
    <a href="booking_history.php">📊 Booking History</a>
    <a href="logout.php" style="color:#dc2626;">🚪 Logout</a>
</div>

<div class="main">
    <div class="card hero">
        <div>
            <h2>Welcome, <?= htmlspecialchars($user['name'] ?? 'User'); ?> 👋</h2>
            <p>Here is a quick overview of your court bookings and account activity.</p>
        </div>
        <img src="<?= htmlspecialchars($profile_photo); ?>" alt="Profile" class="avatar">
    </div>

    <div class="stats">
        <div class="stat">
            <div class="label">Total Bookings</div>
            <div class="value"><?= (int)$total; ?></div>
        </div>
        <div class="stat">
            <div class="label">Upcoming</div>
            <div class="value"><?= count($upcoming_rows); ?></div>
        </div>
        <div class="stat">
            <div class="label">Courts Available</div>
            <div class="value"><?= (int)$court_count; ?></div>
        </div>
    </div>

    <?php if ($next_booking): ?>
    <div class="notification-modal" id="upcomingModal">
        <div class="notification-box">
            <h3>Upcoming Booking</h3>
            <p>Your next reservation is for <strong><?= htmlspecialchars($next_booking['court_name'] ?? '-'); ?></strong> on <strong><?= date('F j, Y', strtotime($next_booking['reservation_date'])); ?></strong> at <strong><?= htmlspecialchars($next_booking['time_slot'] ?? '-'); ?></strong>.</p>
            <button onclick="closeNotification()">Close</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <h3 style="margin-bottom:12px;">📅 Upcoming Bookings</h3>
        <table>
            <tr><th>Date</th><th>Time</th><th>Court</th><th>Status</th><th>Payment</th></tr>
            <?php if (empty($upcoming_rows)): ?>
            <tr><td colspan="5" style="text-align:center;color:#64748b;">No upcoming bookings yet.</td></tr>
            <?php else: foreach($upcoming_rows as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['reservation_date'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['time_slot'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['court_name'] ?? '-'); ?></td>
                <td><span class="badge <?= strtolower($row['status'] ?? 'pending'); ?>"><?= ucfirst($row['status'] ?? 'Pending'); ?></span></td>
                <td>
                    <?php if(($row['payment_status'] ?? '') == 'pending'): ?>
                        <span class="badge pending">Pending</span>
                    <?php elseif(($row['payment_status'] ?? '') == 'approved'): ?>
                        <span class="badge completed">Paid</span>
                    <?php else: ?>
                        <button class="payBtn" data-id="<?= (int)$row['id']; ?>">Pay</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-bottom:12px;">🕘 Recent Activity</h3>
        <table>
            <tr><th>Date</th><th>Time</th><th>Status</th><th>Court</th></tr>
            <?php if (empty($recent_rows)): ?>
            <tr><td colspan="4" style="text-align:center;color:#64748b;">No recent activity yet.</td></tr>
            <?php else: foreach($recent_rows as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['reservation_date'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['time_slot'] ?? '-'); ?></td>
                <td><span class="badge <?= strtolower($row['status'] ?? 'pending'); ?>"><?= ucfirst($row['status'] ?? 'Pending'); ?></span></td>
                <td><?= htmlspecialchars($row['court_name'] ?? '-'); ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </table>
    </div>

    <div class="modal" id="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal()">×</button>
            <h3>Downpayment</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="reservation_id" id="res_id">
                <input type="number" name="amount" placeholder="Amount" required>
                <select name="method">
                    <option value="GCash">GCash</option>
                    <option value="Cash">Cash</option>
                </select>
                <input type="text" name="reference" placeholder="Reference">
                <input type="file" name="proof">
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
</div>
</div>

<script>
const modal = document.getElementById("modal");
const resInput = document.getElementById("res_id");
const notification = document.getElementById("upcomingModal");

function closeModal(){ if(modal) modal.style.display='none'; }
function closeNotification(){ if(notification) notification.style.display='none'; }

<?php if ($upcoming_count > 0): ?>
window.addEventListener('DOMContentLoaded', ()=>{ if(notification) notification.style.display='block'; });
<?php endif; ?>

document.querySelectorAll(".payBtn").forEach(btn=>{
    btn.onclick=()=>{
        modal.style.display='block';
        resInput.value = btn.dataset.id;
    }
});
</script>

</body>
</html>