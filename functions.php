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
    if (isset($longpost['user']['name'])) {
        $name = $longpost['user']['name'];
    } else {
        $name = '@'.$longpost['user']['username'];
    }
    
    echo '
    <div class="meta-top">
        <p class="author-toggle"><a class="author-button" href="javascript:toggle_description(\''.$longpost['id'].'\')"><i class="fa fa-chevron-circle-down"></i></a></p>
        <a href="'.URL.'@'.$longpost['user']['username'].'"><img class="author-avatar" src="'.$longpost['user']['avatar_image']['url'].'?w=45&h=45" title="@'.$longpost['user']['username'].'"/>
        <span class="author-name">'.$name.'</span></a>
        <p class="author-permalink" title="'.$longpost['created_at'].'"><a class="author-tstamp" href="'.$longpost['canonical_url'].'">'.$longpost['created_at'].'</a></p>
        
        <div class="author-description">
            '.$longpost['user']['description']['html'].'
            <p><a class="author-name" href="'.$longpost['user']['canonical_url'].'" target="_blank">@'.$longpost['user']['username'].' on App.net <i class="fa fa-external-link"></i></a></p>
        </div>
    </div>
    ';
}

function longpost_preview($longpost,$include_author) {
    // Markdown parser
    require_once 'stuff/Parsedown.php';
    $Parsedown = new Parsedown();
    
    $is_longpost = false;
    foreach ($longpost['annotations'] as $annotation) {
        if ($annotation['type'] == 'net.jazzychad.adnblog.post' && isset($annotation['value']['title']) && !empty($annotation['value']['title'])) {
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
            $body_preview = $Parsedown->text($body_preview.'&#8230;');
            
            // discussion indicator
            if ($longpost['num_replies'] == '0') {
                $discussion = '';
            } else {
                $discussion = ' · <span title="Has replies"><i class="fa fa-comments"></i></span>';
            }
            
            echo '
            
            <div class="article" id="post-'.$longpost['id'].'">
                ';
                if ($include_author) {
                    echo brief_author($longpost);
                }
                echo '
                
                <h2 class="title"><a href="'.URL.$longpost['id'].'">'.$annotation['value']['title'].'</a></h2>';
                if (!$include_author) {
                    echo '<p class="author-permalink"><a class="author-tstamp" href="'.$longpost['canonical_url'].'">'.$longpost['created_at'].'</a></p>';
                }
                echo '<div class="body">'.$body_preview.'</div>
                
                <div class="meta-bottom"><a href="'.URL.$longpost['id'].'" class="article-more">Continue reading</a> · <span class="article-reading-time">'.$readingTime.' min read</span>'.$discussion.'</div>
            </div>
            
            ';
        }
    }
}

?>