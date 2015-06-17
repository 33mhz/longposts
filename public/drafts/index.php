<?php

require_once '../../config.php';
require_once '../../AppDotNet.php';
require_once '../../EZAppDotNet.php';
require_once '../../functions.php';

// checking if the 'Remember me' checkbox was clicked
if (isset($_GET['rem'])) {
	session_start();
	if ($_GET['rem']=='1') {
		$_SESSION['rem']=1;
	} else {
		unset($_SESSION['rem']);
	}
	header('Location: '.URL);
}

$app = new EZAppDotNet();
$login_url = $app->getAuthUrl();

// if not logged in as user, use app for calls
if (isset($_SESSION['logged_in'])) {
    $app->getSession();
    $_SESSION['user'] = $app->getUser();
} else {
    unset($_SESSION['user']);
}

$page_title = 'Lp · Drafts';
require_once '../stuff/header.php';

// get drafts
$longposts = $app->searchChannels($params = array('type'=>'net.longposts.longpost','creator_id'=>$_SESSION['user']['id'],'is_private'=>1,'include_recent_message'=>1,'include_annotations'=>1), $query='', $order='id');


echo '<h2>Private</h2>';
foreach($longposts as $longpost) {
    
    echo '
    
    <div id="post-'.$longpost['id'].'" class="article">
        <a href="'.URL.'drafts/write?id='.$longpost['id'].'" style="float:right"><button type="button">Edit</button></a>
        <h3><a href="'.URL.'@'.$_SESSION['user']['username'].'/'.$longpost['id'].'">'.$longpost['annotations'][0]['value']['title'].'</a></h3>
        <p>'.$longpost['recent_message']['html'].'</p>
    </div>
    
    ';
}

if (count($longposts) == 0) {
    echo '
        
        <p>You have no private posts.</p>
        
    ';
}


require_once '../stuff/footer.php';
?>