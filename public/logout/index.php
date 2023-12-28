<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

require_once __DIR__ . '/../../config.php';

$app = new phpnut\ezphpnut();

unset($_SESSION['logged_in']);

// log out user
$app->deleteSession();

// redirect user after logging out
header('Location: '.URL.substr($_SESSION['last_url'],1));
