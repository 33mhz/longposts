<?php

try {
	// get id
	if (isset($page_key[2])) {
		$db = new PDO(DBHOST, DBUSER, DBPASS);
		$query = $db->prepare('SELECT post_id FROM categories WHERE username = :username AND slug = :slug LIMIT 1');
		$query->execute([
			':username'=>strtolower(substr($page_key[1],1)),
			':slug'=>urldecode(strtolower($page_key[2])),
		]);
		$entry = $query->fetch();

		if (empty($entry)) {
			throw new Exception('Not found');
		}
		$channel_id = $entry['post_id'];
	} elseif (is_numeric($page_key[1])) {
		$channel_id = $page_key[1];
	} else {
		$channel_id = 0;
	}

	$longpost = $app->getChannel($channel_id,['include_channel_raw'=>1,'include_message_raw'=>1,'include_recent_message'=>1]);

	if ($longpost['type'] !== 'st.longpo.longpost') {
		// set notification
		$_SESSION['NEG_NOTICE'][] = 'No long post found.';
		// redirect to index
		header('Location: '.URL);
		exit;
	}

	// find appropriate raw item
	foreach ($longpost['raw'] as $raw) {
		if ($raw['type'] === 'st.longpo.post') {

			// Markdown parser
			$Parsedown = new ParsedownExtra();
			$Parsedown->setSafeMode(true);

			// views
			$views = update_views($longpost['id']);
			// global post
			if (!empty($raw['value']['global_post_id'])) {
				$global_post = $app->getPost($raw['value']['global_post_id']);
			}

			$single_page = true;
			$page_title = htmlentities($raw['value']['title'], ENT_QUOTES) . ' &ndash; Long posts';

			// currently assumes first message raw item is longpost body
			$page_description = substr($longpost['recent_message']['raw'][0]['value']['body'], 0, 256) . '…';
			$title = $raw['value']['title'];
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

			echo '<p style="float:right;font-size:80%;color:#888">';
			if (!empty($raw['value']['category'])) {
				echo 'Filed Under: <a href="/category/'.$raw['value']['category'].'">"'.$raw['value']['category'].'"</a><br>
				';
			}
			echo '<a href="' . URL . '@' . $longpost['owner']['username'] . '/' . get_slug($title) .'">Share Link</a></p>';

			if ($longpost['recent_message']['content']['entities']['tags'] || $longpost['recent_message']['content']['entities']['mentions']) {
				echo '
				<p><b>Tags</b> ';

				foreach ($longpost['recent_message']['content']['entities']['tags'] as $tag) {
					echo '<a href="https://pnut.io/tags/'.$tag['text'].'" target="_blank">#'.$tag['text'].'</a> ';
				}
				foreach ($longpost['recent_message']['content']['entities']['mentions'] as $mention) {
					echo '<a href="/@'.$mention['text'].'">@'.$mention['text'].'</a> ';
				}

				echo '</p>';
			}
			echo '<p title="Approximate Views"><i>'.$views.' approximate views</i></p>';

			// Retrieve replies
			if (isset($global_post) && !empty($global_post)) {
				echo '<p><b>Activity:</b> '.$global_post['counts']['replies'].' Repl' . ($global_post['counts']['replies'] == 1 ? 'y' : 'ies') . ', ' . $global_post['counts']['reposts'] . ' Repost' . ($global_post['counts']['reposts'] == 1 ? '' : 's') . ', ' . $global_post['counts']['bookmarks'] . ' Bookmark' . ($global_post['counts']['bookmarks'] == 1 ? '' : 's') . '</p>';

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

			break;
		}
	}

} catch (Exception $e) {
	$page_title = 'Long Posts';

	require_once 'header.php';

	echo '<p><i>'.$e->getMessage().'</i></p>
	<p>No long post found.</p>';
}

require_once 'footer.php';
