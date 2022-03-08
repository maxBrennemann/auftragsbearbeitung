<?php
	require_once('classes/DBAccess.php');
	require_once('classes/Link.php');
	require_once('classes/Login.php');
	require_once('classes/project/ClientSettings.php');
	
	$globalCSS =  Link::getGlobalCSS();
	$globalJS =  Link::getGlobalJS();
	$curr_Link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$adminLink = Link::getAdminLink();
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height">
	<title>b-schriftung - <?=$pageName?></title>
	<meta name="Description" content="Auftragsübersicht">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" type="image/x-icon" href="<?=WEB_URL?>/favicon.ico">
	<link rel="shortcut icon" type="image/png" href="<?=WEB_URL?>/img/favicon.png">
	<style>
		<?=Settings::getColorConfiguration()?>
	</style>
	<link rel="stylesheet" href="<?=$globalCSS?>">
	<script src="<?=$globalJS?>"></script>
	<?php
		$files;
		if ($isArticle) {
			$files = DBAccess::selectQuery("SELECT * FROM attachments_gen WHERE articleId = '{$result['id']}' AND anchor = 'head'");
		} else {
			$files = DBAccess::selectQuery("SELECT * FROM attachments WHERE articleId = '{$result['id']}' AND anchor = 'head'");
		}
		foreach($files as $file) {
			$link = Link::getResourcesShortLink($file['fileSrc'], $file['fileType']);
		
			if ($file['fileType'] == 'css') {
				echo '<link rel="stylesheet" href="' . $link . '">';
			} else if ($file['fileType'] == 'js') {
				echo '<script src="' . $link . '"></script>';
			} else if ($file['fileType'] == 'font') {
				echo '<style> @font-face { font-family: ' . $file['fileName'] . '; src: url("' . $link . '"); }</style>';
			}
		}
	?>
	<style type="text/css" media="print">
		@page {
			size: auto;   /* auto is the initial value */
			margin: 0;  /* this affects the margin in the printer settings */
		}
	</style>
</head>
<body>
	<header>
		<section>
			<h1><?=$pageName?></h1>
			<aside>
				<span>
					<span><?=NotificationManager::getNotificationCount();?></span>
					<span>&#128276;</span>
					<span>⚙</span>
				</span>
			</aside>
		</section>
		<hr class="headerline">
		<div style="margin: auto; width: 80%; margin-top: 12px; margin-bottom: 12px;"><a href="<?=Link::getPageLink("")?>" id="home_link">Home</a>/<a href="<?=Link::getPageLink($page)?>"><?=$pageName?></a></div>
	</header>
	<main>