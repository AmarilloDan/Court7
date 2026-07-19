<?php
require_once 'db_connect.php';

session_start();

/* LOGOUT CONFIRMATION */

if (isset($_POST['confirm_logout'])) {

    session_unset();

    session_destroy();

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

    <title>Logout</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial,sans-serif;
        }

        body{
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background:black;
        }

        .logout-box{
            background:white;
            padding:35px;
            width:100%;
            max-width:400px;
            border-radius:15px;
            text-align:center;
            box-shadow:0 5px 20px rgba(0,0,0,0.1);
        }

        h2{
            margin-bottom:15px;
            color:#111;
        }

        p{
            color:#555;
            margin-bottom:25px;
            font-size:15px;
        }

        .btn-group{
            display:flex;
            gap:15px;
            justify-content:center;
        }

        button,
        a{
            flex:1;
            padding:12px;
            border:none;
            border-radius:10px;
            font-size:15px;
            font-weight:bold;
            cursor:pointer;
            text-decoration:none;
            transition:0.3s;
        }

        .logout-btn{
            background:green;
            color:white;
        }

        .logout-btn:hover{
            background:#d32f2f;
        }

        .cancel-btn{
            background:#ddd;
            color:#111;
            text-align:center;
        }

        .cancel-btn:hover{
            background:red;
        }

    </style>

</head>

<body>

    <div class="logout-box">

        <h2>
            Logout Confirmation
        </h2>

        <p>
            Do you want to log out of your account?
        </p>

        <div class="btn-group">

            <form method="POST"
                  style="flex:1;">

                <button type="submit"
                        name="confirm_logout"
                        class="logout-btn">

                    YES, LOG OUT

                </button>

            </form>

            <a href="index.php"
               class="cancel-btn">

                CANCEL

            </a>

        </div>

    </div>

</body>

</html>