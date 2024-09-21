<?php

require __DIR__."/../init.php";

sess_destroy();
header("Location: index.php?ref=logout");
