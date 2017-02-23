<?php
set_time_limit (0);
require_once('inc/login.inc.php');
require_once('inc/db.inc.php');


// draw login box
echo $GLOBALS['oSimpleLoginSystem']->getLoginBox();


?>
