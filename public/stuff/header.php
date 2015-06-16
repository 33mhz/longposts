<?php

if ($page_title == 'Longposts') {
    $heading = 'Longposts';
} else {
    $heading = 'Lp';
}

echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>'.$page_title.'</title>
<!--<link id="siteicon" rel="icon" href="../icon.png"/>-->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link href="'.URL.'stuff/main.css" rel="stylesheet"/>
<script src="'.URL.'stuff/jquery2.1.4.js"></script>
<script src="'.URL.'stuff/moment.js"></script>
';
if (isset($_SESSION['logged_in'])) {
    echo '<!--markdown editor-->
<link rel="stylesheet" href="//cdn.jsdelivr.net/editor/0.1.0/editor.css">
<script src="//cdn.jsdelivr.net/editor/0.1.0/editor.js"></script>
<script src="//cdn.jsdelivr.net/editor/0.1.0/marked.js"></script>';
}
echo '
</head>
<body>

<div id="navigation">
    <div style="float:right">';
    // If not logged in
    if (!isset($_SESSION['logged_in'])) {
        echo '<a href="'.$login_url.'">Log in</a>';
    } else {
        echo '<a href="'.URL.'@'.$_SESSION['user']['username'].'">Your Posts</a> <a href="'.URL.'drafts">Drafts</a> <a href="'.URL.'drafts/write">Write</a> <a href="'.URL.'logout">Log out</a>';
    }
    echo '</div>

    <h1 id="longposts"><a href="'.URL.'">'.$heading.'</a></h1>
</div>

<div id="content">';

?>