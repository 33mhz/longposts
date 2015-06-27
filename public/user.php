<?php

// if user found
if ($user = $app->getUser($page_key[1])) {
    $page_title = 'Longposts by '.$page_key[1];
    require_once 'stuff/header.php';
    
    // search for posts
    $longposts = $app->searchChannels($params = array('type'=>'net.longposts.longpost','include_recent_message'=>1,'include_annotations'=>1,'is_public'=>1,'creator_id'=>$user['id']), $query='', $order='activity');
    //$longposts = $app->searchPosts($params = array('annotation_types'=>'net.jazzychad.adnblog.post','include_annotations'=>1,'creator_id'=>$user['id']), $query='', $order='default');
    
    function sortByOrder($a, $b) {
        return $b['id'] - $a['id'];
    }
    usort($longposts, "sortByOrder");
    
    // User byline
    echo author($user);
    
    // display posts
    foreach($longposts as $longpost) {
        longpost_preview($longpost,0);
    }
    
    if (!$longposts) {
        echo '<p>This user has not published any Longposts.</p>';
    }
    
} else {
    echo '<p>No user found</p>';
}