<?php

require_once '../config.php';
require_once '../AppDotNet.php';
require_once '../EZAppDotNet.php';
require_once '../functions.php';

// checking if the 'Remember me' checkbox was clicked
if (isset($_GET['rem'])) {
	session_start();
	if ($_GET['rem']=='1') {
		$_SESSION['rem']=1;
	} else {
		unset($_SESSION['rem']);
	}
	header('Location: '.URL);
}

$app = new EZAppDotNet();
$login_url = $app->getAuthUrl();

// if not logged in as user, use app for calls
if (isset($_SESSION['logged_in'])) {
    $app->getSession();
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = $app->getUser();
    }
} else {
    $app = new AppDotNet(API_ID,API_SECRET);
    $token = $app->getAppAccessToken();
    unset($_SESSION['user']);
}

// get post ID from URL
$post_id = basename("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

// what page are we viewing?
if ($post_id !== 'longposts.net') {
    if (is_numeric($post_id)) {
        // display post
        require_once 'post.php';
    } else if (substr($post_id,0,1) == '@') {
        // display a user's posts
        require_once 'user.php';
    }
} else {
    // display frontpage
    require_once 'frontpage.php';
}

// Close tags
require_once 'stuff/footer.php';

?>