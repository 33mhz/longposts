<?php

function update_category(int $post_id, string $category) {
  // Connect to db
  $db = new PDO(DBHOST, DBUSER, DBPASS);
  
  // check if post ID has a recorded category
  $sth = $db->prepare("SELECT post_id, category FROM categories WHERE post_id = $post_id LIMIT 1");
  $sth->execute();
  $channel_exists = $sth->fetch();
  
  // if channel doesn't have record, insert
  if ($sth->rowCount() == 0) {
    $query = $db->prepare('INSERT INTO categories (post_id, category) VALUES (:post_id, :category)');
    $query->execute(array(
      ':post_id' => $post_id,
      ':category' => $category
    ));
  } elseif ($channel_exists['category'] !== $category) {
    // if channel hasn't been recorded with this category, update
    $query = $db->prepare("UPDATE categories SET category = ':category' WHERE post_id = $post_id");
	
		$query->execute([':category' => $category]);
  }
}

function get_category_ids(string $category) {
  // Connect to db
  $db = new PDO(DBHOST, DBUSER, DBPASS);
  
  // get view count
  $sth = $db->prepare("SELECT post_id FROM categories WHERE category = '$category' ORDER BY post_id DESC LIMIT 200");
  $sth->execute();
  $category_ids = $sth->fetchAll();
  
  $channel_ids = array();
  foreach($category_ids as $category_id) {
    $channel_ids[] = $category_id['post_id'];
  }
  
  return $channel_ids;
}

function getIp() {
  $ip = $_SERVER['REMOTE_ADDR'];

  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  }
  return $ip;
}

function update_views(int $post_id) {
  // Connect to db
  $db = new PDO(DBHOST, DBUSER, DBPASS);
  
  // get view count
  $sth = $db->prepare('SELECT COUNT(*) FROM views WHERE post_id = '.$post_id);
  $sth->execute();
  $views = $sth->fetch()[0];
  $ip = str_replace(array('.'), '', getIp());
  
  $this_user_viewed = false;
  
  // tick database for another view
  if ($views > 0) {
      // get visits from this IP
      $sth = $db->prepare("SELECT * FROM views WHERE post_id = $post_id AND ip = $ip");
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
      if (isset($_SESSION['logged_in'])) {
          $user_id = $_SESSION['user']['id'];
      } else {
          $user_id = Null;
      }
      
      $query = $db->prepare('INSERT INTO views (post_id, ip, user_id) VALUES (:post_id, :ip, :user_id)');
      $query->execute(array(
          ':post_id' => $post_id,
          ':ip' => $ip,
          ':user_id' => $user_id
      ));
      
      $views++;
  }
  
  return $views;
}

function author($user) {
    if (isset($user['name'])) {
        $name = $user['name'];
    } else {
        $name = '@'.$user['username'];
    }
    
    echo '
    <a href="/'.'@'.$user['username'].'"><img class="author-avatar" src="'.$user['content']['avatar_image']['link'].'?w=85&h=85" title="@'.$user['username'].'" style="width:85px;height:85px"/>
    <span class="author-name" style="font-size:150%">'.$name.'</span></a>
    
    <div class="author-description" style="height:auto;border-bottom:1px dotted #ccc;padding:1.2em;margin-bottom:1em">
        '.$user['content']['html'].'
        <p><a class="author-name" href="https://pnut.io/@'.$user['username'].'" target="_blank">@'.$user['username'].' on Pnut</a></p>
    </div>
    ';
}

function old_brief_author($longpost) {
    if (isset($longpost['user']['name'])) {
        $name = $longpost['user']['name'];
    } else {
        $name = '@'.$longpost['user']['username'];
    }
    
    echo '
    <div class="meta-top">
        <p class="author-toggle"><a class="author-button down" href="javascript:toggle_description(\''.$longpost['id'].'\')"></a></p>
        <a href="/@'.$longpost['user']['username'].'"><img class="author-avatar" src="'.$longpost['user']['content']['avatar_image']['link'].'?w=45&h=45" title="@'.$longpost['user']['username'].'"/>
        <span class="author-name">'.$name.'</span></a>
        <p class="author-permalink" title="'.$longpost['created_at'].'"><a class="author-tstamp tstamp" href="https://pnut.io/@'.$longpost['user']['username'].'">'.$longpost['created_at'].'</a></p>
        
        <div class="author-description">
            '.$longpost['user']['content']['html'].'
            <p><a class="author-name" href="https://pnut.io/@'.$longpost['user']['username'].'" target="_blank">@'.$longpost['user']['username'].' on Pnut</a></p>
        </div>
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
        <p class="author-toggle"><a class="author-button down" href="javascript:toggle_description(\''.$longpost['id'].'\')"></a></p>
        <a href="/'.'@'.$longpost['owner']['username'].'"><img class="author-avatar" src="'.$longpost['owner']['content']['avatar_image']['link'].'?w=45&h=45" title="@'.$longpost['owner']['username'].'"/>
        <span class="author-name">'.$name.'</span></a>
        <p class="author-permalink" title="'.$longpost['recent_message']['created_at'].'"><span class="author-tstamp tstamp">'.$longpost['recent_message']['created_at'].'</span></p>
        
        <div class="author-description">
            '.$longpost['owner']['content']['html'].'
            <p><a class="author-name" href="https://pnut.io/@'.$longpost['owner']['username'].'" target="_blank">@'.$longpost['owner']['username'].' on Pnut</a></p>
        </div>
    </div>
    ';
}

function reply_content($reply)
{
    echo '
    <div class="reply">
        <div class="reply-avatar" title="@' . $reply['user']['username'] . '">
            <a href="https://pnut.io/@'.$reply['user']['username'].'" target="_blank"><img src="'.$reply['user']['content']['avatar_image']['link'].'?w=45&h=45" width="45" height="45"/></a>
        </div>

        <div class="reply-text-area">
            <div class="reply-username">
                <a href="https://pnut.io/@'.$reply['user']['username'].'" target="_blank">@'.$reply['user']['username'].'</a>
            </div>

            <div class="reply-html">
                '.$reply['content']['html'].'
            </div>
        </div>
    </div>
    ';
}

function longpost_p_preview($longpost,$include_author) {
    // Connect to db
    $db = new PDO(DBHOST, DBUSER, DBPASS);
    $sth = $db->prepare('SELECT COUNT(*) FROM views WHERE post_id = '.$longpost['id']);
    $sth->execute();
    $views = $sth->fetch()[0];
    
    // Markdown parser
    $Parsedown = new ParsedownExtra();
    
    // Make a random guess at reading speed and don't even consider wordage
    $body_by_word = preg_split('/\s+/', $longpost['raw'][0]['value']['body']);
    $readingTime = ceil(count($body_by_word) / 175);
    
    // Cut previews after a handful of words
    if (isset($longpost['content']['html']) && !empty($longpost['content']['html'])) {
        $body_preview = $longpost['content']['html'];
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
    /*if (isset($longpost['raw'][0]['value']['global_post_id'])) {
        $global_post = $app->getPost($longpost['raw'][0]['value']['global_post_id']);
        
        // discussion indicator
        if ($global_post['num_replies'] == '0') {
            $discussion = ' · '.$views.' views';
        } else {
            $discussion = ' · '.$views.' views · <span title="Has replies">Comments</span>';
        }
    } else {*/
        $discussion = ' · '.$views.' views';
    //}
    
    echo '
    
    <div class="article" id="post-'.$longpost['id'].'">
        <h2 class="title"><a href="/' . $longpost['id'].'">'.$longpost['raw'][0]['value']['title'].'</a></h2>';
        if ($include_author) {
            echo brief_author($longpost);
        } else {
            echo '<p class="author-permalink"><a class="author-tstamp tstamp" href="/'.$longpost['id'].'">'.$longpost['created_at'].'</a></p>';
        }
        echo '<div class="body">'.$body_preview.'</div>
        
        <div class="meta-bottom"><a href="/'.$longpost['id'].'" class="article-more">Continue reading</a> · <span class="article-reading-time">'.$readingTime.' min read</span>'.$discussion.'</div>
    </div>
    
    ';
}

function longpost_preview($longpost,$include_author) {
    // Connect to db
    $db = new PDO(DBHOST, DBUSER, DBPASS);
    $sth = $db->prepare('SELECT COUNT(*) FROM views WHERE post_id = '.$longpost['id']);
    $sth->execute();
    $views = $sth->fetch()[0];
    
    // Markdown parser
    $Parsedown = new ParsedownExtra();
    
    // Make a random guess at reading speed and don't even consider wordage
    $body_by_word = preg_split('/\s+/', $longpost['recent_message']['raw'][0]['value']['body']);
    $readingTime = ceil(count($body_by_word) / 175);
    
    // Cut previews after a handful of words
    if (isset($longpost['recent_message']['content']['html']) && !empty($longpost['recent_message']['content']['html'])) {
        $body_preview = $longpost['recent_message']['content']['html'];
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
    /*if (isset($longpost['raw'][0]['value']['global_post_id'])) {
        $global_post = $app->getPost($longpost['raw'][0]['value']['global_post_id']);
        
        // discussion indicator
        if ($global_post['num_replies'] == '0') {
            $discussion = ' · '.$views.' views';
        } else {
            $discussion = ' · '.$views.' views · <span title="Has replies">Comments</span>';
        }
    } else {*/
        $discussion = ' · '.$views.' views';
    //}
    
    echo '
    
    <div class="article" id="post-'.$longpost['id'].'">
        <h2 class="title"><a href="/' . $longpost['id'].'">'.$longpost['raw'][0]['value']['title'].'</a></h2>';
        if ($include_author) {
            echo brief_author($longpost);
        } else {
            echo '<p class="author-permalink"><a class="author-tstamp tstamp" href="/'.$longpost['id'].'">'.$longpost['recent_message']['created_at'].'</a></p>';
        }
        echo '<div class="body">'.$body_preview.'</div>
        
        <div class="meta-bottom"><a href="/'.$longpost['id'].'" class="article-more">Continue reading</a> · <span class="article-reading-time">'.$readingTime.' min read</span>'.$discussion.'</div>
    </div>
    
    ';
}
