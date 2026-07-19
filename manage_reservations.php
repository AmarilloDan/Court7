<?php
require_once __DIR__ . '/includes/init.php';
require_role('admin');
$conn = db();

/* =========================
   DELETE RESERVATION
========================= */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: manage_reservations.php?deleted=1");
    exit();
}

/* =========================
   UPDATE STATUS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_update'])) {

    $id = (int) $_POST['reservation_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    mysqli_query($conn, "
        UPDATE reservations 
        SET status = '$status'
        WHERE id = $id
    ");

    header("Location: manage_reservations.php?updated=1");
    exit();
}

/* =========================
   SEARCH
========================= */
$search = $_GET['search'] ?? '';
$where = '';

if (!empty($search)) {
    $safe = mysqli_real_escape_string($conn, $search);

    $where = "
        WHERE
            u.name LIKE '%$safe%' OR
            u.email LIKE '%$safe%' OR
            u.contact_number LIKE '%$safe%' OR
            u.address LIKE '%$safe%' OR
            r.court LIKE '%$safe%' OR
            r.status LIKE '%$safe%'
    ";
}

/* =========================
   FETCH RESERVATIONS
========================= */
$sql = "
    SELECT
        r.*,
        u.id AS user_id,
        u.name,
        u.email,
        u.contact_number,
        u.address,
        u.profile_photo
    FROM reservations r
    INNER JOIN users u ON r.user_id = u.id
    $where
    ORDER BY r.reservation_date DESC, r.time_slot DESC
";

$reservations = mysqli_query($conn, $sql);

render_header('Manage Reservations');
?>

<style>
/* 🔥 YOUR ORIGINAL CSS (UNCHANGED) */
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:#f4f7fb;
}
.container{ padding:30px; }
.card{
    background:#fff;
    border-radius:20px;
    padding:30px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}
h2{ margin-bottom:20px; }
.search-box input{
    width:50%;
    padding:12px;
    border-radius:10px;
    border:1px solid #ddd;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}
th{
    background:#4e73df;
    color:#fff;
    padding:12px;
}
td{
    padding:12px;
    border-bottom:1px solid #eee;
}
.user-cell{
    display:flex;
    align-items:center;
    gap:10px;
}
.user-cell img{
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
}
.btn{
    padding:8px 14px;
    border:none;
    border-radius:8px;
    color:#fff;
    cursor:pointer;
}
.btn-view{ background:#36b9cc; }
.btn-delete{ background:#e74a3b; }

/* MODAL */
.modal{
    display:none;
    position:fixed;
    z-index:999;
    left:0; top:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.6);
}
/* Enhanced Modal Styling */
.modal-content{
    background: #f8fafd;
    margin: 4% auto;
    padding: 35px 35px 25px 35px;
    width: 100%;
    max-width: 420px;
    max-height: 85vh;
    overflow-y: auto;
    border-radius: 18px;
    text-align: left;
    position: relative;
    box-shadow: 0 8px 32px rgba(78,115,223,0.18);
    border: 1.5px solid #e3e6f0;
}
.modal-header {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 18px;
    border-bottom: 1.5px solid #e3e6f0;
    padding-bottom: 12px;
}
.modal-img{
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #4e73df;
    box-shadow: 0 2px 8px rgba(78,115,223,0.10);
    background: #fff;
}
.modal-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #4e73df;
    margin: 0;
}
.modal-section {
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e3e6f0;
}
.modal-section:last-child {
    border-bottom: none;
}
.modal-label {
    color: #858796;
    font-size: 0.98rem;
    margin-right: 6px;
    font-weight: 500;
}
.modal-value {
    color: #343a40;
    font-size: 1.05rem;
    font-weight: 500;
}
.modal-icon {
    font-size: 1.1em;
    margin-right: 7px;
    color: #36b9cc;
    vertical-align: middle;
}
.modal-finance {
    display: flex;
    gap: 18px;
    margin-bottom: 10px;
}
.modal-finance .modal-section {
    margin-bottom: 0;
    border-bottom: none;
}
.modal-purchases-list {
    background: #f1f3fa;
    border-radius: 8px;
    padding: 10px 12px;
    margin-top: 6px;
    font-size: 0.98rem;
    max-height: 120px;
    overflow-y: auto;
}
.modal-purchases-list ul {
    margin: 0;
    padding: 0 0 0 10px;
}
.modal-purchases-list li {
    padding: 4px 0;
    border-bottom: 1px solid #e3e6f0;
    font-size: 0.97rem;
}
.modal-purchases-list li:last-child {
    border-bottom: none;
}
.modal-content button#printBtn {
    background: #28a745;
    margin-top: 14px;
    width: 100%;
    font-size: 1.05rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}
.close{
    position:absolute;
    right:15px;
    top:10px;
    font-size:22px;
    cursor:pointer;
    color: #e74a3b;
    font-weight: bold;
    transition: color 0.2s;
}
.close:hover {
    color: #b71c1c;
}
.close{
    position:absolute;
    right:15px;
    top:10px;
    font-size:22px;
    cursor:pointer;
}

/* PURCHASES LIST */
#modalPurchases ul {
    list-style-type: none;
    padding: 0;
}
#modalPurchases li {
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}
</style>

<div class="container">
<div class="card">

<h2>Reservation Management</h2>

<form method="GET" class="search-box">
    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
</form>

<?php if(isset($_GET['deleted'])): ?>
<p style="color:green;">Deleted successfully</p>
<?php endif; ?>

<?php if(isset($_GET['updated'])): ?>
<p style="color:green;">Status updated successfully</p>
<?php endif; ?>

<table>
<tr>
<th>User</th>
<th>Contact</th>
<th>Address</th>
<th>Court</th>
<th>Date</th>
<th>Time</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php while($r = mysqli_fetch_assoc($reservations)): ?>

<?php
$photo = (!empty($r['profile_photo']) && file_exists($r['profile_photo']))
    ? $r['profile_photo']
    : 'assets/images/default-user.png';
?>

<tr>
<td>
<div class="user-cell">
<img src="<?= htmlspecialchars($photo) ?>">
<div>
<strong><?= htmlspecialchars($r['name']) ?></strong><br>
<small><?= htmlspecialchars($r['email']) ?></small>
</div>
</div>
</td>

<td><?= htmlspecialchars($r['contact_number']) ?></td>
<td><?= htmlspecialchars($r['address']) ?></td>
<td><?= htmlspecialchars($r['court']) ?></td>
<td><?= htmlspecialchars($r['reservation_date']) ?></td>
<td><?= htmlspecialchars($r['time_slot']) ?></td>

<td>
<form method="POST">
<input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">

<select name="status" onchange="this.form.submit()">
<option value="cancelled" <?= $r['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
<option value="reserved" <?= $r['status']=='reserved'?'selected':'' ?>>Reserved</option>
<option value="completed" <?= $r['status']=='completed'?'selected':'' ?>>Completed</option>
</select>

<input type="hidden" name="status_update" value="1">
</form>
</td>

<td>
<button class="btn btn-view view-btn"
data-user-id="<?= $r['user_id'] ?>"
data-name="<?= htmlspecialchars($r['name']) ?>"
data-email="<?= htmlspecialchars($r['email']) ?>"
data-contact="<?= htmlspecialchars($r['contact_number']) ?>"
data-address="<?= htmlspecialchars($r['address']) ?>"
data-photo="<?= htmlspecialchars($photo) ?>">
View Details
</button>

<a href="?delete=<?= $r['id'] ?>" class="btn btn-delete"
onclick="return confirm('Delete this?')">
Delete
</a>
</td>
</tr>

<?php endwhile; ?>
</table>

</div>
</div>

<div id="profileModal" class="modal">
    <div class="modal-content" id="modalContent">
        <span class="close">&times;</span>
        <div class="modal-header">
            <img id="modalPhoto" class="modal-img">
            <div>
                <div class="modal-title" id="modalName"></div>
                <div style="color:#858796; font-size:0.98rem;" id="modalEmail"></div>
            </div>
        </div>
        <div class="modal-section">
            <span class="modal-icon">📞</span><span class="modal-label">Contact:</span> <span class="modal-value" id="modalContact"></span><br>
            <span class="modal-icon">🏠</span><span class="modal-label">Address:</span> <span class="modal-value" id="modalAddress"></span>
        </div>
        <div class="modal-finance">
            <div class="modal-section" style="flex:1;">
                <span class="modal-label">Debt:</span> <span class="modal-value" style="color:#e74a3b;">₱<span id="modalDebt">0.00</span></span>
            </div>
            <div class="modal-section" style="flex:1;">
                <span class="modal-label">Balance:</span> <span class="modal-value" style="color:#28a745;">₱<span id="modalBalance">0.00</span></span>
            </div>
        </div>
        <div class="modal-section">
            <span class="modal-label" style="font-size:1.05rem;">Purchases:</span>
            <div id="modalPurchases" class="modal-purchases-list">
                <p>Loading...</p>
            </div>
        </div>
        <button id="printBtn" class="btn">Print Receipt</button>
    </div>
</div>

<script>
const modal = document.getElementById("profileModal");
const closeBtn = document.querySelector(".close");

document.querySelectorAll(".view-btn").forEach(btn => {
btn.addEventListener("click", function(){
const userId = this.dataset.userId;
document.getElementById("modalName").innerText = this.dataset.name;
document.getElementById("modalEmail").innerText = this.dataset.email;
document.getElementById("modalContact").innerText = this.dataset.contact;
document.getElementById("modalAddress").innerText = this.dataset.address;
document.getElementById("modalPhoto").src = this.dataset.photo;

// Fetch debt, balance, purchases
fetch(`get_user_details.php?user_id=${userId}`)
.then(response => response.json())
.then(data => {
document.getElementById("modalDebt").innerText = parseFloat(data.debt).toFixed(2);
document.getElementById("modalBalance").innerText = parseFloat(data.balance).toFixed(2);

const purchasesDiv = document.getElementById("modalPurchases");
if (data.purchases.length > 0) {
    let html = '<ul>';
    data.purchases.forEach(p => {
        html += `<li><b>${p.product_name}</b> <span style='color:#858796;'>| Qty:</span> ${p.quantity} <span style='color:#858796;'>| Total:</span> ₱${parseFloat(p.total).toFixed(2)} <span style='color:#858796;'>|</span> <span style='font-size:0.93em;'>${p.created_at}</span></li>`;
    });
    html += '</ul>';
    purchasesDiv.innerHTML = html;
} else {
    purchasesDiv.innerHTML = '<p style="color:#858796;">No purchases found.</p>';
}
})
.catch(error => {
console.error('Error fetching user details:', error);
document.getElementById("modalDebt").innerText = 'Error';
document.getElementById("modalBalance").innerText = 'Error';
document.getElementById("modalPurchases").innerHTML = '<p>Error loading purchases.</p>';
});

modal.style.display = "block";
});
});

closeBtn.onclick = () => modal.style.display = "none";
window.onclick = (e) => { if (e.target == modal) modal.style.display = "none"; };

// Print functionality
document.getElementById("printBtn").addEventListener("click", function(){
const name = document.getElementById("modalName").innerText;
const email = document.getElementById("modalEmail").innerText;
const contact = document.getElementById("modalContact").innerText;
const address = document.getElementById("modalAddress").innerText;
const debt = document.getElementById("modalDebt").innerText;
const balance = document.getElementById("modalBalance").innerText;
const purchases = document.getElementById("modalPurchases").innerHTML;

const printWindow = window.open('', '_blank');
printWindow.document.write(`
<html>
<head>
<title>Court 7 Receipt</title>
<style>
body {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.4;
    margin: 0;
    padding: 10px;
    background: white;
    color: black;
    width: 300px;
    margin: 0 auto;
}
.receipt {
    border: 1px dashed #000;
    padding: 10px;
    text-align: center;
}
.header {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 10px;
}
.line {
    border-top: 1px dashed #000;
    margin: 5px 0;
}
.item {
    text-align: left;
    margin: 5px 0;
}
.total {
    font-weight: bold;
    text-align: right;
}
.footer {
    margin-top: 10px;
    font-size: 10px;
}
</style>
</head>
<body>
<div class="receipt">
<div class="header">COURT 7 RECEIPT</div>
<div class="line"></div>
<p><strong>Customer:</strong> ${name}</p>
<p><strong>Email:</strong> ${email}</p>
<p><strong>Contact:</strong> ${contact}</p>
<p><strong>Address:</strong> ${address}</p>
<p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
<div class="line"></div>
<h4>Purchases:</h4>
${purchases.replace(/<ul>/g, '').replace(/<\/ul>/g, '').replace(/<li>/g, '').replace(/<\/li>/g, '<br>')}
<div class="line"></div>
<p class="total"><strong>Balance Paid:</strong> ₱${balance}</p>
<p class="total"><strong>Outstanding Debt:</strong> ₱${debt}</p>
<div class="line"></div>
<div class="footer">
Thank you for your business!<br>
Court 7 Management
</div>
</div>
</body>
</html>
`);
printWindow.document.close();
printWindow.print();
});
</script>

<?php render_footer(); ?>