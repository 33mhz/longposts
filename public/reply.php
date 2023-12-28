<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
require_once __DIR__ . '/../config.php';

$app = new phpnut\ezphpnut();

// if not logged in
if (isset($_SESSION['logged_in'])) {
    $app->getSession();
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = $app->getUser();
    }
    $longpost_id = htmlentities($_POST['longpost_id'], ENT_QUOTES);

    // if reply exists
    if (isset($_POST['reply_text']) && !empty(trim($_POST['reply_text']))) {
        $global_post_id = htmlentities($_POST['global_post_id'], ENT_QUOTES);

        if ($app->createPost($_POST['reply_text'],['reply_to'=>$global_post_id])) {
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

