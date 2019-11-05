<?php

if (isset($page_key[2])) {
    $page_title = 'Long posts &ndash; Category '.$page_key[2];
    require_once 'header.php';

    // get drafts
    // $longposts = $app->searchChannels($params = array('type'=>'net.longposts.longpost','tags'=>$page_key[2],'is_private'=>0,'include_recent_message'=>1,'include_annotations'=>1), $query='', $order='id');
    // get list of posts by category
    $channel_ids = get_category_ids($page_key[2]);
    
    echo '<h2>Category: <i>'.$page_key[2].'</i></h2>';
    
    if ($channel_ids) {
      // get posts by category
      $longposts = $app->getChannels($channel_ids, ['include_recent_message'=>1,'include_channel_raw'=>1,'include_message_raw'=>1]);

      foreach($longposts as $longpost) {
        
        echo '
        
        <div id="post-'.$longpost['id'].'" class="article">
            <h3><a href="/'.$longpost['id'].'">'.$longpost['raw'][0]['value']['title'].'</a></h3>
            <p>'.($longpost['recent_message']['content']['html'] ?? 'DELETED').'</p>
        </div>
        
        ';
      }
    } else {
      echo '
          
      <p>No posts in this category.</p>
          
      ';
    }
} else {
    
}

require_once 'footer.php';
