<?php
require_once 'db_connect.php';

session_start();

$usersFile = __DIR__ . '/uploads/users.json';
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, "[]");
}

function load_users_from_file($path) {
    $data = @file_get_contents($path);
    if ($data === false || trim($data) === '') {
        return [];
    }

    $decoded = json_decode($data, true);
    return is_array($decoded) ? $decoded : [];
}

function save_users_to_file($path, $users) {
    return file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (isset($_POST['register'])) {
		$name = trim($_POST['full_name'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$username = trim($_POST['username'] ?? '');
		$password = trim($_POST['password'] ?? '');

		if ($name === '' || $email === '' || $username === '' || $password === '') {
			$message = 'Please fill in all registration fields.';
			$messageType = 'error';
		} else {
			$users = load_users_from_file($usersFile);
			$exists = false;
			foreach ($users as $userEntry) {
				if (($userEntry['username'] ?? '') === $username || ($userEntry['email'] ?? '') === $email) {
					$exists = true;
					break;
				}
			}
			if ($exists) {
				$message = 'That username or email already exists.';
				$messageType = 'error';
			} else {
				$users[] = [
					'id' => time(),
					'name' => $name,
					'email' => $email,
					'username' => $username,
					'password' => password_hash($password, PASSWORD_DEFAULT),
				];
				if (save_users_to_file($usersFile, $users) !== false) {
					session_regenerate_id(true);
					$_SESSION['user'] = $username;
					$_SESSION['user_id'] = time();
					$_SESSION['id'] = time();
					$_SESSION['name'] = $name;
					$_SESSION['role'] = 'user';
					header('Location: user_dashboard.php');
					exit();
				} else {
					$message = 'Registration failed. Please try again.';
					$messageType = 'error';
				}
			}
		}
	} else {
		$username = trim($_POST['username'] ?? '');
		$password = trim($_POST['password'] ?? '');

		if ($username == 'admin' && $password == 'admin123') {
			$_SESSION['admin'] = $username;
			header('Location: index.php');
			exit();
		}

		$users = load_users_from_file($usersFile);
		$matchedUser = null;
		foreach ($users as $userEntry) {
			if (($userEntry['username'] ?? '') === $username) {
				$matchedUser = $userEntry;
				break;
			}
		}
		if ($matchedUser && password_verify($password, $matchedUser['password'] ?? '')) {
			session_regenerate_id(true);
			$_SESSION['user'] = $matchedUser['username'];
			$_SESSION['user_id'] = $matchedUser['id'];
			$_SESSION['id'] = $matchedUser['id'];
			$_SESSION['name'] = $matchedUser['name'];
			$_SESSION['role'] = 'user';
			header('Location: user_dashboard.php');
			exit();
		}
		$error = 'Invalid Login Credentials';
		if ($conn !== null) {
			$stmt = $conn->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
			if ($stmt === false) {
				$error = 'Unable to sign in right now. Please try again later.';
			} else {
				$stmt->bind_param('s', $username);
				$stmt->execute();
				$result = $stmt->get_result();
				$user = $result->fetch_assoc();
				$stmt->close();

				$storedPassword = $user['password'] ?? '';
				$validPassword = $user && (
					($storedPassword !== '' && password_verify($password, $storedPassword)) ||
					($storedPassword !== '' && $storedPassword === $password)
				);
				if ($validPassword) {
					session_regenerate_id(true);
					$_SESSION['user'] = $user['username'];
					$_SESSION['user_id'] = $user['id'];
					$_SESSION['id'] = $user['id'];
					$_SESSION['name'] = $user['username'];
					$_SESSION['role'] = 'user';
					header('Location: user_dashboard.php');
					exit();
				}

				$error = 'Invalid Login Credentials';
			}
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="UTF-8">

	<meta name="viewport"
		  content="width=device-width, initial-scale=1.0">

	<title>Sign In to Court7</title>

	<style>

		*{
			margin:0;
			padding:0;
			box-sizing:border-box;
			font-family:Arial,sans-serif;
		}

		body{
			background:black;
			height:100vh;
			display:flex;
			align-items:center;
			justify-content:center;
			padding:20px;
		}

		.container{
			width:100%;
			max-width:1200px;
			display:flex;
			align-items:center;
			justify-content:space-between;
			gap:60px;
		}

		.left-side{
			flex:1;
		}

		.logo-image{
			width:100%;
			max-width:750px;
			display:block;
			margin:auto;
			border-radius:500px;
		}

		.right-side{
			width:420px;
		}

		.login-card{
			background:white;
			padding:25px;
			border-radius:12px;
			box-shadow:0 2px 12px rgba(0,0,0,0.15);
			height:25%;
		}

		.login-title{
			font-size:32px;
			font-weight:bold;
			color:#111;
			text-align:center;
			margin-bottom:10px;
		}

		.login-subtitle{
			text-align:center;
			color:#666;
			margin-bottom:25px;
			font-size:15px;
		}

		.error{
			background:#ffe5e5;
			color:#d8000c;
			padding:12px;
			border-radius:8px;
			margin-bottom:20px;
			text-align:center;
			font-size:14px;
		}

		.form-group{
			margin-bottom:18px;
		}

		.form-group label{
			display:block;
			margin-bottom:8px;
			font-weight:bold;
			color:#333;
			font-size:14px;
		}

		.form-group input{
			width:100%;
			padding:14px;
			border:1px solid #ddd;
			border-radius:10px;
			font-size:15px;
			outline:none;
			transition:0.3s;
		}

		.form-group input:focus{
			border-color:#1877f2;
			box-shadow:0 0 0 3px rgba(24,119,242,0.15);
		}

		.success{
			background:#e7f9ed;
			color:#1f7a3d;
			padding:12px;
			border-radius:8px;
			margin-bottom:20px;
			text-align:center;
			font-size:14px;
		}

		.login-btn{
			width:100%;
			padding:14px;
			background:#1877f2;
			color:white;
			border:none;
			border-radius:10px;
			font-size:16px;
			font-weight:bold;
			cursor:pointer;
			transition:0.3s;
			margin-top:5px;
		}

		.login-btn:hover{
			background:#166fe5;
		}

		.links{
			text-align:center;
			margin-top:18px;
		}

		.links a{
			text-decoration:none;
			color:#1877f2;
			font-size:14px;
			display:block;
			margin-top:10px;
			transition:0.3s;
		}

		.links a:hover{
			text-decoration:underline;
		}

		.divider{
			height:1px;
			background:#ddd;
			margin:25px 0;
		}

		.register-btn{
			width:100%;
			padding:14px;
			background:#42b72a;
			color:white;
			border:none;
			border-radius:10px;
			font-size:15px;
			font-weight:bold;
			cursor:pointer;
			transition:0.3s;
		}

		.register-btn:hover{
			background:#36a420;
		}

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
			max-width:450px;
			border-radius:12px;
			padding:30px;
			position:relative;
			animation:fadeIn 0.3s ease;
		}

		.close-btn{
			position:absolute;
			right:18px;
			top:15px;
			font-size:28px;
			cursor:pointer;
			color:#555;
		}

		.modal-title{
			font-size:28px;
			font-weight:bold;
			margin-bottom:5px;
			color:#111;
		}

		.modal-subtitle{
			color:#666;
			margin-bottom:20px;
			font-size:14px;
		}

		.submit-btn{
			width:100%;
			padding:14px;
			border:none;
			border-radius:10px;
			background:#1877f2;
			color:white;
			font-size:15px;
			font-weight:bold;
			cursor:pointer;
			transition:0.3s;
		}

		.submit-btn:hover{
			background:#166fe5;
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

		@media(max-width:900px){

			.container{
				flex-direction:column;
				text-align:center;
			}

			.right-side{
				width:100%;
				max-width:420px;
			}

			.logo-image{
				max-width:350px;
			}

		}

	</style>

</head>

<body>

	<div class="container">

		<div class="left-side">

			<img src="images/logoo.png"
				 alt="Court 7 Logo"
				 class="logo-image">

		</div>

		<div class="right-side">

			<div class="login-card">

				<div class="login-title">
					<h4>Sign In</h4>
				</div>

				<div class="login-subtitle">
					An Online Court Booking System
				</div>

				<?php if(isset($error)) { ?>

					<div class="error">

						<?php echo $error; ?>

					</div>

				<?php } ?>

				<?php if ($message !== '') { ?>

					<div class="<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">

						<?php echo $message; ?>

					</div>

				<?php } ?>

				<form method="POST">

					<div class="form-group">

						<label>
							Username
						</label>

						<input type="text"
							   name="username"
							   placeholder="Enter username"
							   required>

					</div>

					<div class="form-group">

						<label>
							Password
						</label>

						<input type="password"
							   name="password"
							   placeholder="Enter password"
							   required>

					</div>

					<button type="submit"
							class="login-btn">

						LOG IN

					</button>

				</form>

				<div class="links">

					<a href="#"
					   id="forgotPasswordBtn">

						Forgot Password?

					</a>

				</div>

				<div class="divider"></div>

				<button class="register-btn"
						id="registerBtn">

					Create New Account

				</button>

			</div>

		</div>

	</div>

	<!-- REGISTER MODAL -->

	<div class="modal"
		 id="registerModal">

		<div class="modal-content">

			<span class="close-btn"
				  id="closeRegister">

				&times;

			</span>

			<div class="modal-title">
				Create Account
			</div>

			<div class="modal-subtitle">
				Register your Court 7 account
			</div>

			<form method="POST">
				<input type="hidden" name="register" value="1">

				<div class="form-group">

					<label>
						Full Name
					</label>

					<input type="text"
						   name="full_name"
						   placeholder="Enter full name"
						   required>

				</div>

				<div class="form-group">

					<label>
						Email Address
					</label>

					<input type="email"
						   name="email"
						   placeholder="Enter email"
						   required>

				</div>

				<div class="form-group">

					<label>
						Username
					</label>

					<input type="text"
						   name="username"
						   placeholder="Choose username"
						   required>

				</div>

				<div class="form-group">

					<label>
						Password
					</label>

					<input type="password"
						   name="password"
						   placeholder="Create password"
						   required>

				</div>

				<button type="submit"
						class="submit-btn">

					Register Account

				</button>

			</form>

		</div>

	</div>

	<!-- FORGOT PASSWORD MODAL -->

	<div class="modal"
		 id="forgotModal">

		<div class="modal-content">

			<span class="close-btn"
				  id="closeForgot">

				&times;

			</span>

			<div class="modal-title">
				Forgot Password
			</div>

			<div class="modal-subtitle">
				Enter your email to reset your password
			</div>

			<form>

				<div class="form-group">

					<label>
						Email Address
					</label>

					<input type="email"
						   placeholder="Enter your email"
						   required>

				</div>

				<button type="submit"
						class="submit-btn">

					Send Reset Link

				</button>

			</form>

		</div>

	</div>

	<script>

		/* REGISTER MODAL */

		const registerBtn =
		document.getElementById(
			'registerBtn'
		);

		const registerModal =
		document.getElementById(
			'registerModal'
		);

		const closeRegister =
		document.getElementById(
			'closeRegister'
		);

		registerBtn.onclick = () => {

			registerModal.style.display =
			'flex';

		}

		closeRegister.onclick = () => {

			registerModal.style.display =
			'none';

		}

		/* FORGOT PASSWORD MODAL */

		const forgotPasswordBtn =
		document.getElementById(
			'forgotPasswordBtn'
		);

		const forgotModal =
		document.getElementById(
			'forgotModal'
		);

		const closeForgot =
		document.getElementById(
			'closeForgot'
		);

		forgotPasswordBtn.onclick = () => {

			forgotModal.style.display =
			'flex';

		}

		closeForgot.onclick = () => {

			forgotModal.style.display =
			'none';

		}

		/* CLOSE MODALS */

		window.onclick = (e) => {

			if(e.target == registerModal){

				registerModal.style.display =
				'none';

			}

			if(e.target == forgotModal){

				forgotModal.style.display =
				'none';

			}

		}

	</script>

</body>

</html>