<?php

require __DIR__."/../init.php";

if (has_login_sess()) {
	$pdo = pdo();
	db_logout_user($pdo, $g_user_id, $g_sess_id);
}

sess_destroy();
header("Location: index.php?ref=logout");
