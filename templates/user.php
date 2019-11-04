<?php

// if user found
if ($user = $app->getUser($page_key[1])) {

    $page_title = 'Long posts by '.$page_key[1];
    $heading = 'Long posts';

    require_once 'header.php';
    
    // search for posts
    $longposts = $app->searchChannels(['channel_types'=>'st.longpo.longpost','include_recent_message'=>1,'include_channel_raw'=>1,'include_message_raw'=>1,'is_public'=>1,'creator_id'=>$user['id'],'order'=>'activity']);
    // $longposts = $app->searchPosts($params = ['raw_types'=>'nl.chimpnut.blog.post','include_post_raw'=>1,'creator_id'=>$user['id']], $query='', $order='default');
    
    // function sortByOrder($a, $b) {
    //     return $b['id'] - $a['id'];
    // }
    // usort($longposts, "sortByOrder");
    
    // User byline
    echo author($user);
    
    // display posts
    foreach($longposts as $longpost) {
        longpost_preview($longpost,0);
    }
    
    if (!$longposts) {
        echo '<p>This user has not published any Long posts.</p>';
    }
    
} else {
    echo '<p>No user found</p>';
}
