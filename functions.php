<?php

function author($user) {
    if (isset($user['name'])) {
        $name = $user['name'];
    } else {
        $name = '@'.$user['username'];
    }
    
    echo '
    <a href="'.URL.'@'.$user['username'].'"><img class="author-avatar" src="'.$user['avatar_image']['url'].'?w=85&h=85" title="@'.$user['username'].'" style="width:85px;height:85px"/>
    <span class="author-name" style="font-size:150%">'.$name.'</span></a>
    
    <div class="author-description" style="height:auto;border-bottom:1px dotted #ccc;padding:1.2em;margin-bottom:1em">
        '.$user['description']['html'].'
        <p><a class="author-name" href="'.$user['canonical_url'].'" target="_blank">@'.$user['username'].' on App.net <i class="fa fa-external-link"></i></a></p>
    </div>
    ';
}

function brief_author($longpost) {
    if (isset($longpost['owner']['name'])) {
        $name = $longpost['owner']['name'];
    } else {
        $name = '@'.$longpost['owner']['username'];
    }
    
    echo '
    <div class="meta-top">
        <p class="author-toggle"><a class="author-button" href="javascript:toggle_description(\''.$longpost['id'].'\')"><i class="fa fa-chevron-circle-down"></i></a></p>
        <a href="'.URL.'@'.$longpost['owner']['username'].'"><img class="author-avatar" src="'.$longpost['owner']['avatar_image']['url'].'?w=45&h=45" title="@'.$longpost['owner']['username'].'"/>
        <span class="author-name">'.$name.'</span></a>
        <p class="author-permalink" title="'.$longpost['recent_message']['created_at'].'"><a class="author-tstamp tstamp" href="'.URL.$longpost['id'].'">'.$longpost['recent_message']['created_at'].'</a></p>
        
        <div class="author-description">
            '.$longpost['owner']['description']['html'].'
            <p><a class="author-name" href="'.$longpost['owner']['canonical_url'].'" target="_blank">@'.$longpost['owner']['username'].' on App.net <i class="fa fa-external-link"></i></a></p>
        </div>
    </div>
    ';
}

function longpost_preview($longpost,$include_author) {
    // Connect to db
    $db = new PDO(DBHOST, DBUSER, DBPASS);
    $sth = $db->prepare("SELECT COUNT(*) FROM views WHERE post_id = ".$longpost['id']);
    $sth->execute();
    $views = $sth->fetch()[0];
    
    // Markdown parser
    require_once 'public/stuff/Parsedown.php';
    $Parsedown = new Parsedown();
    
    // Make a random guess at reading speed and don't even consider wordage
    $body_by_word = preg_split('/\s+/', $longpost['recent_message']['annotations'][0]['value']['body']);
    $readingTime = ceil(count($body_by_word) / 175);
    
    // Cut previews after a handful of words
    if (isset($longpost['recent_message']['html']) && !empty($longpost['recent_message']['html'])) {
        $body_preview = $longpost['recent_message']['html'];
    } else {
        $body_preview = '';
        $preview_word_count = min(count($body_by_word),70)-1;
        for ($n = 0; $n < $preview_word_count; $n++) {
            $body_preview .= ' '.$body_by_word[$n];
        }
        
        // parse markdown
        $body_preview = $Parsedown->text($body_preview.'&#8230;');
    }
    
    // retrieve global post
    /*if (isset($longpost['annotations'][0]['value']['global_post_id'])) {
        $global_post = $app->getPost($longpost['annotations'][0]['value']['global_post_id']);
        
        // discussion indicator
        if ($global_post['num_replies'] == '0') {
            $discussion = ' · <i class="fa fa-eye"></i>'.$views;
        } else {
            $discussion = ' · <i class="fa fa-eye"></i>'.$views.' · <span title="Has replies"><i class="fa fa-comments"></i></span>';
        }
    } else {*/
        $discussion = ' · <i class="fa fa-eye"></i>'.$views;
    //}
    
    echo '
    
    <div class="article" id="post-'.$longpost['id'].'">
        ';
        if ($include_author) {
            echo brief_author($longpost);
        }
        echo '
        
        <h2 class="title"><a href="'.URL.$longpost['id'].'">'.$longpost['annotations'][0]['value']['title'].'</a></h2>';
        if (!$include_author) {
            echo '<p class="author-permalink"><a class="author-tstamp tstamp" href="'.URL.$longpost['id'].'">'.$longpost['recent_message']['created_at'].'</a></p>';
        }
        echo '<div class="body">'.$body_preview.'</div>
        
        <div class="meta-bottom"><a href="'.URL.$longpost['id'].'" class="article-more">Continue reading</a> · <span class="article-reading-time">'.$readingTime.' min read</span>'.$discussion.'</div>
    </div>
    
    ';
}

?>