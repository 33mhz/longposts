<?php

require_once __DIR__ . '/../../vendor/autoload.php';

\Dotenv\Dotenv::create(__DIR__.'/../..')->load();

require_once __DIR__ . '/../../config.php';
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

$app = new phpnut\ezphpnut();
$login_url = $app->getAuthUrl();

// if not logged in as user, use app for calls
if (isset($_SESSION['logged_in'])) {
    $app->getSession();
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = $app->getUser();
    }
} else {
    unset($_SESSION['user']);
    header('location: '.URL);
}

$page_title = 'Long posts &ndash; Drafts';
require_once '../../templates/header.php';

// Markdown parser
$Parsedown = new ParsedownExtra();

// get drafts
$longposts = $app->searchChannels($params = ['channel_types'=>'st.longpo.longpost','creator_id'=>$_SESSION['user']['id'],'is_private'=>1,'include_recent_message'=>1,'include_channel_raw'=>1,'include_message_raw'=>1]);


echo '<h2>Private</h2>';
foreach($longposts as $longpost) {
    
    echo '
    
    <div id="post-'.$longpost['id'].'" class="article">
        <a href="/'.'drafts/write?id='.$longpost['id'].'" style="float:right"><button type="button">Edit</button></a>
        <h3><a href="/'.$longpost['id'].'">'.$longpost['raw'][0]['value']['title'].'</a></h3>
        <p>'.$Parsedown->text($longpost['recent_message']['content']['html']).'</p>
    </div>
    
    ';
}

if (count($longposts) == 0) {
    echo '
        
        <p>You have no private posts.</p>
        
    ';
}


require_once '../../templates/footer.php';
