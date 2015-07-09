<?php
function getIp() {
    $ip = $_SERVER['REMOTE_ADDR'];

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $ip;
}

if ($longpost = $app->getChannel($page_key[1],$params=array('include_annotations'=>1,'include_recent_message'=>1))) {
    // Markdown parser
    require_once 'stuff/Parsedown.php';
    $Parsedown = new Parsedown();
    
    $is_longpost = false;
    if ($longpost['type'] == 'net.longposts.longpost') {
        $is_longpost = true;

        // Connect to db
        $db = new PDO(DBHOST, DBUSER, DBPASS);
        
        // get view count
        $sth = $db->prepare("SELECT COUNT(*) FROM views WHERE post_id = ".$longpost['id']);
        $sth->execute();
        $views = $sth->fetch()[0];
        $ip = str_replace(array('.'), '', getIp());
        
        $this_user_viewed = false;
        
        // tick database for another view
        if ($views > 0) {
            // get visits from this IP
            $sth = $db->prepare("SELECT * FROM views WHERE post_id = ".$longpost['id']." AND ip = ".$ip);
            $sth->execute();
            $by_ip = $sth->fetchAll();
            $by_ip_count = $sth->rowCount();
            
            if ($by_ip_count !== 0) {
                if (isset($_SESSION['logged_in'])) {
                    foreach ($by_ip as $ip_view) {
                        if ($_SESSION['user']['id'] == $ip_view['user_id']) {
                            $this_user_viewed = true;
                            break;
                        }
                    }
                } else {
                    $this_user_viewed = true;
                }
            }
        }
        
        if (!$this_user_viewed) {
            $query = $db->prepare("INSERT INTO views (post_id, ip, user_id) VALUES (:post_id, :ip, :user_id)");
            $query->execute(array(
                ':post_id' => $longpost['id'],
                ':ip' => $ip,
                ':user_id' => $_SESSION['user']['id']
            ));
            
            $views++;
        }
        

        if (isset($longpost['annotations'][0]['value']['global_post_id'])) {
            $global_post = $app->getPost($longpost['annotations'][0]['value']['global_post_id']);
        }

        $page_title = $longpost['annotations'][0]['value']['title'].' · Lp';
        $title = $longpost['annotations'][0]['value']['title'];
        $body = $longpost['recent_message']['annotations'][0]['value']['body'];
        require_once 'stuff/header.php';
        
        echo '
        
        <div id="post-'.$longpost['id'].'" class="single-article">
            
        ';
        echo brief_author($longpost);
        
        if (isset($longpost['you_can_edit'])) {
            echo '<a href="'.URL.'drafts/write?id='.$longpost['id'].'" style="float:right"><button type="button">Edit</button></a>';
        }
        echo '

        <h2 class="title">'.$title.'</h2>
        <div class="body">'.$Parsedown->text($body).'</div>
        
        ';
        // if global post is significantly older than the most recent message, count the recent_message created_at time as a more recent edit
        if ($longpost['counts']['messages'] > 1 && isset($global_post)) {
            if (strtotime($global_post['created_at']) - strtotime($longpost['recent_message']['created_at']) > 14400) {
                echo '<p class="last-edit" style="float:right;font-size:80%;font-family:sans-serif;color:#888">Last Edited <span class="tstamp">'.$longpost['recent_message']['created_at'].'</span></p>';
            }
        }
        
        echo '<p style="float:right;font-size:80%;color:#888"><i class="fa fa-archive"></i> Filed Under: <a href="'.URL.'category/'.$longpost['annotations'][0]['value']['category'].'">"'.$longpost['annotations'][0]['value']['category'].'"</a></p>
        
        
        <p><strong><i class="fa fa-tags"></i></strong> ';
        
        foreach ($longpost['recent_message']['entities']['hashtags'] as $tag) {
            echo '<a href="https://alpha.app.net/hashtags/'.$tag['name'].'">#'.$tag['name'].'</a> ';
        }
        foreach ($longpost['recent_message']['entities']['mentions'] as $mention) {
            echo '<a href="https://alpha.app.net/'.$mention['username'].'">@'.$mention['username'].'</a> ';
        }
        
        echo '<p title="Approximate Views"><i class="fa fa-eye"></i> '.$views.'</p>';
        
        // Retrieve replies
        if (isset($global_post) && !empty($global_post)) {
            echo '<p><strong>Activity</strong> '.$global_post['num_replies'].' Replies, '.$global_post['num_reposts'].' Reposts, '.$global_post['num_stars'].' Stars</p>';
            
            if ($thread = $app->getPostReplies($global_post['id'],$params = array('count'=>200))) {
                array_pop($thread);
                $thread = array_reverse($thread,true);
                
                echo '<h2>Discussion</h2>
                
                <p>View on <a href="http://treeview.us/home/thread/'.$global_post['id'].'#a'.$global_post['id'].'" target="_blank">TreeView <i class="fa fa-external-link"></i></a></p>';
                
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
            
            if (isset($_SESSION['logged_in'])) {
            echo '
            
            <div>
                <form action="reply.php" method="POST">
                <p><textarea style="width:100%" name="reply_text" maxlength="256">@'.$longpost['recent_message']['user']['username'].' </textarea></p>
                
                <p><button type="submit">Reply</button></p>
                
                <input type="hidden" name="global_post_id" value="'.$global_post['id'].'"/>
                <input type="hidden" name="longpost_id" value="'.$longpost['id'].'"/>
                </form>
            </div>
            
            ';
            } else {
                echo '
                
                <p><a href="'.$login_url.'">Log in</a> to comment.</p>
                
                ';
            }
        }
        echo '</div>';
    }
    
    if (!$is_longpost) {
        // set notification
        
        // redirect to index
        header('Location: '.URL);
    }
} else {
    $page_title = 'Long Posts';
    require_once 'stuff/header.php';
    
    echo 'Could not retrieve post '.$page_key[2];
}

require_once 'stuff/footer.php';

?>