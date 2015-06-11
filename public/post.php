<?php

if ($longpost = $app->getPost($post_id,$params=array('include_annotations'=>1))) {
    $is_longpost = false;
    foreach ($longpost['annotations'] as $annotation) {
        if ($annotation['type'] == 'net.jazzychad.adnblog.post') {
            $is_longpost = true;

            $page_title = $annotation['value']['title'].' Long Posts';
            require_once 'stuff/header.php';
            
            if (isset($longpost['user']['name'])) {
                $name = $longpost['user']['name'];
            } else {
                $name = '@'.$longpost['user']['username'];
            }
            
            echo '
            
            <div class="single-article">
                
            <div class="meta-top">
                <p id="description-button"><a href="javascript:description_open()"><i class="fa fa-chevron-circle-down"></i></a></p>
                <a href="javascript:description_open()"><img class="author-avatar" src="'.$longpost['user']['avatar_image']['url'].'?w=45&h=45" title="@'.$longpost['user']['username'].'"/>
                <span class="author-name">'.$name.'</span></a>
                <p class="author-permalink" title="'.$longpost['created_at'].'"><a class="author-tstamp" href="'.$longpost['canonical_url'].'">'.$longpost['created_at'].'</a></p>
                <div id="author-description">'.$longpost['user']['description']['html'].'
                <p><a class="author-name" href="'.$longpost['user']['canonical_url'].'" target="_blank">@'.$longpost['user']['username'].' on App.net <i class="fa fa-external-link"></i></a></p></div>
            </div>

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

            // format dates with Moment.js
            echo '</div>

            <script>
            $(document).ready(function() {
                $(".author-tstamp").each(function(i) {
                    this.innerHTML = moment(this.innerHTML).fromNow();
                });
            });
            // expand author description
            function description_open() {
                if ($(".fa-chevron-circle-down").length) {
                    $(".fa-chevron-circle-down").replaceWith(\'<i class="fa fa-chevron-circle-up"></i>\');
                } else {
                    $(".fa-chevron-circle-up").replaceWith(\'<i class="fa fa-chevron-circle-down"></i>\');
                }
                
                $("#author-description").toggleClass("author-open");
            }
            </script>
            
            ';
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