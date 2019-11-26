<?php

// if user found
if ($user = $app->getUser($page_key[1])) {

	$page_key[1] = htmlentities($page_key[1], ENT_QUOTES);

    $page_title = 'Long posts by ' . $page_key[1];
    $rss_url = 'https://api.pnut.io/v0/feed/rss/channels/search?channel_types=st.longpo.longpost&order=activity&owner_id=' . $user['id'];

    require_once 'header.php';
    
    // search for posts
    if (isset($_GET['post'])) {
		$longposts = $app->searchPosts(['raw_types'=>['nl.chimpnut.blog.post'],'include_post_raw'=>1,'creator_id'=>$user['id']]);
	} else {
		$longposts = $app->searchChannels(['channel_types'=>'st.longpo.longpost','include_recent_message'=>1,'include_channel_raw'=>1,'include_message_raw'=>1,'is_public'=>1,'owner_id'=>$user['id'],'order'=>'activity']);
	}
    
    // function sortByOrder($a, $b) {
    //     return $b['id'] - $a['id'];
    // }
    // usort($longposts, "sortByOrder");

	if (isset($_GET['post'])) {
		echo '<p style="float:right"><a href="/' . $page_key[1] . '">Show blog posts</a></p>';
	} else {
		echo '<p style="float:right"><a href="?post=1">Show Global longposts</a></p>';
	}

    // User byline
    echo author($user);
    
	if (isset($_GET['post'])) {
		// display posts
		foreach($longposts as $longpost) {
			longpost_p_preview($longpost,0);
		}
	} else {
		// display posts
		foreach($longposts as $longpost) {
			longpost_preview($longpost,0);
		}
	}
    
    if (!$longposts) {
        echo '<p>This user has not published any Long posts.</p>';
    }
    
} else {
    echo '<p>No user found</p>';
}
