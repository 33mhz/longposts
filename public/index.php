<?php

require_once '../config.php';
require_once '../AppDotNet.php';
require_once 'stuff/Parsedown.php';
// Try connecting to the ADN API
if (!defined('API_ID') || !API_ID) {
	throw new Exception('No ADN API ID specified');
}
if (!defined('API_SECRET') || !API_SECRET) {
	throw new Exception('No ADN API secret specified');
}
$app = new AppDotNet(API_ID,API_SECRET);
$token = $app->getAppAccessToken();

// Markdown parser
$Parsedown = new Parsedown();

// Get post ID from URL
$post_id = basename("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
    // ADD check if digits

if ($post_id !== 'longposts.net') {
    // Display post
    require_once 'post.php';
} else {
    $page_title = 'Long Posts';
    require_once 'stuff/header.php';
    
    // search for posts
    $longposts = $app->searchPosts($params = array('annotation_types'=>'net.jazzychad.adnblog.post','include_annotations'=>1), $query='', $order='default');
    
    foreach ($longposts as $longpost) {
        $is_longpost = false;
        foreach ($longpost['annotations'] as $annotation) {
            if ($annotation['type'] == 'net.jazzychad.adnblog.post') {
                echo '
                
                <h2 class="title"><a href="'.URL.$longpost['id'].'">'.$annotation['value']['title'].'</a></h2>
                <div class="body">'.$Parsedown->text(substr($annotation['value']['body'],0,400)).'</div>';
                if (isset($annotation['value']['tstamp'])) {
                    echo '<h4 class="tstamp">'.$annotation['value']['tstamp'].'</h4>';
                }
            }
        }
    }
    
    require_once 'stuff/footer.php';
}

?>