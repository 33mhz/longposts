<?php

// display index
$page_title = 'Long posts';
$rss_url = 'https://api.pnut.io/v0/feed/rss/channels/search?channel_types=st.longpo.longpost&order=activity';

require_once 'header.php';

// search for posts
//$longposts = $app->searchPosts($params = array('annotation_types'=>'net.jazzychad.adnblog.post','include_annotations'=>1), $query='', $order='default');
// search for channels
$longposts = $app->searchChannels(['channel_types'=>'st.longpo.longpost','include_recent_message'=>1,'include_channel_raw'=>1,'include_message_raw'=>1,'is_public'=>1,'order'=>'activity']);

//function sortByOrder($a, $b) {
//	return $b['id'] - $a['id'];
//}
//usort($longposts, "sortByOrder");

foreach ($longposts as $longpost) {
	longpost_preview($longpost,1);
}

if (!$longposts) {
	echo '<p>No public posts.</p>';
}
