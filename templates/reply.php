<?php

require_once __DIR__ . '/../vendor/autoload.php';

\Dotenv\Dotenv::create(__DIR__.'/..')->load();

require_once __DIR__ . '/../config.php';

$app = new phpnut\ezphpnut();

// if not logged in
if (!isset($_SESSION['logged_in'])) {
    unset($_SESSION['user']);
    $returns = ['notice'=>'Not logged in!','status'=>0,'redirect'=>URL];
    echo json_encode($returns);
    exit;
}

$app->getSession();
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = $app->getUser();
}

// if reply exists
if (!empty($_POST['reply_text']) && !empty($_POST['global_post_id']) && !empty($_POST['longpost_id'])) {
    $global_post_id = htmlentities($_POST['global_post_id'], ENT_QUOTES);

    if ($app->createPost($_POST['reply_text'], ['reply_to'=>$global_post_id])) {
        $_SESSION['POS_NOTICE'][] = 'Replied';
        header('Location: '.URL.$_POST['longpost_id']);
    }
} else {
    $returns = ['notice'=>'Could not post.','status'=>0,'redirect'=>URL.$_POST['longpost_id']];
    echo json_encode($returns);
}
