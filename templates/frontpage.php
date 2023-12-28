<?php

// display index
$page_title = 'Long posts';
$rss_url = 'https://api.pnut.io/v0/feed/rss/channels/search?channel_types=st.longpo.longpost&order=activity';

require_once 'header.php';

if (empty($_SESSION['logged_in'])) {
	echo '<p style="text-align:center;font-weight:bold;font-size: 1.1em;margin-bottom:2em;margin-top:0;padding:.5em;"><a href="' . $login_url . '">Log in using Pnut</a> to create your own blog!</p>';
}

// search for channels
// $longposts = $app->searchChannels(['channel_types'=>'st.longpo.longpost','include_recent_message'=>1,'include_channel_raw'=>1,'include_message_raw'=>1,'is_public'=>1,'order'=>'activity']);

//function sortByOrder($a, $b) {
//	return $b['id'] - $a['id'];
//}
//usort($longposts, "sortByOrder");

// foreach ($longposts as $longpost) {
// 	longpost_preview($longpost, true);
// }

// if (!$longposts) {
// 	echo '<p>No public posts.</p>';
// }

?>

<div class="homepage-columns">
    <div>
        <h2>Make your own blog</h2>

        <p>Longpo.st will host your simple blog on <a href="https://pnut.io">Pnut.io</a>, and order your posts by "when your post was last updated" (not a <code>published</code> date).</p>

        <p>Pnut &ndash; that's where your "data" is stored. You own it, but you have to join.</p>
    </div>
</div>

<div class="homepage-columns">
    <div>
        <h2>Keep it simple</h2>

        <p>You create posts in markdown, and it will save the history of every change you make to the post. If you don't want a history, you can simply delete the revisions.</p>
    </div>

    <div>
        <h2>Have fun</h2>

        <p>This is a little hackathon project from <a href="/@33MHz">@33MHz</a>, but the code is on GitHub. It uses "messages" in a separate "channel" for each blog post. Look up <a href="https://gist.github.com/33mhz/15fece3d82b928020605b53a31de090d">the details for the custom channel</a>, and the <a href="https://gist.github.com/33mhz/d25cd3c8f23b1b8be39da61db78234cb">custom messages in the channel</a>.</p>
    </div>
</div>
