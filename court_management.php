<?php
require_once 'db_connect.php';
/* ADD COURT */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_court'])) {

    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';

    $columnsResult = $conn->query('SHOW COLUMNS FROM courts');
    $columns = [];
    if ($columnsResult) {
        while ($column = $columnsResult->fetch_assoc()) {
            $columns[] = $column['Field'];
        }
    }

    $insertColumns = [];
    $insertValues = [];
    $bindTypes = '';

    if (in_array('name', $columns, true)) {
        $insertColumns[] = 'name';
        $insertValues[] = $name;
        $bindTypes .= 's';
    }

    if (in_array('type', $columns, true)) {
        $insertColumns[] = 'type';
        $insertValues[] = $type;
        $bindTypes .= 's';
    }

    if (empty($insertColumns)) {
        $stmt = $conn->prepare('INSERT INTO courts VALUES ()');
        $stmt->execute();
    } else {
        $sql = 'INSERT INTO courts (' . implode(', ', $insertColumns) . ') VALUES (' . str_repeat('?, ', count($insertColumns) - 1) . '?)';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($bindTypes, ...$insertValues);
        $stmt->execute();
    }
}

/* DELETE COURT */

if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM courts
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: court_management.php");
    exit();
}

/* EDIT COURT */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_court'])) {

    $id = $_POST['court_id'];
    $name = $_POST['edit_name'] ?? '';
    $type = $_POST['edit_type'] ?? '';

    $columnsResult = $conn->query('SHOW COLUMNS FROM courts');
    $columns = [];
    if ($columnsResult) {
        while ($column = $columnsResult->fetch_assoc()) {
            $columns[] = $column['Field'];
        }
    }

    $setParts = [];
    $values = [];
    $bindTypes = '';

    if (in_array('name', $columns, true)) {
        $setParts[] = 'name = ?';
        $values[] = $name;
        $bindTypes .= 's';
    }

    if (in_array('type', $columns, true)) {
        $setParts[] = 'type = ?';
        $values[] = $type;
        $bindTypes .= 's';
    }

    if (empty($setParts)) {
        $setParts[] = 'id = id';
    }

    $values[] = $id;
    $bindTypes .= 'i';

    $sql = 'UPDATE courts SET ' . implode(', ', $setParts) . ' WHERE id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($bindTypes, ...$values);
    $stmt->execute();

    header("Location: court_management.php");
    exit();
}

/* FETCH COURTS */

$columnsResult = $conn->query('SHOW COLUMNS FROM courts');
$columns = [];
if ($columnsResult) {
    while ($column = $columnsResult->fetch_assoc()) {
        $columns[] = $column['Field'];
    }
}

$orderClause = '';
if (in_array('id', $columns, true)) {
    $orderClause = 'id DESC';
} elseif (in_array('name', $columns, true)) {
    $orderClause = 'name ASC';
} elseif (in_array('court_name', $columns, true)) {
    $orderClause = 'court_name ASC';
}

if ($orderClause) {
    $courts = $conn->query("SELECT * FROM courts ORDER BY $orderClause");
} else {
    $courts = $conn->query('SELECT * FROM courts');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Court Management</title>

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

        /* CARD */

        .card{
            background:white;
            padding:25px;
            border-radius:15px;
            box-shadow:0 4px 15px rgba(0,0,0,0.08);
            margin-bottom:30px;
        }

        /* FORM */

        .form-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
            gap:20px;
        }

        .form-group label{
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

        /* BUTTONS */

        .btn{
            border:none;
            padding:12px 20px;
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

        .btn-warning{
            background:#ff9800;
            color:white;
        }

        .btn-warning:hover{
            background:#e68900;
        }

        .btn-danger{
            background:#f44336;
            color:white;
        }

        .btn-danger:hover{
            background:#d32f2f;
        }

        /* TABLE */

        .table-container{
            overflow-x:auto;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
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
        }

        .modal-content{
            background:white;
            width:90%;
            max-width:450px;
            padding:25px;
            border-radius:15px;
        }

        .modal-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }

        .close{
            font-size:25px;
            cursor:pointer;
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

    <!-- HEADER -->

    <div class="header">

        <div class="title">
            Court 7 ADMIN
        </div>

        <nav class="navbar">

            <a href="index.php" class="nav-link">
                Home Page
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

            <a href="court_management.php"
               class="nav-link active">
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
            Court Management
        </h1>

        <!-- ADD COURT -->

        <div class="card">

            <form method="POST">

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Court Name
                        </label>

                        <input type="text"
                               name="name"
                               required>

                    </div>

                    <div class="form-group">

                        <label>
                            Court Type
                        </label>

                        <select name="type" required>

                            <option value="">
                                Select Court
                            </option>

                            <option value="Court 1">
                                Court 1
                            </option>

                            <option value="Court 2">
                                Court 2
                            </option>

                            <option value="Court 3">
                                Court 3
                            </option>

                            <option value="Court 4">
                                Court 4
                            </option>

                        </select>

                    </div>

                </div>

                <br>

                <button type="submit"
                        name="add_court"
                        class="btn btn-primary">

                    + Add Court

                </button>

            </form>

        </div>

        <!-- COURTS TABLE -->

        <div class="card table-container">

            <table>

                <thead>

                    <tr>

                        <th>Court Name</th>
                        <th>Type</th>
                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php while($row = $courts->fetch_assoc()) { ?>

                    <tr>

                        <td>
                            <?php echo $row['name']; ?>
                        </td>

                        <td>
                            <?php echo $row['type']; ?>
                        </td>

                        <td>

                            <button class="btn btn-warning editBtn"
                                    data-id="<?php echo $row['id']; ?>"
                                    data-name="<?php echo $row['name']; ?>"
                                    data-type="<?php echo $row['type']; ?>">

                                Edit

                            </button>

                            <a href="?delete=<?php echo $row['id']; ?>">

                                <button class="btn btn-danger"
                                        onclick="return confirm('Delete this court?')">

                                    Delete

                                </button>

                            </a>

                        </td>

                    </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- EDIT MODAL -->

    <div class="modal" id="editModal">

        <div class="modal-content">

            <div class="modal-header">

                <h2>Edit Court</h2>

                <span class="close">
                    &times;
                </span>

            </div>

            <form method="POST">

                <input type="hidden"
                       name="court_id"
                       id="court_id">

                <div class="form-group">

                    <label>
                        Court Name
                    </label>

                    <input type="text"
                           name="edit_name"
                           id="edit_name"
                           required>

                </div>

                <br>

                <div class="form-group">

                    <label>
                        Court Type
                    </label>

                    <select name="edit_type"
                            id="edit_type"
                            required>

                        <option value="Basketball">
                            Basketball
                        </option>

                        <option value="Badminton">
                            Badminton
                        </option>

                        <option value="Tennis">
                            Tennis
                        </option>

                        <option value="Volleyball">
                            Volleyball
                        </option>

                    </select>

                </div>

                <br>

                <button type="submit"
                        name="edit_court"
                        class="btn btn-primary">

                    Save Changes

                </button>

            </form>

        </div>

    </div>

    <script>

        const modal = document.getElementById('editModal');
        const closeBtn = document.querySelector('.close');

        document.querySelectorAll('.editBtn')
        .forEach(button => {

            button.addEventListener('click', () => {

                modal.style.display = 'flex';

                document.getElementById('court_id').value =
                    button.dataset.id;

                document.getElementById('edit_name').value =
                    button.dataset.name;

                document.getElementById('edit_type').value =
                    button.dataset.type;

            });

        });

        closeBtn.onclick = () => {
            modal.style.display = 'none';
        }

        window.onclick = (e) => {

            if (e.target == modal) {
                modal.style.display = 'none';
            }

        }

    </script>

</body>
</html>