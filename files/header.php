<?php
	//session_start();

	require_once('classes/DBAccess.php');
	require_once('classes/Link.php');
	require_once('classes/Login.php');
	
	$globalCSS =  Link::getGlobalCSS();
	$globalJS =  Link::getGlobalJS();
	$curr_Link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$adminLink = Link::getAdminLink();
	
	if(isset($_POST['info'])) {
		Login::manageRequest();
	}
	
	if(isset($_GET['mailId'])) {
		Login::registerEmail();
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>b-schriftung - <?=$pageName?></title>
	<meta name="Description" content="Auftragsübersicht">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" type="image/x-icon" href="<?=WEB_URL?>/favicon.ico">
	<link rel="shortcut icon" type="image/png" href="<?=WEB_URL?>/img/favicon.png">
	<link rel="stylesheet" href="<?=$globalCSS?>">
	<script src="<?=$globalJS?>"></script>
	<?php
		$files;
		if($isArticle) {
			$files = DBAccess::selectQuery("SELECT * FROM attachments_gen WHERE articleId = '${result['id']}' AND anchor = 'head'");
		} else {
			$files = DBAccess::selectQuery("SELECT * FROM attachments WHERE articleId = '${result['id']}' AND anchor = 'head'");
		}
		foreach($files as $file) {
			$link = Link::getResourcesShortLink($file['fileSrc'], $file['fileType']);
			
			if($file['fileType'] == 'css') {
				echo '<link rel="stylesheet" href="' . $link . '">';
			} else if($file['fileType'] == 'js') {
				echo '<script src="' . $link . '"></script>';
			} else if($file['fileType'] == 'font') {
				echo '<style> @font-face { font-family: ' . $file['fileName'] . '; src: url("' . $link . '"); }</style>';
			}
		}
	?>
</head>
<body>
	<header>
		<h1><?=$pageName?></h1>
	</header>
	<main>