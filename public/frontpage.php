<?php

// display index
$page_title = 'Longposts';
require_once 'stuff/header.php';

// search for posts
$longposts = $app->searchPosts($params = array('annotation_types'=>'net.jazzychad.adnblog.post','include_annotations'=>1), $query='', $order='default');

foreach ($longposts as $longpost) {
    longpost_preview($longpost,1);
}

?>