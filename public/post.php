<?php

if ($longpost = $app->getPost($post_id,$params=array('include_annotations'=>1))) {
    // Markdown parser
    require_once 'stuff/Parsedown.php';
    $Parsedown = new Parsedown();
    
    $is_longpost = false;
    foreach ($longpost['annotations'] as $annotation) {
        if ($annotation['type'] == 'net.jazzychad.adnblog.post') {
            $is_longpost = true;

            $page_title = $annotation['value']['title'].' · LP';
            require_once 'stuff/header.php';
            
            echo '
            
            <div id="post-'.$longpost['id'].'" class="single-article">
                
            ';
            echo brief_author($longpost);
            echo '

            <h2 class="title">'.$annotation['value']['title'].'</h2>
            <div class="body">'.$Parsedown->text($annotation['value']['body']).'</div>
            
            <p><strong>Activity</strong> '.$longpost['num_replies'].' Replies, '.$longpost['num_reposts'].' Reposts, '.$longpost['num_stars'].' Stars</p>
            
            ';
            /*<p><strong>Tagged</strong> ';
            
            foreach ($longpost['entities']['hashtags'] as $tag) {
                echo '<a href="https://alpha.app.net/hashtags/'.$tag['name'].'">#'.$tag['name'].'</a> ';
            }*/
            
            // Retrieve replies
            if ($thread = $app->getPostReplies($post_id,$params = array('count'=>200))) {
                array_pop($thread);
                $thread = array_reverse($thread,true);
                
                echo '<h2>Discussion</h2>
                
                <p>View on <a href="http://treeview.us/home/thread/'.$longpost['id'].'#a'.$longpost['id'].'" target="_blank">TreeView <i class="fa fa-external-link"></i></a></p>';
                
                foreach ($thread as $reply) {
                    echo '
                    
                    <div class="reply">
                        <div class="reply-author">
                            <a href="'.$reply['user']['canonical_url'].'" target="_blank" title="@'.$reply['user']['username'].'"><img  src="'.$reply['user']['avatar_image']['url'].'?w=45&h=45" width="45" height="45" class="reply-avatar" /> <span class="reply-username">@'.$reply['user']['username'].'</span></a>
                        </div>
                        
                        <div class="reply-text">
                            '.$reply['html'].'
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
        header('Location: '.URL);
    }
} else {
    $page_title = 'Long Posts';
    require_once 'stuff/header.php';
    
    echo 'Could not retrieve post '.$post_id;
}

require_once 'stuff/footer.php';

?>