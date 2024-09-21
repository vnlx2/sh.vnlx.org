<?php

require __DIR__."/../init.php";

if (has_login_sess()) {
	header("Location: index.php?ref=register");
	exit(0);
}

if (isset($_GET["submit"]) && $_SERVER["REQUEST_METHOD"] === "POST") {
	$code = 400;
	$data = "Unknown error!";

	try {
		$p = validate_csrf($_POST["csrf"] ?? NULL);
		if (!$p)
			throw new Exception("Invalid CSRF token.");

		$pdo = pdo();
		$u = db_user_register($pdo, $_POST);
		$code = 200;
		$data = ["msg" => "OK!", "user_id" => $u];
	} catch (Exception $e) {
		$code = $e->getCode();
		$data = ["msg" => $e->getMessage()];
	}

	json_api_res($code, $data);
	exit(0);
}

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= e(APP_NAME); ?> | Register</title>
<link rel="stylesheet" type="text/css" href="<?= ASSETS_URL_PATH ?>/css/common.css" />
<script type="text/javascript" src="<?= ASSETS_URL_PATH ?>/js/common.js"></script>
<style type="text/css">
.dcg {
	width: 100%;
	max-width: 500px;
	margin: 0 auto;
	margin-top: 200px;
	padding: 20px;
	border: 1px solid #ccc;
	text-align: center;
}
.dcg form table {
	margin: auto;
	text-align: left;
}
</style>
</head>
<body>
<div class="dcg">
	<form id="reg-form" action="javascript:void(0);" method="POST">
		<h1>Create a VNL Shop Account</h1>
		<input type="hidden" id="csrf" name="csrf" value="<?= e(csrf_token()); ?>"/>
		<table>
			<tbody>
				<tr><td>Full Name</td><td>:</td><td><input type="text" name="fullname" required/></td></tr>
				<tr><td>Username</td><td>:</td><td><input type="text" name="username" required/></td></tr>
				<tr><td>E-Mail</td><td>:</td><td><input type="email" name="email" required/></td></tr>
				<tr><td>Password</td><td>:</td><td><input type="password" name="password" required/></td></tr>
				<tr><td>Retype Password</td><td>:</td><td><input type="password" name="cpassword" required/></td></tr>
				<tr><td colspan="3" align="center"><button id="btn-submit" type="submit">Register</button></td></tr>
				<tr><td colspan="3" align="center"><p>Already have an account? <a href="login.php?ref=register">Login</a></p></td></tr>
			</tbody>
		</table>
	</form>
</div>
<script type="text/javascript">
const reg_form = gid("reg-form");
function submit_form()
{
	let fr = gid("reg-form");
	let pwd = fr.password.value;

	if (pwd !== fr.cpassword.value) {
		alert("Password and Retype Password must be the same.");
		return;
	}

	if (pwd.length < 4) {
		alert("Password must be at least 4 characters.");
		return;
	}

	if (pwd.length > 512) {
		alert("Password cannot be more than 512 characters.");
		return;
	}

	let data = new FormData(fr);
	let xhr = new XMLHttpRequest();
	xhr.withCredentials = true;
	xhr.open("POST", "register.php?submit=1");
	xhr.onload = function() {
		try {
			let j = JSON.parse(this.responseText);
			if (j.code == 200) {
				alert("Registration successful!");
				window.location = "login.php";
			} else {
				alert(j.data.msg);
			}
		} catch (e) {
			alert("Error: " + e);
		}
	};
	xhr.send(data);
}
reg_form.addEventListener("submit", submit_form);
</script>
</body>
</html>
