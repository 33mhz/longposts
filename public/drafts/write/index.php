<?php

require_once '../../../config.php';
require_once '../../../AppDotNet.php';
require_once '../../../EZAppDotNet.php';

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
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = $app->getUser();
    }
} else {
    unset($_SESSION['user']);
}

$page_title = 'Lp · Write';
require_once '../../stuff/header.php';

if (isset($_GET['id'])) {
    $channel = $app->getChannel($_GET['id'],$params = array('include_recent_message'=>1,'include_annotations'=>1));
    $title = $channel['annotations'][0]['value']['title'];
    $body = $channel['recent_message']['annotations'][0]['value']['body'];
    $description = $channel['recent_message']['text'];
    $is_published = $channel['readers']['public'];
    if (isset($channel['annotations'][0]['value']['category']) && !empty($channel['annotations'][0]['value']['category'])) {
        $category = $channel['annotations'][0]['value']['category'];
    } else {
        $category = '';
    }
    if (isset($channel['annotations'][0]['value']['global_post_id'])) {
        $broadcast = '';
    } else {
        $broadcast = ' <p><label><input type="checkbox" class="broadcast" name="broadcast" checked/> Broadcast Post to Global (and allow replies)</label></p>';
    }
} else {
    $title = '';
    $body = '';
    $description = '';
    $category = '';
    $broadcast = ' <p><label><input type="checkbox" class="broadcast" name="broadcast" checked/> Broadcast Post to Global (and allow replies)</label></p>';
}

?>

<div class="editor-wrapper">
    <form name="draft_form">
    <input class="title" type="text" id="title" name="post_title" placeholder="Title" value="<?php echo $title; ?>" required/>
    <textarea id="editor" name="post_body" placeholder="Content here ...." maxlength="8000"><?php echo $body; ?></textarea>
    
    <p><textarea name="post_description" id="description" placeholder="Optional description/subheading" maxlength="256" style="width:100%"><?php echo $description; ?></textarea></p>
    
    <p>Category: <input type="text" id="category" name="category" placeholder="Optional" value="<?php echo $category; ?>" /></p>
    
    <?php echo $broadcast;
    if (isset($channel)) { ?>
    <p><button type="button" name="submit" value="update" onclick="save_form(1)">Save Private</button> <?php if (!$is_published) { echo '<button type="button" name="submit" value="publish" onclick="save_form(2)">Publish</button> '; } else { echo '<button type="button" name="submit" value="unpublish" onclick="save_form(3)">Un-publish</button>'; } ?> <button type="button" name="submit" value="delete" onclick="save_form(4)">DELETE</button></p>
    <? } else { ?>
    <p><button type="button" name="submit" value="save" onclick="save_form(5)">Save Private</button> <button type="button" name="submit" value="publish" onclick="save_form(6)">Publish</button></p>
    <?php } ?>
    </form>
</div>


<script>
var editor = new Editor();
editor.render();

// Variable to hold request
var request;

function save_form(which) {
    if (which == 1) {
        var type = 'update';
    } else if (which == 2) {
        var type = 'publish';
    } else if (which == 3) {
        var type = 'unpublish';
    } else if (which == 4) {
        var type = 'delete';
    } else if (which == 5) {
        var type = 'save';
    } else if (which == 6) {
        var type = 'publish';
    }
    
    if ($('.broadcast')[0].checked == true) {
        var broadcast = 1;
    } else {
        var broadcast = 0;
    }
    var title = $('#title').val();
    var body = editor.codemirror.getValue();
    var description = $('#description').val();
    if (description == '') {
        description = body.substring(0,256);
    }
    var category = $('#category').val();
    
    // Variable to hold request
    /*var request;

    // Abort any pending request
    if (request) {
        request.abort();
    }*/

    if (body.length > 0 && body.length < 8001 && description.length < 257 && title.length > 0 && title.length < 65) {
        // Fire off the request to /form.php
        $.ajax({
            type:"POST",
            url: 'https://longposts.net/drafts/write/submit.php',
            data: {'title':title,'body':body,'description':description,'category':category,'type':type,'broadcast':broadcast<?php if (isset($channel)) { echo ',\'channel_id\':\''.$channel['id'].'\''; } ?>},
            dataType: 'json',
            success: function(r) {
                console.log('worked');
                console.log(r);
            },
            error: function(r) {
                console.log(r);
            }
        });
    } else {
        console.log('does not meet requirements');
    }
    /*request = $.ajax({
        url: "<?php echo URL; ?>drafts/write/submit.php",
        type: "post",
        data: {'title':title,'body':body,'description':description,'category':category,'type':type<?php if (isset($channel)) { echo ',\'channel_id\':\''.$channel['id'].'\''; } ?>}
    });

    // Callback handler that will be called on success
    request.done(function (response){
        // Log a message to the console
        console.log(response);
        console.log("Hooray, it worked!");
    });

    // Callback handler that will be called on failure
    request.fail(function (jqXHR, textStatus, errorThrown){
        // Log the error to the console
        console.error(
            "The following error occurred: "+
            textStatus, errorThrown
        );
    });

    // Callback handler that will be called regardless
    // if the request failed or succeeded
    request.always(function () {
        // Reenable the inputs
        //$inputs.prop("disabled", false);
    });*/

    // Prevent default posting of form
    //event.preventDefault();
}
</script>
<?
require_once '../../stuff/footer.php';
?>