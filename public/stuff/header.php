<?php

if ($page_title == 'Longposts') {
    $heading = 'Longposts';
} else {
    $heading = 'Lp';
}

echo '<!DOCTYPE html>
<head>
<meta charset="UTF-8" />
<title>'.$page_title.'</title>
<!--<link id="siteicon" rel="icon" href="../icon.png"/>-->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link href="stuff/main.css" rel="stylesheet"/>
<script src="stuff/jquery2.1.4.js"></script>
<script src="stuff/moment.js"></script>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
</head>
<body>

<div id="navigation">
    <h1 id="longposts"><a href="'.URL.'">'.$heading.'</a></h1>
</div>

<div id="content">';

?>