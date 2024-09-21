<?php

require __DIR__."/../init.php";

if ($g_user_ss !== NULL) {
	header("Location: index.php?ref=register");
	exit(0);
}

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Register</title>
<link rel="stylesheet" type="text/css" href="<?= ASSETS_URL_PATH ?>/css/common.css" />
<style type="text/css">
.dcg {
	width: 100%;
	max-width: 500px;
	margin: 0 auto;
	padding: 20px;
	border: 1px solid #ccc;
	text-align: center;
}
.dcg form table {
	margin: auto;
	text-align: left;
}
button {
	cursor: pointer;
}
#btn-submit {
	margin-top: 30px;
	padding: 8px 15px 8px 15px;
	font-size: 16px;
	font-weight: bold;
}
</style>
</head>
<body>
	<div class="dcg">
		<form action="register.php?submit=1" method="POST" enctype="multipart/form-data">
			<h1>Create a VNL Shop Account</h1>
			<input type="hidden" id="csrf" name="csrf" value="<?= e(csrf_token()); ?>"/>
			<table>
				<tbody>
					<tr><td>Full Name</td><td>:</td><td><input type="text" name="fullname" required/></td></tr>
					<tr><td>Username</td><td>:</td><td><input type="text" name="username" required/></td></tr>
					<tr><td>E-Mail</td><td>:</td><td><input type="email" name="email" required/></td></tr>
					<tr><td>Password</td><td>:</td><td><input type="password" name="password" required/></td></tr>
					<tr><td>Retype Password</td><td>:</td><td><input type="password" name="cassword" required/></td></tr>
					<tr><td colspan="3" align="center"><button id="btn-submit" type="submit">Register</button></td></tr>
				</tbody>
			</table>
		</form>
	</div>
</body>
</html>
