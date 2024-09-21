<?php

require __DIR__."/../init.php";

if (!has_login_sess()) {
	header("Location: login.php?ref=home");
	exit(0);
}

?><!DOCTYPE html>
<html>
<head>
<title><?= e(APP_NAME); ?> | Home</title>
<link rel="stylesheet" type="text/css" href="<?= ASSETS_URL_PATH ?>/css/common.css" />
<script type="text/javascript" src="<?= ASSETS_URL_PATH ?>/js/common.js"></script>
</head>
<body>
<?php load_comp("navbar", ["active" => "profile"]); ?>
<h1>Welcome <?= e($g_user["fullname"]); ?>!</h1>
</body>
</html>
