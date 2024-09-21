<?php

require __DIR__."/../init.php";

if (!has_login_sess())
	require __DIR__."/login.php";
else
	require __DIR__."/home.php";
