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
            if ($annotation['type'] == 'net.jazzychad.adnblog.post' && isset($annotation['value']['title']) && !empty($annotation['value']['title'])) {
                if (isset($longpost['user']['name'])) {
                    $name = $longpost['user']['name'];
                } else {
                    $name = '@'.$longpost['user']['username'];
                }
                
                // Make a random guess at reading speed and don't even consider wordage
                $body_by_word = preg_split('/\s+/', $annotation['value']['body']);
                $readingTime = ceil(count($body_by_word) / 175);
                
                // Cut previews after a handful of words
                $body_preview = '';
                $preview_word_count = min(count($body_by_word),70)-1;
                for ($n = 0; $n < $preview_word_count; $n++) {
                    $body_preview .= ' '.$body_by_word[$n];
                }
                
                // parse markdown
                $body_preview = $Parsedown->text($body_preview.'…');
                
                echo '
                
                <div class="article">
                    <div class="meta-top">
                        <a class="author-name" href="'.$longpost['user']['canonical_url'].'" target="_blank"><img class="author-avatar" src="'.$longpost['user']['avatar_image']['url'].'?w=45&h=45" title="@'.$longpost['user']['username'].'"/></a>
                        <p class="author-name"><a class="author-name" href="'.$longpost['user']['canonical_url'].'" target="_blank">'.$name.'</a></p>
                        <p class="author-permalink" title="'.$longpost['created_at'].'"><a class="author-tstamp" href="'.$longpost['canonical_url'].'">'.$longpost['created_at'].'</a></p>
                    </div>
                    <h2 class="title"><a href="'.URL.$longpost['id'].'">'.$annotation['value']['title'].'</a></h2>
                    <div class="body">'.$body_preview.'</div>
                    
                    <div class="meta-bottom"><a href="'.URL.$longpost['id'].'" class="article-more">Continue reading</a> · <span class="article-reading-time">'.$readingTime.' min read</span></div>
                </div>
                
                ';
            }
        }
    }
    
    // format dates with Moment.js
    echo '<script>
    $(document).ready(function() {
        $(".author-tstamp").each(function(i) {
            this.innerHTML = moment(this.innerHTML).fromNow();
        });
    });
    </script>';
    
    // Close tags
    require_once 'stuff/footer.php';
}

?>