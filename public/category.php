<?php

if (isset($page_key[2])) {
    $page_title = 'Lp · Category '.$page_key[2];
    require_once 'stuff/header.php';

    // get drafts
    // $longposts = $app->searchChannels($params = array('type'=>'net.longposts.longpost','tags'=>$page_key[2],'is_private'=>0,'include_recent_message'=>1,'include_annotations'=>1), $query='', $order='id');
    // get list of posts by category
    $channel_ids = get_category_ids($page_key[2]);
    
    echo '<h2>Category: '.$page_key[2].'</h2>';
    
    if ($channel_ids) {
      // get posts by category
      $longposts = $app->getChannels($channel_ids, array('include_recent_message'=>1,'include_annotations'=>1));

      foreach($longposts as $longpost) {
        
        echo '
        
        <div id="post-'.$longpost['id'].'" class="article">
            <h3><a href="'.URL.$longpost['id'].'">'.$longpost['annotations'][0]['value']['title'].'</a></h3>
            <p>'.$longpost['recent_message']['html'].'</p>
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

require_once 'stuff/footer.php';
?>