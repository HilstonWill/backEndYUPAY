<?php
define('DB_SERVER', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', intval(getenv('DB_PORT') ?: 3306));
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'bd_yupay');

?>
