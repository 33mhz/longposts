<?php

if ($longpost = $app->getPost($post_id,$params=array('include_annotations'=>1))) {
    $is_longpost = false;
    foreach ($longpost['annotations'] as $annotation) {
        if ($annotation['type'] == 'net.jazzychad.adnblog.post') {
            $is_longpost = true;

            $page_title = $annotation['value']['title'].' Long Posts';
            require_once 'stuff/header.php';

            echo '
            
            <div class="single-article">
            
            <h2 class="title">'.$annotation['value']['title'].'</h2>
            <div class="body">'.$Parsedown->text($annotation['value']['body']).'</div>
            
            <p>'.$longpost['num_replies'].' Replies, '.$longpost['num_reposts'].' Reposts, '.$longpost['num_stars'].' Stars</p>
            
            ';
            
            // Retrieve replies
            if ($thread = $app->getPostReplies($post_id,$params = array('count'=>200))) {
                array_pop($thread);
                $thread = array_reverse($thread,true);
                
                echo '<h2>Discussion (View on <a href="http://treeview.us/home/thread/'.$longpost['id'].'#a'.$longpost['id'].'">TreeView</a>)</h2>';
                
                foreach ($thread as $reply) {
                    echo '
                    
                    <div class="reply">
                        <div class="reply-avatar"><img src="'.$reply['user']['avatar_image']['url'].'?w=45&h=45" width="45" height="45" /></div>
                        
                        <div class="reply-text">
                            <span class="reply-username">@'.$reply['user']['username'].'</span> '.$reply['html'].'
                        </div>
                    </div>
                    
                    ';
                }
            }
            
            echo '</div>';
        }
    }
    
    if (!$is_longpost) {
        // set notification
        
        // redirect to index
        header(URL);
    }
} else {
    $page_title = 'Long Posts';
    require_once 'stuff/header.php';
    
    echo 'Could not retrieve post '.$post_id;
}

require_once 'stuff/footer.php';

?>