<?php

if (!defined("__INIT__")):
define("__INIT__", 1);

date_default_timezone_set("UTC");

require __DIR__."/config.php";
require __DIR__."/src/helpers.php";

sess_start();

endif;
