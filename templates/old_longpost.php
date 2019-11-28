<?php

try {
    $longpost = $app->getPost($post_id, ['include_post_raw'=>1]);

    // Markdown parser
    $Parsedown = new ParsedownExtra();
    $Parsedown->setSafeMode(true);
    
    $is_longpost = false;

    foreach ($longpost['raw'] as $raw) {
        if ($raw['type'] === 'nl.chimpnut.blog.post') {

            $is_longpost = true;
            $single_page = true;

            $page_title = htmlentities((empty($raw['value']['title']) ? (substr($raw['value']['body'], 0, 42) . '…') : $raw['value']['title']), ENT_QUOTES) . ' &ndash; Long posts';
            $page_description = substr($raw['value']['body'], 0, 256) . '…';

		if (empty($raw['value']['title'])) {
			$title = strftime('%Y-%m-%d', strtotime($longpost['created_at']));
		} else {
			$title = htmlentities($raw['value']['title'], ENT_QUOTES);
		}

            require_once 'header.php';
            
            echo '
            
            <div id="post-'.$longpost['id'].'">

            <h2 class="title">'.$title.'</h2>';

            brief_author($longpost, true);

            echo '
            <div class="body">'.$Parsedown->text($raw['value']['body']).'</div>
            
            <p>Written with <a href="' . $longpost['source']['link'] . '">' . $longpost['source']['name'] . '</a>.</p>

            <p><b>Activity:</b> '.$longpost['counts']['replies'].' Repl' . ($longpost['counts']['replies'] == 1 ? 'y' : 'ies') . ', ' . $longpost['counts']['reposts'].' Repost' . ($longpost['counts']['reposts'] == 1 ? '' : 's') . ', ' . $longpost['counts']['bookmarks'].' Bookmark' . ($longpost['counts']['bookmarks'] == 1 ? '' : 's') . '</p>
            
            ';
            /*<p><strong>Tagged</strong> ';
            
            foreach ($longpost['entities']['hashtags'] as $tag) {
                echo '<a href="https://alpha.app.net/hashtags/'.$tag['name'].'">#'.$tag['name'].'</a> ';
            }*/
            
            // Retrieve replies
            if ($thread = $app->getPostThread($post_id, ['count'=>200,'include_deleted'=>0])) {
                array_pop($thread);
                $thread = array_reverse($thread,true);
                
                echo '<h2>Discussion</h2>
                
                <p><a href="https://beta.pnut.io/posts/'.$longpost['id'].'">View on Beta</a></p>';
                
                foreach ($thread as $reply) {
                    reply_content($reply);
                }
            }
            echo '</div>';
            
            break;
        }
    }
    
    require_once 'footer.php';
    
    if (!$is_longpost) {
        // set notification
        
        // redirect to index
        header('Location: '.URL);
    }
} catch (Exception $e) {
    header('location: '.URL);
}
