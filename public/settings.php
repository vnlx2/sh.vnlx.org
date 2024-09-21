<?php

require __DIR__."/../init.php";

if (!has_login_sess()) {
	header("Location: login.php?ref=home");
	exit(0);
}

$has_action = (isset($_GET["action"]) && is_string($_GET["action"]));

if ($has_action && $_SERVER["REQUEST_METHOD"] === "POST") {
	$code = -1;
	$data = NULL;

	try {
		$p = validate_csrf($_POST["csrf_token"] ?? NULL);
		if (!$p)
			throw new Exception("Invalid CSRF token.");

		switch ($_GET["action"]) {
		case "change_pwd":
			db_user_change_pwd(pdo(), $_POST);
			$code = 200;
			$msg = ["msg" => "Password changed successfully!"];
			break;
		}
	} catch (Exception $e) {
		$code = $e->getCode();
		$data = ["msg" => $e->getMessage()];
	}

	if ($code !== -1) {
		json_api_res($code, $data);
		exit(0);
	}
}

?><!DOCTYPE html>
<html>
<head>
<title><?= e(APP_NAME); ?> | Home</title>
<link rel="stylesheet" type="text/css" href="<?= ASSETS_URL_PATH ?>/css/common.css" />
<script type="text/javascript" src="<?= ASSETS_URL_PATH ?>/js/common.js"></script>
<style type="text/css">
.dcg {
	margin: 0 auto;
	margin-top: 200px;
	padding: 20px;
	border: 1px solid #ccc;
	text-align: left;
	width: 800px;
}
.tbd {
	width: 100%;
}
.atd {
	padding: 10px;
	border: 1px solid #ccc;
}
.atb {
	cursor: pointer;
}
.atb:hover {
	background-color: #f0f0f0;
}
</style>
</head>
<body>
<?php load_comp("navbar", ["active" => "settings"]); ?>
<div class="dcg">
	<table class="tbd">
		<tbody>
			<tr>
				<td class="atd atb" id="bt_change_pwd" onclick="toggle_view('change_pwd')">Change Password</td>
				<td class="atd tdv" rowspan="4" id="tdv_change_pwd"><?php load_comp("settings/change_pwd"); ?></td>
				<td class="atd tdv" rowspan="4" id="tdv_manage_addr"></td>
				<td class="atd tdv" rowspan="4" id="tdv_manage_email"></td>
			</tr>
			<tr>
				<td class="atd atb" id="bt_manage_addr" onclick="toggle_view('manage_addr')">Manage Addresses</td>
			</tr>
			<tr>
				<td class="atd atb" id="bt_manage_email" onclick="toggle_view('manage_email')">Manage Email</td>
			</tr>
		</tbody>
	</table>
</div>
<script type="text/javascript">
function toggle_view(id) {
	let i, tds = document.getElementsByClassName("tdv");
	for (i = 0; i < tds.length; i++) {
		tds[i].style.display = "none";
		gid("bt_" + tds[i].id.substr(4)).style["background-color"] = "";
	}
	let t = document.getElementById("tdv_" + id);
	t.style.display = "";
	gid("bt_" + id).style["background-color"] = "#f7f194";
}
toggle_view("change_pwd");
</script>
</body>
</html>
