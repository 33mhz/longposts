<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../..');
$dotenv->load();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../functions.php';

$app = new phpnut\ezphpnut();

// if not logged in as user, use app for calls
if (isset($_SESSION['logged_in'])) {
	$app->getSession();

	if (!isset($_SESSION['user'])) {
		$_SESSION['user'] = $app->getUser();
	}

	if (!isset($_POST['body'],$_POST['title'])) {
		header('Location: '.URL.'drafts');
	}

	// description
	if (empty(trim($_POST['description']))) {
		$description = '';
	} else {
		$description = $_POST['description'];
	}

	// title
	$title = $_POST['title'];
	// body
	$body = $_POST['body'];

	// category
	if (empty(trim($_POST['category']))) {
		$category = '';
	} else {
		$category = $_POST['category'];
	}

	// assemble the channel data
	if (isset($_POST['channel_id'])) {
		$channel_id = $_POST['channel_id'];

		$channel_data = $app->getChannel($channel_id, ['include_channel_raw'=>1,'include_message_raw'=>1]);

		if (isset($channel_data['raw']['st.longpo.post'][0])) {
			$channel_data['raw']['st.longpo.post'][0]['category'] = $category;
			$channel_data['raw']['st.longpo.post'][0]['title'] = $title;
		} else {
			$channel_data['raw'][] = [
				'type' => 'st.longpo.post',
				'value' => [
					'title' => $title,
					'category' => $category
				]
			];
		}
	} else {
		$channel_data = [
			'type' => 'st.longpo.longpost',
			'raw' => [
				'st.longpo.post' => [
					[
						'title' => $title,
						'category' => $category,
					]
				]
			]
		];
	}

	// make sure slug (title) isn't duplicate
	if ($_POST['type'] === 'save') {
		if (entry_exists($title, $_SESSION['user']['id'])) {
			$_SESSION['NEG_NOTICE'][] = 'Error creating post: title matches existing post.';
			$returns = ['notice'=>'Error creating post: title matches existing post.','status'=>0,'redirect'=>URL.'drafts/write'];
		}
	} else {
		if (entry_exists($title, $_SESSION['user']['id'], $channel_id)) {
			$_SESSION['NEG_NOTICE'][] = 'Error updating post: title matches existing post.';
			$returns = ['notice'=>'Error updating post: title matches existing post.','status'=>0,'redirect'=>URL.'drafts/write'];
		}
	}

	// save draft
	if ($_POST['type'] === 'save') {
		// create new channel
		if ($channel = $app->createChannel($channel_data)) {
			$channel_id = $channel['id'];

			// create message
			$app->createMessage(
				$channel_id,
				[
					'text' => $description,
					'raw' => [
						'st.longpo.content' => [
							[
								'body' => $body
							]
						]
					]
				]
			);

			// Go to new post
			$_SESSION['POS_NOTICE'][] = 'Created new post!';
			$returns = ['notice'=>'Created new post.','status'=>1,'redirect'=>URL.$channel_id];
		} else {
			$_SESSION['NEG_NOTICE'][] = "Error creating post.";
			$returns = ['notice'=>'Error creating post.','status'=>0,'redirect'=>URL.'drafts/write'];
		}
	}
	// publish
	else if ($_POST['type'] === 'publish') {
		// set readers to public
		$channel_data['acl']['read'] = [
			'any_user' => false,
			'immutable' => false,
			'public' => true,
			'user_ids' => [],
		];

		// publish new post
		if (!isset($_POST['channel_id'])) {
			// create new channel
			if ($channel = $app->createChannel($channel_data)) {
				$channel_id = $channel['id'];

				// create message
				$app->createMessage(
					$channel_id,
					[
						'text' => $description,
						'raw' => [
							[
								'type' => 'st.longpo.content',
								'value' => [
									'body' => $body
								]
							]
						]
					]
				);

				$_SESSION['POS_NOTICE'][] = 'Created new post.';
				$returns = ['notice'=>'Published post.','status'=>1,'redirect'=>URL.$channel_id];
			} else {
				$_SESSION['NEG_NOTICE'][] = 'Error publishing post.';
				$returns = ['notice'=>'Error publishing post.','status'=>0,'redirect'=>URL.$channel_id];
			}
		}
		// publish draft
		else {
			if ($app->updateChannel($channel_id, $channel_data)) {
				// create message
				$app->createMessage(
					$channel_id,
					[
						'text' => $description,
						'raw' => [
							[
								'type' => 'st.longpo.content',
								'value' => [
									'body' => $body
								]
							]
						]
					]
				);

				$_SESSION['POS_NOTICE'][] = 'Created new post.';
				$returns = ['notice'=>'Published post.','status'=>1,'redirect'=>URL.$channel_id];
			} else {
				$_SESSION['NEG_NOTICE'][] = 'Error publishing post.';
				$returns = ['notice'=>'Error publishing post.','status'=>0,'redirect'=>URL.$channel_id];
			}
		}
	}
	// save updated draft
	else if ($_POST['type'] === 'update') {
		// update channel
		if ($channel = $app->updateChannel($channel_id, $channel_data)) {
			// create message
			$message = $app->createMessage(
				$channel_id,
				[
					'text' => $description,
					'raw' => [
						'st.longpo.content' => [
							[
								'body' => $body
							]
						]
					]
				]
			);

			$_SESSION['POS_NOTICE'][] = 'Updated draft.';
			$returns = ['notice'=>'Updated draft.','status'=>1,'redirect'=>URL.$channel_id];
		} else {
			$_SESSION['NEG_NOTICE'][] = 'Error publishing post.';
			$returns = ['notice'=>'Error publishing post.','status'=>0,'redirect'=>URL.$channel_id];
		}
	}
	// make private
	else if ($_POST['type'] === 'unpublish') {
		// set readers to private
		$channel_data['acl']['read'] = [
			'any_user' => false,
			'immutable' => false,
			'public' => false,
			'user_ids' => [],
		];

		// delete Global post if exists
		if (!empty($channel_data['raw']['st.longpo.post'][0]['global_post_id'])) {
			try {
				$app->deletePost($channel_data['raw']['st.longpo.post'][0]['global_post_id']);
			} catch (Exception $e) {
				// @TODO catch this
			}

			$channel_data['raw']['st.longpo.post'][0]['global_post_id'] = '';
		}

		// update channel
		if ($channel_data = $app->updateChannel($channel_id, $channel_data)) {
			$_SESSION['POS_NOTICE'][] = 'Made post private.';
			$returns = array('notice'=>'Made post private.','status'=>1,'redirect'=>URL.$channel_id);
		} else {
			$_SESSION['NEG_NOTICE'][] = 'Error making post private.';
			$returns = array('notice'=>'Error making post private.','status'=>0,'redirect'=>URL.$channel_id);
		}
	}
	// delete post!
	else if ($_POST['type'] === 'delete') {
		// delete Global post if exists
		if (!empty($channel_data['raw']['st.longpo.post'][0]['global_post_id'])) {
			try {
				$app->deletePost($channel_data['raw']['st.longpo.post'][0]['global_post_id']);
			} catch (Exception $e) {
				// @TODO catch this?
			}
		}

		// deactivate channel
		if ($app->deleteChannel($channel_id)) {
			$_SESSION['POS_NOTICE'][] = 'Deleted post.';
			$returns = array('notice'=>'Deleted post.','status'=>1,'redirect'=>URL.'drafts');
		} else {
			$_SESSION['NEG_NOTICE'][] = 'Couldn\'t delete!';
			$returns = array('notice'=>'Couldn\'t delete!','status'=>0,'redirect'=>URL.$channel_id);
		}
	}

	// update local database for lookup later
	if ($_POST['type'] !== 'delete') {
		update_entry($channel_id, $category, $title, $_SESSION['user']['username'], $_SESSION['user']['id']);
	}

	// Handle broadcasting
	if (!empty($_POST['broadcast']) && ((!empty($channel_data['acl']['read']['public']) && $_POST['type'] === 'update') || $_POST['type'] === 'publish')) {
		$text = '['.$_POST['title'].'](https://longpo.st/'.$channel_id.')';
		if (!empty($_POST['description']) && strlen($text . "\n".$_POST['description']."\n#longpost") <= 256) {
			$text .= "\n".$_POST['description']."\n#longpost";
		}
		// create broadcast post to global
		// allow custom post!
		$broadcast_post = $app->createPost(
			$text,
			[
				'raw' => [
					'st.longpo.broadcast' => [
						[
							'longpost_id' => $channel_id
						]
					]
				]
			],
			['include_raw' => 1]
		);
		if ($broadcast_post) {
			$last_update = $channel_data['raw'];
			$channel_data = [];
			$channel_data['raw'] = $last_update;
			$channel_data['raw']['st.longpo.post'][0]['global_post_id'] = $broadcast_post['id'];

			// update channel to reflect the broadcast post
			if ($channel_data = $app->updateChannel($channel_id, $channel_data)) {
				$_SESSION['POS_NOTICE'][] = 'Broadcasted post <a href="https://beta.pnut.io/posts/'.$broadcast_post['id'].'" target="_blank">to Global</a>.';
				$returns = array('notice'=>'Broadcasted post <a href="https://beta.pnut.io/posts/'.$broadcast_post['id'].'" target="_blank">to Global</a>.','status'=>1,'redirect'=>URL.$channel_id);
			} else {
				$_SESSION['NEG_NOTICE'][] = 'Error broadcasting to Global.';
				$returns = array('notice'=>'Error broadcasting to Global.','status'=>0,'redirect'=>URL.$channel_id);
			}
		}
	}

	// Go to published post
	//header('Location: '.URL.$channel_id);
	echo json_encode($returns);

} else {
	unset($_SESSION['user']);
	$returns = array('notice'=>'Not logged in!','status'=>0,'redirect'=>URL);
	echo json_encode($returns);
}
