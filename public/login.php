<?php

require __DIR__."/../init.php";

if (has_login_sess()) {
	header("Location: index.php?ref=login");
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
		$u = db_user_login($pdo, $_POST);
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
<title><?= e(APP_NAME); ?> | Login</title>
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
<body>
<div class="dcg">
	<form id="log-form" action="javascript:void(0);" method="POST">
		<h1>Login</h1>
		<input type="hidden" id="csrf" name="csrf" value="<?= e(csrf_token()); ?>"/>
		<table>
			<tbody>
				<tr><td>Username or Email</td><td>:</td><td><input type="text" name="user" required/></td></tr>
				<tr><td>Password</td><td>:</td><td><input type="password" name="pass" required/></td></tr>
				<tr><td colspan="3" align="center"><button id="btn-submit" type="submit">Login</button></td></tr>
				<tr><td colspan="3" align="center"><p>Don't have an account? <a href="register.php?ref=login">Register</a></p></td></tr>
			</tbody>
		</table>
	</form>
</div>
<script type="text/javascript">
const log_form = gid("log-form");
function submit_form()
{
	let fr = gid("log-form");
	let user = fr.user.value;
	let pwd = fr.pass.value;

	if (user.length < 3) {
		alert("Wrong username or password!");
		return;
	}

	if (pwd.length < 4) {
		alert("Wrong username or password!");
		return;
	}

	let data = new FormData(fr);
	let xhr = new XMLHttpRequest();
	xhr.withCredentials = true;
	xhr.open("POST", "login.php?submit");
	xhr.onload = function() {
		try {
			let j = JSON.parse(this.responseText);
			if (j.code == 200) {
				alert("Login successful.");
				window.location = "index.php";
			} else if (j.code == 401) {
				alert("Wrong username or password!");
			} else {
				alert("Error: " + j.data.msg);
			}
		} catch (e) {
			alert("Error: " + e);
		}
	};
	xhr.send(data);
}
log_form.addEventListener("submit", submit_form);
</script>
</body>
</html>
