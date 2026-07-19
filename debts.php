<?php
require_once 'db_connect.php';

/* ADD DEBT */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_debt'])) {

    $customer_name = $_POST['customer_name'];
    $amount = $_POST['amount'];
    $debt_date = $_POST['debt_date'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("
        INSERT INTO debts
        (customer_name, amount, debt_date, description)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sdss",
        $customer_name,
        $amount,
        $debt_date,
        $description
    );

    $stmt->execute();
}

/* DELETE DEBT */

if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM debts
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: debts.php");
    exit();
}

/* FETCH DEBTS */

$debts = $conn->query("
    SELECT * FROM debts
    ORDER BY debt_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Debt Tracking</title>

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
            color:#222;
            margin-bottom:25px;
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

        input{
            width:100%;
            padding:12px;
            border:1px solid #ccc;
            border-radius:10px;
            outline:none;
            font-size:14px;
        }

        input:focus{
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

        .amount{
            color:#e53935;
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
               class="nav-link active">
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
            Debt Tracking
        </h1>

        <!-- ADD DEBT FORM -->

        <div class="card">

            <form method="POST">

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Customer Name
                        </label>

                        <input list="customer_names"
                               name="customer_name"
                               required>

                        <datalist id="customer_names">

                            <option value="Jane Smith">
                            <option value="Carlos Reyes">
                            <option value="Maria Santos">

                        </datalist>

                    </div>

                    <div class="form-group">

                        <label>
                            Amount (₱)
                        </label>

                        <input type="number"
                               step="0.01"
                               name="amount"
                               required>

                    </div>

                    <div class="form-group">

                        <label>
                            Date
                        </label>

                        <input type="date"
                               name="debt_date"
                               required>

                    </div>

                    <div class="form-group">

                        <label>
                            Product / Description
                        </label>

                        <input list="products"
                               name="description">

                        <datalist id="products">

                            <option value="Tennis Racket">
                            <option value="Badminton Shuttlecock">
                            <option value="Basketball">
                            <option value="Court Rental">

                        </datalist>

                    </div>

                </div>

                <br>

                <button type="submit"
                        name="add_debt"
                        class="btn btn-primary">

                    + Add Debt

                </button>

            </form>

        </div>

        <!-- DEBTS TABLE -->

        <div class="card table-container">

            <table>

                <thead>

                    <tr>

                        <th>Customer Name</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php while($row = $debts->fetch_assoc()) { ?>

                    <tr>

                        <td>
                            <?php echo $row['customer_name']; ?>
                        </td>

                        <td class="amount">
                            ₱<?php echo number_format($row['amount'],2); ?>
                        </td>

                        <td>
                            <?php echo $row['debt_date']; ?>
                        </td>

                        <td>
                            <?php echo $row['description']; ?>
                        </td>

                        <td>

                            <a href="?delete=<?php echo $row['id']; ?>">

                                <button class="btn btn-danger"
                                        onclick="return confirm('Delete this debt?')">

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

</body>
</html>