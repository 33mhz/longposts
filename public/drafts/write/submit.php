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
            ),
            'include_annotations' => 1
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
                array(
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
            
            // Go back to drafts
            //header('Location: '.URL.'drafts');
        } else {
            //header('Location: '.URL.'drafts/write');
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
                    array(
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
            }
        }
        // publish draft
        else {
            if ($app->updateChannel($channel_id, $channel_data)) {
                // create message
                $app->createMessage(
                    $channel_id,
                    array(
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
                
                //header('Location: '.URL.$channel_id);
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
                $data = array(
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
            
            // Go back to drafts
            //header('Location: '.URL.$channel_id);
        } else {
            //header('Location: '.URL.'drafts/write/?id='.$channel_id);
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
            //header('Location: '.URL);
        } else {
            
        }
    }
    // delete post!
    else if ($_POST['type'] == 'delete') {
        // delete Global post if exists
        if (isset($channel_data['annotations'][0]['value']['global_post_id'])) {
            
        }
        
        // deactivate channel
        $app->deleteChannel($channel_id);
    }
    
    // Handle broadcasting
    if (isset($_POST['broadcast']) && $_POST['broadcast'] == 1 && ($_POST['type'] == 'update' || $_POST['type'] == 'publish')) {
        // create broadcast post to global
        // allow custom post!
        if ($broadcast_post = $app->createPost(
            $text='Published ['.$_POST['title'].'](https://longposts.net/'.$channel_id.')',
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
            
            print_r($channel_data);
            echo '-------------------------------------------------------------------------------';
            //echo $data['annotations'][0]['value']['global_post_id'];
            
            // update channel to reflect the broadcast post
            if ($channel_data = $app->updateChannel($channel_id,$channel_data)) {
                
            }
        }
        
        // Go to published post
        //header('Location: '.URL.$channel_id);
    }   
    
} else {
    //header('Location: '.URL.'drafts');
    echo 'no title/body';
}


?>