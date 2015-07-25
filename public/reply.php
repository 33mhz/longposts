<?php

require_once '../config.php';
require_once '../AppDotNet.php';
require_once '../EZAppDotNet.php';

$app = new EZAppDotNet();

// if not logged in
if (isset($_SESSION['logged_in'])) {
    $app->getSession();
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = $app->getUser();
    }

    $longpost_id = htmlentities($_POST['longpost_id'], ENT_QUOTES);
    
    // if reply exists
    if (isset($_POST['reply_text']) && !empty($_POST['reply_text'])) {
        $reply = htmlentities($_POST['reply_text'], ENT_QUOTES);
        $global_post_id = htmlentities($_POST['global_post_id'], ENT_QUOTES);
        
        if ($app->createPost($text=$reply,$data=array('reply_to'=>$global_post_id))) {
            $_SESSION['POS_NOTICE'][] = 'Replied';
            header('Location: '.URL.$longpost_id);
        }
    } else {
        $returns = array('notice'=>'Could not post.','status'=>0,'redirect'=>URL.$longpost_id);
        echo json_encode($returns);
    }
    
} else {
    unset($_SESSION['user']);
    $returns = array('notice'=>'Not logged in!','status'=>0,'redirect'=>URL);
    echo json_encode($returns);
}

?>