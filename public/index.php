<?php

require_once '../config.php';
require_once '../AppDotNet.php';
require_once '../functions.php';
// try connecting to the ADN API
if (!defined('API_ID') || !API_ID) {
	throw new Exception('No ADN API ID specified');
}
if (!defined('API_SECRET') || !API_SECRET) {
	throw new Exception('No ADN API secret specified');
}
$app = new AppDotNet(API_ID,API_SECRET);
$token = $app->getAppAccessToken();

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