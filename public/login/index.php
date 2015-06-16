<?php
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

require_once '../../EZAppDotNet.php';
$app = new EZAppDotNet();
// check that the user is signed in
if (isset($_SESSION['user'])) {
    header('Location: '.URL);
} else {
	$url = $app->getAuthUrl();
	echo '<a href="'.$url.'"><h2>Sign in using App.net</h2></a>';
	if (isset($_SESSION['rem'])) {
		echo 'Remember me <input type="checkbox" id="rem" value="1" checked/>';
	} else {
		echo 'Remember me <input type="checkbox" id="rem" value="2" />';
	}
	?>
	<script>
	document.getElementById('rem').onclick = function(e){
		if (document.getElementById('rem').value=='1') {
			window.location='?rem=2';
		} else {
			window.location='?rem=1';
		};
	}
	</script>
<?php
}
?>