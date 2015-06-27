<?php

require_once '../../../config.php';
require_once '../../../AppDotNet.php';
require_once '../../../EZAppDotNet.php';

$app = new EZAppDotNet();

// if not logged in as user, use app for calls
if (isset($_SESSION['logged_in'])) {
    $app->getSession();
    $_SESSION['user'] = $app->getUser();
} else {
    unset($_SESSION['user']);
}


if (isset($_POST['body']) && isset($_POST['title'])) {
    // description
    if (isset($_POST['description']) && !empty($_POST['description'])) {
        $description = $_POST['description'];
    } else {
        $description = '';
    }
    
    // title
    $title = $_POST['title'];
    
    // body
    $body = $_POST['body'];
    
    // category
    if (empty($_POST['category']) || !isset($_POST['category'])) {
        $category = '';
    } else {
        $category = $_POST['category'];
    }
    
    // assemble the channel data
    if (isset($_POST['channel_id'])) {
        $channel_data = $app->getChannel($_POST['channel_id'],array('include_annotations'=>1));
        $channel_data['annotations'][0]['value']['category'] = $category;
        $channel_data['annotations'][0]['value']['title'] = $title;
        $channel_id = $_POST['channel_id'];
    } else {
        $channel_data = array(
            'type' => 'net.longposts.longpost',
            'annotations' => array(
                array(
                    'type' => 'net.longposts.post',
                    'value' => array(
                        'title' => $title,
                        'category' => $category
                    )
                )
            )
        );
    }
    
    // save draft
    if ($_POST['type'] == 'save') {
        // create new channel
        if ($channel = $app->createChannel($channel_data)) {
            $channel_id = $channel['id'];
        
            // create message
            $app->createMessage(
                $channel_id,
                $thisdata = array(
                    'text' => $description,
                    'annotations' => array(
                        array(
                            'type' => 'net.longposts.content',
                            'value' => array(
                                'body' => $body
                            )
                        )
                    )
                )
            );
            
            // Go to new post
            $_SESSION['POS_NOTICE'][] = 'Created new post!';
            $returns = array('notice'=>'Created new post.','status'=>1,'redirect'=>URL.$channel_id);
        } else {
            $_SESSION['NEG_NOTICE'][] = "Error creating post.";
            $returns = array('notice'=>'Error creating post.','status'=>0,'redirect'=>URL.'drafts/write');
        }
    }
    // publish
    else if ($_POST['type'] == 'publish') {
        // set readers to public
        $channel_data['readers'] = array(
            'any_user' => false,
            'immutable' => false,
            'public' => true,
            'user_ids' => array()
        );
        
        // publish new post
        if (!isset($_POST['channel_id'])) {
            // create new channel
            if ($channel = $app->createChannel($channel_data)) {
                $channel_id = $channel['id'];
                
                // create message
                $app->createMessage(
                    $channel_id,
                    $thisdata = array(
                        'text' => $description,
                        'annotations' => array(
                            array(
                                'type' => 'net.longposts.content',
                                'value' => array(
                                    'body' => $body
                                )
                            )
                        )
                    )
                );
                
                $_SESSION['POS_NOTICE'][] = 'Created new post.';
                $returns = array('notice'=>'Published post.','status'=>1,'redirect'=>URL.$channel_id);
            } else {
                $_SESSION['NEG_NOTICE'][] = 'Error publishing post.';
                $returns = array('notice'=>'Error publishing post.','status'=>0,'redirect'=>URL.$channel_id);
            }
        }
        // publish draft
        else {
            if ($app->updateChannel($channel_id, $channel_data)) {
                // create message
                $app->createMessage(
                    $channel_id,
                    $thisdata = array(
                        'text' => $description,
                        'annotations' => array(
                            array(
                                'type' => 'net.longposts.content',
                                'value' => array(
                                    'body' => $body
                                )
                            )
                        )
                    )
                );
                
                $_SESSION['POS_NOTICE'][] = 'Created new post.';
                $returns = array('notice'=>'Published post.','status'=>1,'redirect'=>URL.$channel_id);
            } else {
                $_SESSION['NEG_NOTICE'][] = 'Error publishing post.';
                $returns = array('notice'=>'Error publishing post.','status'=>0,'redirect'=>URL.$channel_id);
            }
        }
    }
    // save updated draft
    else if ($_POST['type'] == 'update') {
        // update channel
        if ($channel = $app->updateChannel($channel_id,$channel_data)) {
            // create message
            if ($message = $app->createMessage(
                $channel_id,
                $thisdata = array(
                    'text' => $description,
                    'annotations' => array(
                        array(
                            'type' => 'net.longposts.content',
                            'value' => array(
                                'body' => $body
                            )
                        )
                    )
                )
            )) {
                
            }
            
            $_SESSION['POS_NOTICE'][] = 'Updated draft.';
            $returns = array('notice'=>'Updated draft.','status'=>1,'redirect'=>URL.$channel_id);
        } else {
            $_SESSION['NEG_NOTICE'][] = 'Error publishing post.';
            $returns = array('notice'=>'Error publishing post.','status'=>0,'redirect'=>URL.$channel_id);
        }
    }
    // make private
    else if ($_POST['type'] == 'unpublish') {
        // set readers to private
        $channel_data['readers'] = array(
            'any_user' => false,
            'immutable' => false,
            'public' => false,
            'user_ids' => array()
        );
        
        // update channel
        if ($channel_data = $app->updateChannel($channel_id,$channel_data)) {
            $_SESSION['POS_NOTICE'][] = 'Made post private.';
            $returns = array('notice'=>'Made post private.','status'=>1,'redirect'=>URL.$channel_id);
        } else {
            $_SESSION['NEG_NOTICE'][] = 'Error making post private.';
            $returns = array('notice'=>'Error making post private.','status'=>0,'redirect'=>URL.$channel_id);
        }
    }
    // delete post!
    else if ($_POST['type'] == 'delete') {
        // delete Global post if exists
        if (isset($channel_data['annotations'][0]['value']['global_post_id'])) {
            $app->deletePost($channel_data['annotations'][0]['value']['global_post_id']);
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
    
    // Handle broadcasting
    if (isset($_POST['broadcast']) && $_POST['broadcast'] == 1 && ($_POST['type'] == 'update' || $_POST['type'] == 'publish')) {
        // create broadcast post to global
        // allow custom post!
        if ($broadcast_post = $app->createPost(
            $text='['.$_POST['title'].'](https://longposts.net/'.$channel_id.') #longpost',
            $thisdata = array(
                'annotations' => array(
                    array(
                        'type' => 'net.longposts.broadcast',
                        'value' => array(
                            'longpost_id' => $channel_id
                        )
                    )
                ),
                'entities' => array(
                    'parse_markdown_links' => 1,
                    'parse_links' => 1
                )
            ),
            $params = array(
                'include_annotations' => 1
            )
        )) {
            $last_update = $channel_data['annotations'];
            $channel_data = array();
            $channel_data['annotations'] = $last_update;
            $channel_data['annotations'][0]['value']['global_post_id'] = $broadcast_post['id'];
            
            // update channel to reflect the broadcast post
            if ($channel_data = $app->updateChannel($channel_id,$channel_data)) {
                $_SESSION['POS_NOTICE'][] = 'Broadcasted post <a href="http://treeview.us/home/thread/'.$broadcast_post['id'].'" target="_blank">to Global</a>.';
                $returns = array('notice'=>'Broadcasted post <a href="http://treeview.us/home/thread/'.$broadcast_post['id'].'" target="_blank">to Global</a>.','status'=>1,'redirect'=>URL.$channel_id);
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
    //header('Location: '.URL.'drafts');
}


?>