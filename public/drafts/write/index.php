<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

\Dotenv\Dotenv::create(__DIR__.'/../../..')->load();

require_once __DIR__ . '/../../../config.php';

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

$page_title = 'Long posts &ndash; Write';
require_once '../../../templates/header.php';

if (isset($_GET['id'])) {
    $channel = $app->getChannel($_GET['id'],['include_recent_message'=>1,'include_channel_raw'=>1,'include_message_raw'=>1]);
    $title = $channel['raw'][0]['value']['title'];
    $body = $channel['recent_message']['raw'][0]['value']['body'];
    $description = $channel['recent_message']['content']['text'];
    $is_published = $channel['acl']['read']['public'];
    if (!empty($channel['raw'][0]['value']['category'])) {
        $category = $channel['raw'][0]['value']['category'];
    } else {
        $category = '';
    }
    if (!empty($channel['raw'][0]['value']['global_post_id'])) {
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

    <textarea id="editor" name="post_body" placeholder="Content here ...." maxlength="8400"><?php echo $body; ?></textarea>

    <p><label>Category: <input type="text" id="category" name="category" placeholder="Optional" value="<?php echo $category; ?>" /></label></p>
    
    <p><label>Description: <textarea name="post_description" id="description" placeholder="Optional description/subheading" maxlength="244" style="width:100%"><?php echo $description; ?></textarea></label></p>

    <?php
    echo $broadcast;
    if (isset($channel)) {
        echo '<p><button type="button" name="submit" value="update" onclick="save_form(1)">Save</button> ';
        if (!$is_published) {
            echo '<button type="button" name="submit" value="publish" onclick="save_form(2)">Publish</button> ';
        } else {
            echo '<button type="button" name="submit" value="unpublish" onclick="save_form(3)">Un-publish</button>';
        }
        echo ' <button type="button" name="submit" value="delete" onclick="save_form(4)">DELETE</button></p>';
    } else {
        echo '<p><button type="button" name="submit" value="save" onclick="save_form(5)">Save</button> <button type="button" name="submit" value="publish" onclick="save_form(6)">Publish</button></p>';
    } ?>
    </form>
</div>


<script>
var simplemde = new SimpleMDE({
    autosave: {
        enabled:true,
        unique_id: 'longpost',
        delay: 60000
    }
});
simplemde.render();

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
    
    if (typeof $('.broadcast')[0] !== 'undefined' && $('.broadcast')[0].checked == true) {
        var broadcast = 1;
    } else {
        var broadcast = 0;
    }
    var title = $('#title').val();
    var body = simplemde.value();
    var description = $('#description').val();
    if (description == '') {
        description = body.substring(0,256);
    }
    var category = $('#category').val();

    if (body.length > 0 && body.length < 8001 && description.length < 257 && title.length > 0 && title.length < 65) {
        // Fire off the request to /form.php
        $.ajax({
            type:"POST",
            url: 'https://longpo.st/drafts/write/submit.php',
            data: {'title':title,'body':body,'description':description,'category':category,'type':type,'broadcast':broadcast<?php if (isset($channel)) { echo ',\'channel_id\':\''.$channel['id'].'\''; } ?>},
            dataType: 'json',
            success: function(r) {
                console.log(r);
                if (r.status == 1) {
                    $('#notices').html('<p class="positive-notice">'+r.notice+'</p>');
                    window.location = r.redirect;
                } else {
                    $('#notices').html('<p class="negative-notice">'+r.notice+'</p>');
                }
            },
            error: function(r) {
                console.log(r);
            }
        });
    } else {
        console.log('does not meet requirements');
    }
}
</script>
<?php
require_once '../../../templates/footer.php';
