<?php

// display index
$page_title = 'Longposts';
require_once 'stuff/header.php';

// search for posts
//$longposts = $app->searchPosts($params = array('annotation_types'=>'net.jazzychad.adnblog.post','include_annotations'=>1), $query='', $order='default');
// search for channels
$longposts = $app->searchChannels($params = array('type'=>'net.longposts.longpost','include_recent_message'=>1,'include_annotations'=>1,'is_public'=>1), $query='', $order='activity');

krsort($longposts);

foreach ($longposts as $longpost) {
    longpost_preview($longpost,1);
}

if (!$longposts) {
    echo '<p>No public posts.</p>';
}

?>