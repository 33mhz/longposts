<?php

if (isset($page_key[2])) {
    $page_title = 'Lp · Category '.$key;
    require_once 'stuff/header.php';

    // get drafts
    $longposts = $app->searchChannels($params = array('type'=>'net.longposts.longpost','tags'=>$page_key[2],'is_private'=>0,'include_recent_message'=>1,'include_annotations'=>1), $query='', $order='id');


    echo '<h2>Category: '.$page_key[2].'</h2>';
    foreach($longposts as $longpost) {
        
        echo '
        
        <div id="post-'.$longpost['id'].'" class="article">
            <h3><a href="'.URL.$longpost['id'].'">'.$longpost['annotations'][0]['value']['title'].'</a></h3>
            <p>'.$longpost['recent_message']['html'].'</p>
        </div>
        
        ';
    }

    if (count($longposts) == 0) {
        echo '
            
        <p>No posts in this category.</p>
            
        ';
    }
} else {
    
}

require_once 'stuff/footer.php';
?>