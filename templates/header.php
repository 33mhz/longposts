<?php

$_SESSION['last_url'] = $_SERVER['REQUEST_URI'];

echo '<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>'.$page_title.'</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">';
    if (isset($page_description)) {
    echo '
    <meta name="description" content="' . htmlentities($page_description,ENT_QUOTES) . '">';
    }
    if (isset($rss_url)) {
    	echo '
    	<link rel="alternate" type="application/rss+xml" href="' . $rss_url . '">';
    }
    echo '
    <link href="/static/css/main.css?d=20191104" rel="stylesheet"/>
    <script src="/static/js/jquery.js"></script>
';
if (isset($_SESSION['logged_in'])) {
    echo '  <!--markdown editor-->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
    <script src="//cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>';
}
echo '
</head>
<body';
if (isset($single_page)) {
    echo ' class="single-article"';
}
echo '>

<div id="navigation">
    <div style="float:right">';
    // If not logged in
    if (!isset($_SESSION['logged_in'])) {
        echo '<a href="'.$login_url.'">Log in</a>';
    } else {
        echo '<a href="/@'.$_SESSION['user']['username'].'">@'.$_SESSION['user']['username'].'</a> | <a href="/drafts">Drafts</a> | <a href="/drafts/write">Write</a> | <a href="/logout">Log out</a>';
    }
    echo '</div>

    <h1 id="longposts"><a href="/">Long posts</a></h1>
</div>

<div id="content">

<div id="statuses">
    ';
    if (isset($_SESSION['POS_NOTICE'][0])) {
        foreach ($_SESSION['POS_NOTICE'] as $notice) {
            echo '<p class="positive-notice"> '.$notice.'</p>';
        }
        unset($_SESSION['POS_NOTICE']);
    }
    if (isset($_SESSION['NEG_NOTICE'][0])) {
        foreach ($_SESSION['NEG_NOTICE'] as $notice) {
            echo '<p class="negative-notice"> '.$notice.'</p>';
        }
        unset($_SESSION['NEG_NOTICE']);
    }
echo '</div>';
