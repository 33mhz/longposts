<?php

function get_slug(string $title)
{
	$slug = preg_replace('/[^\pL\d-]/u','',str_replace(['_',' ','&'],['-','-','and'],strtolower(trim($title))));
	if (strlen($slug) > 150) {
		$last_break = strpos($slug, '-', 150);
	}
	if (!empty($last_break) && $last_break < 200) {
		$slug = substr($slug, 0, $last_break);
	} else {
		$slug = substr($slug, 0, 150);
	}
	$slug = trim($slug,'-');
	return $slug;
}

function entry_exists(string $title, int $user_id, $channel_id=false)
{
	// Connect to db
	$db = new PDO(DBHOST, DBUSER, DBPASS);
	
	// normalize slug
	$slug = preg_replace('/[^\w-]/','',str_replace(['_',' ','&'],['-','-','and'],strtolower(trim($title))));
	if (strlen($slug) > 150) {
		$last_break = strpos($slug, '-', 150);
	}
	if (!empty($last_break) && $last_break < 200) {
		$slug = substr($slug, 0, $last_break);
	} else {
		$slug = substr($slug, 0, 150);
	}
	$slug = trim($slug,'-');

	// check if post ID has a recorded category
	$sth = $db->prepare('SELECT post_id FROM categories WHERE slug = :slug AND user_id = :user_id LIMIT 1');
	$sth->execute([
		':slug' => $slug,
		':user_id' => $user_id,
	]);
	$channel_exists = $sth->fetch();

	if (!empty($channel_exists) && ($channel_id == false || $channel_exists['post_id'] != $channel_id)) {
		return true;
	}
	return false;
}

function update_entry(int $channel_id, string $category, string $title, string $username, int $user_id)
{
	// Connect to db
	$db = new PDO(DBHOST, DBUSER, DBPASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
	
	// normalize slug
	$slug = preg_replace('/[^\w-]/','',str_replace(['_',' ','&'],['-','-','and'],strtolower(trim($title))));
	if (strlen($slug) > 150) {
		$last_break = strpos($slug, '-', 150);
	}
	if (!empty($last_break) && $last_break < 200) {
		$slug = substr($slug, 0, $last_break);
	} else {
		$slug = substr($slug, 0, 150);
	}
	$slug = trim($slug,'-');

	// check if post ID has a recorded category
	$sth = $db->prepare('SELECT post_id, category, slug, username, user_id FROM categories WHERE post_id = :post_id LIMIT 1');
	$sth->execute([':post_id'=>$channel_id]);
	$channel_exists = $sth->fetch();
	
	// if channel doesn't have record, insert
	if ($sth->rowCount() == 0) {

		$query = $db->prepare('INSERT INTO categories (post_id, category, slug, username, user_id) VALUES (:post_id, :category, :slug, :username, :user_id)');
		$query->execute([
			':post_id' => $channel_id,
			':category' => $category,
			':slug' => $slug,
			':username' => strtolower($username),
			':user_id' => $user_id,
		]);
	} elseif ($channel_exists['category'] !== $category || $channel_exists['slug'] !== $slug || $channel_exists['username'] !== strtolower($username) || $channel_exists['user_id'] !== $user_id) {

		// if channel hasn't been recorded with this category, update
		$query = $db->prepare('UPDATE categories SET category = :category, slug=:slug, username=:username, user_id=:user_id WHERE post_id = :post_id');
		$query->execute([
				':category' => $category,
				':slug' => $slug,
				':username' => strtolower($username),
				':user_id' => $user_id,
				':post_id' => $channel_id,
		]);
	}
}

function get_category_ids(string $category)
{
	// Connect to db
	$db = new PDO(DBHOST, DBUSER, DBPASS);
	
	// get view count
	$sth = $db->prepare('SELECT post_id FROM categories WHERE category = :category ORDER BY post_id DESC LIMIT 200');
	$sth->execute([':category' => $category]);
	$category_ids = $sth->fetchAll();
	
	$channel_ids = [];
	foreach($category_ids as $category_id) {
		$channel_ids[] = $category_id['post_id'];
	}
	
	return $channel_ids;
}

function getIp()
{
	$ip = $_SERVER['REMOTE_ADDR'];

	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	return $ip;
}

function update_views(int $post_id)
{
	// Connect to db
	$db = new PDO(DBHOST, DBUSER, DBPASS);
	
	// get view count
	$sth = $db->prepare('SELECT COUNT(*) FROM views WHERE post_id = :post_id');
	$sth->execute([':post_id'=>$post_id]);
	$views = $sth->fetch()[0];
	$ip = str_replace('.', '', getIp());
	
	$this_user_viewed = false;
	
	// tick database for another view
	if ($views > 0) {
		// get visits from this IP
		$sth = $db->prepare('SELECT * FROM views WHERE post_id = :post_id AND ip = :ip');
		$sth->execute([':post_id'=>$post_id, ':ip'=>$ip]);
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
			$user_id = null;
		}
		
		$query = $db->prepare('INSERT INTO views (post_id, ip, user_id) VALUES (:post_id, :ip, :user_id)');
		$query->execute([
			':post_id' => $post_id,
			':ip' => $ip,
			':user_id' => $user_id
		]);
		
		$views++;
	}
	
	return $views;
}

function author($user)
{
	$name = $user['name'] ?? '@'.$user['username'];
	if (isset($user['content']['entities'])) {
		$html = parse_entities($user['content']['html'], $user['content']['entities']['tags']);
	} else {
		$html = '';
	}
	
	echo '
	<a href="/'.'@'.$user['username'].'"><img class="author-avatar" src="'.$user['content']['avatar_image']['link'].'?w=85&h=85" title="@'.$user['username'].'" style="width:85px;height:85px"/>
	<span class="author-name" style="font-size:150%">'.$name.'</span></a>
	
	<div class="author-description" style="height:auto;border-bottom:1px dotted #ccc;padding:1.2em;margin-bottom:1em">
		'.$html.'
		<p><a class="author-name" href="https://pnut.io/@'.$user['username'].'" target="_blank">@'.$user['username'].' on Pnut</a></p>
	</div>
	';
}

function rel_time(string $created_at, bool $full = false)
{
	$now = new DateTime;
	$ago = new DateTime($created_at);
	$diff = $now->diff($ago);

	$diff->w = floor($diff->d / 7);
	$diff->d -= $diff->w * 7;

	$string = [
		'y' => 'year',
		'm' => 'month',
		'w' => 'week',
		'd' => 'day',
		'h' => 'hour',
		'i' => 'minute',
		's' => 'second',
	];
	foreach ($string as $k => &$v) {
		if ($diff->$k) {
			$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
		} else {
			unset($string[$k]);
		}
	}

	if (!$full) $string = array_slice($string, 0, 1);
	return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function brief_author($longpost, bool $is_post=false)
{
	if ($is_post) {
		$creator_variable = 'user';
		$created_at = $longpost['created_at'];
	} else {
		$creator_variable = 'owner';
		$created_at = $longpost['recent_message']['created_at'];
	}
	$rel_created_at = rel_time($created_at);

	if (isset($longpost[$creator_variable]['name'])) {
		$name = $longpost[$creator_variable]['name'];
	} else {
		$name = '@'.$longpost[$creator_variable]['username'];
	}

	if (isset($longpost[$creator_variable]['content']['html'])) {
		$html = parse_entities($longpost[$creator_variable]['content']['html'], $longpost[$creator_variable]['content']['entities']['tags']);
	} else {
		$html = '';
	}

	echo '
	<div class="meta-top">
		<p class="author-toggle"><a class="author-button down" href="javascript:toggle_description(\''.$longpost['id'].'\')"></a></p>
		<a href="/'.'@'.$longpost[$creator_variable]['username'].'"><img class="author-avatar" src="'.$longpost[$creator_variable]['content']['avatar_image']['link'].'?w=45&h=45" title="@'.$longpost[$creator_variable]['username'].'"/>
		<span class="author-name">'.$name.'</span></a>
		<p class="author-permalink" title="'.$created_at.'"><span class="author-tstamp tstamp">'.$rel_created_at.'</span></p>
		
		<div class="author-description">
			'.$html.'
			<p><a class="author-name" href="https://pnut.io/@'.$longpost[$creator_variable]['username'].'" target="_blank">@'.$longpost[$creator_variable]['username'].' on Pnut</a></p>
		</div>
	</div>
	';
}

function reply_content($reply)
{
	$html = parse_entities($reply['content']['html'], $reply['content']['entities']['tags']);
	echo '
	<div class="reply">
		<div class="reply-avatar" title="@' . $reply['user']['username'] . '">
			<a href="/@'.$reply['user']['username'].'"><img src="'.$reply['user']['content']['avatar_image']['link'].'?w=45&h=45" width="45" height="45"/></a>
		</div>

		<div class="reply-text-area">
			<div class="reply-username">
				<a href="/@'.$reply['user']['username'].'">@'.$reply['user']['username'].'</a>
			</div>

			<div class="reply-html">
				'.$html.'
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
	// assumes first raw item!
	foreach($longpost['raw'] as $raw) {
		if ($raw['type'] === 'nl.chimpnut.blog.post' && isset($raw['value']['body'])) {
			$body_by_word = preg_split('/\s+/', $longpost['raw'][0]['value']['body']);
			$readingTime = ceil(count($body_by_word) / 175);
		}
	}
	if (!isset($readingTime)) {
		$readingTime = 0;
	}
	
	// Cut previews after a handful of words
	if (isset($longpost['content']['html'])) {
		$body_preview = parse_entities($longpost['content']['html'], $longpost['content']['entities']['tags']);
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
	$rel_created_at = rel_time($longpost['created_at']);

	if (empty($longpost['raw'][0]['value']['title'])) {
		$title = strftime('%Y-%m-%d', strtotime($longpost['created_at']));
	} else {
		$title = $longpost['raw'][0]['value']['title'];
	}
		
	echo '
	
	<div class="article" id="post-'.$longpost['id'].'">
		<h2 class="title"><a href="/p/' . $longpost['id'].'">'.$title.'</a></h2>';
		if ($include_author) {
			echo brief_author($longpost);
		} else {
			echo '<p class="author-permalink"><a class="author-tstamp tstamp" href="/'.$longpost['id'].'" title="'.$longpost['created_at'].'">'.$rel_created_at.'</a></p>';
		}
		echo '<div class="body">'.$body_preview.'</div>
		
		<div class="meta-bottom"><a href="/p/'.$longpost['id'].'" class="article-more">Continue reading</a> · <span class="article-reading-time">'.$readingTime.' min read</span>'.$discussion.'</div>
	</div>
	
	';
}

function longpost_preview($longpost,$include_author) {
	// Connect to db
	$db = new PDO(DBHOST, DBUSER, DBPASS);
	$sth = $db->prepare('SELECT COUNT(*) FROM views WHERE post_id = :post_id');
	$sth->execute([':post_id' => $longpost['id']]);
	$views = $sth->fetch()[0];
	
	// Markdown parser
	$Parsedown = new ParsedownExtra();
	$Parsedown->setSafeMode(true);
		
	// Make a random guess at reading speed and don't even consider wordage
	// assumes first raw item!
	$body_by_word = preg_split('/\s+/', $longpost['recent_message']['raw'][0]['value']['body']);
	$readingTime = ceil(count($body_by_word) / 175);
	
	// Cut previews after a handful of words
	if (isset($longpost['recent_message']['content']['html'])) {
		$body_preview = parse_entities($longpost['recent_message']['content']['html'], $longpost['recent_message']['content']['entities']['tags']);
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
	$rel_created_at = rel_time($longpost['recent_message']['created_at']);
	
	echo '
	
	<div class="article" id="post-'.$longpost['id'].'">
		<h2 class="title"><a href="/' . $longpost['id'].'">'.htmlentities($longpost['raw'][0]['value']['title'],ENT_QUOTES).'</a></h2>';
		if ($include_author) {
			echo brief_author($longpost);
		} else {
			echo '<p class="author-permalink"><a class="author-tstamp tstamp" href="/'.$longpost['id'].'" title="' . $longpost['recent_message']['created_at'] . '">'.$rel_created_at.'</a></p>';
		}
		echo '<div class="body">'.$body_preview.'</div>
		
		<div class="meta-bottom"><a href="/'.$longpost['id'].'" class="article-more">Continue reading</a> · <span class="article-reading-time">'.$readingTime.' min read</span>'.$discussion.'</div>
	</div>
	
	';
}

function parse_entities(string $html, array $tags): string
{
	// replace mentions
	$html = preg_replace('/<span data-mention-id="\d+" data-mention-name="\w+" itemprop="mention">(@\w+)<\/span>/', '<a href="/$1">$1</a>', $html);
	// replace tags
	foreach($tags as $tag) {
		$html = preg_replace('/<span data-tag-name="' . $tag['text'] . '" itemprop="tag">#(' . $tag['text'] . ')<\/span>/', '<a href="https://beta.pnut.io/tags/$1" target="_blank">#$1</a>', $html);
	}

	return $html;
}
