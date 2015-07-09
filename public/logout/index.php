<?php
require_once '../../EZAppDotNet.php';
$app = new EZAppDotNet();

unset($_SESSION['logged_in']);

// log out user
$app->deleteSession();

// redirect user after logging out
header('Location: '.URL.substr($_SESSION['last_url'],1));
?>