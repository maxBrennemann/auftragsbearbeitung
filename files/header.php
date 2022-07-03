<?php
	require_once('classes/DBAccess.php');
	require_once('classes/Link.php');
	require_once('classes/Login.php');
	require_once('classes/project/ClientSettings.php');
	
	$globalCSS =  Link::getGlobalCSS();
	$globalJS =  Link::getGlobalJS();
	$curr_Link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$adminLink = Link::getAdminLink();

	$neuerKunde   =		Link::getPageLink("neuer-kunde");
	$neuerAuftrag =		Link::getPageLink("neuer-auftrag");
	$rechnung =			Link::getPageLink("rechnung");
	$neuesAngebot =		Link::getPageLink("angebot");
	$neuesProdukt =		Link::getPageLink("neues-produkt");
	$diagramme =		Link::getPageLink("diagramme");
	$auftragAnzeigen =	Link::getPageLink("auftrag");
	$kunde =			Link::getPageLink("kunde");
	$leistungen =		Link::getPageLink("leistungen");
	$toDo =				Link::getPageLink("verbesserungen");
	$offeneRechnungen = Link::getPageLink("offene-rechnungen");
	$funktionen = 		Link::getPageLink("functionalities");
	$produkt = 			Link::getPageLink("produkt");
	$einstellungen =	Link::getPageLink("einstellungen");
	$payments =			Link::getPageLink("payments");
	$listmaker =		Link::getPageLink("listmaker");
	$changelog = 		Link::getPageLink("changelog");
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
		<?=ClientSettings::getColorConfiguration()?>
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
			switch ($file['fileType']) {
				case 'css':
					echo '<link rel="stylesheet" href="' . $link . '">';
					break;
				case 'js':
				case 'extJs':
					echo '<script src="' . $link . '"></script>';
					break;
				case 'font':
					echo '<style> @font-face { font-family: ' . $file['fileName'] . '; src: url("' . $link . '"); }</style>';
					break;
				default:
					break;
			}
		}

		/* used this query to try to select all cases for new implementation: SELECT attachments.*, CONCAT(articles.src, ".js") AS concatted FROM `attachments` LEFT JOIN articles ON articles.id = attachments.articleId WHERE CONCAT(articles.src, ".js") = attachments.fileSrc; */

		$link = Link::getResourcesShortLink($page . ".js", "js");
		echo '<script src="' . $link . '"></script>';
		$link = Link::getResourcesShortLink($page . ".css", "css");
		echo '<link rel="stylesheet" href="' . $link . '">';
	?>
	<style type="text/css" media="print">
		@page {
			size: auto;   /* auto is the initial value */
			margin: 0;  /* this affects the margin in the printer settings */
		}
	</style>
</head>
<body>
	<div class="sidenav" id="sidenav">
		<ul>
			<li>
				<a href="<?=$neuerKunde?>">Neuen Kunden erstellen</a>
			</li>
			<li>
				<a href="<?=$kunde?>">Kunden</a>
			</li>
			<li>
				<a href="<?=$neuerAuftrag?>">Neuen Auftrag erstellen</a>
			</li>
			<li>
				<a href="<?=$neuesAngebot?>">Neues Angebot erstellen</a>
			</li>
			<li>
				<a href="<?=$neuesProdukt?>">Neues Produkt erstellen</a>
			</li>
			<li>
				<a href="<?=$produkt?>">Produktübersicht</a>
			</li>
			<li>
				<a href="<?=$auftragAnzeigen?>">Aufträge</a>
			</li>
			<li>
				<a href="<?=$diagramme?>">Diagramme und Auswertungen</a>
			</li>
			<li>
				<a href="<?=$leistungen?>">Leistungen</a>
			</li>
			<li>
				<a href="<?=$listmaker?>">Listen</a>
			</li>
			<li>
				<a href="<?=$payments?>">Finanzen</a>
			</li>
			<li>
				<a href="<?=$einstellungen?>">Einstellungen</a>
			</li>
			<li>
				<a href="<?=$funktionen?>">Funktionen</a>
			</li>
			<li>
				<a href="<?=$changelog?>">Versionsverlauf</a>
			</li>
		</ul>
	</div>
	<header class="moveToSide">
		<section>
			<h1><?=$pageName?></h1>
			<aside>
				<span>
					<input class="searchItems" type="search" onchange="performGlobalSearch(event)">
					<span class="searchItems lupeSpan"><span class="searchItems lupe">&#9906;</span></span>
					<span><?=NotificationManager::getNotificationCount();?></span>
					<span>&#128276;</span>
					<span id="settings">⚙</span>
				</span>
			</aside>
		</section>
		<div class="hamburgerDiv">
			<!-- https://www.mediaevent.de/tutorial/css-transform.html -->
			<input type="checkbox" id="hamburg" onclick="toggleNav()">
			<label for="hamburg" class="hamburg">
				<span class="line"></span>
				<span class="line"></span>
				<span class="line"></span>
			</label>
		</div>
		<hr class="headerline">
		<div style="margin: auto; width: 80%; margin-top: 12px; margin-bottom: 12px;"><a href="<?=Link::getPageLink("")?>" id="home_link">Home</a>/<a href="<?=Link::getPageLink($page)?>"><?=$pageName?></a></div>
	</header>
	<main>