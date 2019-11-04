<?php

try {
    $longpost = $app->getPost($post_id,$params=['include_post_raw'=>1]);

    // Markdown parser
    $Parsedown = new ParsedownExtra();
    
    $is_longpost = false;
    foreach ($longpost['raw'] as $annotation) {
        if ($annotation['type'] === 'nl.chimpnut.blog.post') {

            $is_longpost = true;

            $page_title = $annotation['value']['title'].' &ndash; Long post';
            require_once 'header.php';
            
            echo '
            
            <div id="post-'.$longpost['id'].'" class="single-article">

            <h2 class="title">'.$annotation['value']['title'].'</h2>';

            old_brief_author($longpost);

            echo '
            <div class="body">'.$Parsedown->text($annotation['value']['body']).'</div>
            
            <p><strong>Activity</strong> '.$longpost['counts']['replies'].' Replies, '.$longpost['counts']['reposts'].' Reposts, '.$longpost['counts']['bookmarks'].' Bookmarks</p>
            
            ';
            /*<p><strong>Tagged</strong> ';
            
            foreach ($longpost['entities']['hashtags'] as $tag) {
                echo '<a href="https://alpha.app.net/hashtags/'.$tag['name'].'">#'.$tag['name'].'</a> ';
            }*/
            
            // Retrieve replies
            if ($thread = $app->getPostThread($post_id,$params = ['count'=>200])) {
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
