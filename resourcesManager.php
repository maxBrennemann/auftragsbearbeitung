<?php
	require_once('settings.php');
	require_once('classes/DBAccess.php');
	require_once('classes/Link.php');

	if(isset($_GET['script'])) {
		header('Content-Type: text/javascript');
		$script = $_GET['script'];
		$file = file_get_contents(Link::getResourcesLink($script, "js", false));
		echo $file;
	}

	if(isset($_GET['css'])) {
		header("Content-type: text/css");
		$script = $_GET['css'];
		$file = file_get_contents(Link::getResourcesLink($script, "css", false));
		echo $file;
	}
?>