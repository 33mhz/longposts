<?php

require_once __DIR__ . '/../../vendor/autoload.php';

\Dotenv\Dotenv::create(__DIR__.'/../..')->load();

require_once __DIR__ . '/../../config.php';

$app = new phpnut\ezphpnut();

$_SESSION['logged_in'] = true;

// log in user
// if 'Remember me' was checked...
if (isset($_SESSION['rem'])) {
	// pass 1 into setSession in order
	// to set a cookie and session
	$token = $app->setSession(1);
} else {
	// otherwise just set session
	$token = $app->setSession();
}
// redirect user after logging in
header('Location: '.URL.substr($_SESSION['last_url'],1));
