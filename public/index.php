<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// checking if the 'Remember me' checkbox was clicked
if (isset($_GET['rem'])) {
	session_start();
	if ($_GET['rem'] == '1') {
		$_SESSION['rem'] = 1;
	} else {
		unset($_SESSION['rem']);
	}
	header('Location: '.URL);
}

$app = new phpnut\ezphpnut();
$login_url = $app->getAuthUrl();

// if not logged in as user, use app for calls
if (isset($_SESSION['logged_in'])) {
    $app->getSession();
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = $app->getUser();
    }
} else {
    // $app = new phpnut\phpnut(getenv('PNUT_CLIENT_ID'), getenv('PNUT_CLIENT_SECRET'));
    // $token = $app->getAppAccessToken();
    unset($_SESSION['user']);
}

// get post ID from URL
$post_id = basename("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
$url = $_SERVER['REQUEST_URI'];
$url = parse_url($_SERVER['REQUEST_URI']);
$page_key = explode('/',$url['path']);

// what page are we viewing?
if (!empty($page_key[1])) {
    if (is_numeric($page_key[1])) {
        // display post
        require_once '../templates/post.php';
    } elseif (substr($page_key[1],0,1) == '@') {
        if (isset($page_key[2])) {
            // display post
            require_once '../templates/post.php';
        } else {
            // display a user's posts
            require_once '../templates/user.php';
        }
    } else if ($page_key[1] == 'category') {
        // category
        require_once '../templates/category.php';
    } else if ($page_key[1] == 'p') {
        // OLD longposts
        require_once '../templates/old_longpost.php';
    }
} else {
    // display frontpage
    require_once '../templates/frontpage.php';
}

// Close tags
require_once '../templates/footer.php';
