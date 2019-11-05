<?php

try {
    $longpost = $app->getChannel($page_key[1],['include_channel_raw'=>1,'include_message_raw'=>1,'include_recent_message'=>1]);

    // Markdown parser
    $Parsedown = new ParsedownExtra();

    $is_longpost = false;

    if ($longpost['type'] === 'st.longpo.longpost') {
        $is_longpost = true;
        $single_page = true;

        // views
        $views = update_views($longpost['id']);
        // categories
        if (!empty($longpost['raw'][0]['value']['category'])) {
          update_category($longpost['id'], $longpost['raw'][0]['value']['category']);
        }
        // global post
        if (!empty($longpost['raw'][0]['value']['global_post_id'])) {
            $global_post = $app->getPost($longpost['raw'][0]['value']['global_post_id']);
        }

        $page_title = $longpost['raw'][0]['value']['title'].' &ndash; Long posts';
        $title = $longpost['raw'][0]['value']['title'];
        $body = $longpost['recent_message']['raw'][0]['value']['body'];
        require_once 'header.php';

        echo '

        <div id="post-'.$longpost['id'].'">

        <h2 class="title">'.$title.'</h2>';
        
        if (!empty($longpost['acl']['full']['you'])) {
            echo '<a href="/drafts/write?id='.$longpost['id'].'" style="float:right"><button type="button">Edit</button></a>';
        }

        brief_author($longpost);

        echo '
        <div class="body">'.$Parsedown->text($body).'</div>

        ';
        // if global post is significantly older than the most recent message, count the recent_message created_at time as a more recent edit
        if ($longpost['counts']['messages'] > 1 && isset($global_post)) {
            if (strtotime($global_post['created_at']) - strtotime($longpost['recent_message']['created_at']) > 14400) {
                echo '<p class="last-edit" style="float:right;font-size:80%;font-family:sans-serif;color:#888">Last Edited <span class="tstamp">'.$longpost['recent_message']['created_at'].'</span></p>';
            }
        }

        if (!empty($longpost['raw'][0]['value']['category'])) {
            echo '<p style="float:right;font-size:80%;color:#888">Filed Under: <a href="/category/'.$longpost['raw'][0]['value']['category'].'">"'.$longpost['raw'][0]['value']['category'].'"</a></p>
            ';
        }

        if ($longpost['recent_message']['content']['entities']['tags'] || $longpost['recent_message']['content']['entities']['mentions']) {
            echo '
            <p><strong>Tags</strong> ';

            foreach ($longpost['recent_message']['content']['entities']['tags'] as $tag) {
                echo '<a href="https://pnut.io/tags/'.$tag['text'].'">#'.$tag['text'].'</a> ';
            }
            foreach ($longpost['recent_message']['content']['entities']['mentions'] as $mention) {
                echo '<a href="https://pnut.io/@'.$mention['text'].'">@'.$mention['text'].'</a> ';
            }

            echo '</p>';
        }
        echo '<p title="Approximate Views"><i>'.$views.' approximate views</i></p>';

        // Retrieve replies
        if (isset($global_post) && !empty($global_post)) {
            echo '<p><strong>Activity</strong> '.$global_post['counts']['replies'].' Replies, '.$global_post['counts']['reposts'].' Reposts, '.$global_post['counts']['bookmarks'].' Bookmarks</p>';

            if ($thread = $app->getPostThread($global_post['id'],$params = ['count'=>200,'include_deleted'=>0])) {
                array_pop($thread);
                $thread = array_reverse($thread,true);

                echo '<h2>Discussion</h2>

                <p><a href="https://beta.pnut.io/posts/'.$global_post['id'].'">View on Beta</a></p>';

                foreach ($thread as $reply) {
                    reply_content($reply);
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
} catch (Exception $e) {
    $page_title = 'Long Posts';
    require_once 'header.php';
    echo '<p><i>'.$e->getMessage().'</i></p>
    <p>No long post found for '.$page_key[1].'.</p>';
}

require_once 'footer.php';
