<?php

define('PNUT_REDIRECT_URI', getenv('PNUT_REDIRECT_URI'));
define('PNUT_APP_SCOPE', getenv('PNUT_APP_SCOPE'));
define('PNUT_CLIENT_ID', getenv('PNUT_CLIENT_ID'));
define('PNUT_CLIENT_SECRET', getenv('PNUT_CLIENT_SECRET'));
define('DBUSER', getenv('DB_USER'));
define('DBPASS', getenv('DB_PASS'));
define('DBHOST', getenv('DB_HOST'));
define('URL', getenv('URL'));

error_reporting(0);
ini_set('display_errors', 0);
