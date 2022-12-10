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
	$leistungenLinks =	Link::getPageLink("leistungen");
	$toDo =				Link::getPageLink("verbesserungen");
	$offeneRechnungen = Link::getPageLink("offene-rechnungen");
	$funktionen = 		Link::getPageLink("functionalities");
	$produkt = 			Link::getPageLink("produkt");
	$einstellungen =	Link::getPageLink("einstellungen");
	$payments =			Link::getPageLink("payments");
	$listmaker =		Link::getPageLink("listmaker");
	$changelog = 		Link::getPageLink("changelog");
	$zeiterfassung =	Link::getPageLink("zeiterfassung");
	$motive = 			Link::getPageLink("sticker");
	$motiveOverview = 	Link::getPageLink("sticker-overview");
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
				<a href="<?=$leistungenLinks?>">Leistungen</a>
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
				<a href="<?=$zeiterfassung?>">Zeiterfassung</a>
			</li>
			<li>
				<a href="<?=$motive?>">Motive</a>
			</li>
			<li>
				<a href="<?=$motiveOverview?>">Motivübersicht</a>
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
				<div class="searchContainer">
					<input class="searchItems" type="search" onchange="performGlobalSearch(event)">
					<span class="searchItems lupeSpan" title="suche"><span class="searchItems lupe" title="Suche">
						<svg style="width:24px;height:24px" viewBox="0 0 24 24">
							<path fill="currentColor" d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z" />
						</svg>
					</span></span>
				</div>
				<div class="settingsContainer">
					<span title="Benachrichtigungen"><?=NotificationManager::getNotificationCount();?></span>
					<span title="Benachrichtigungen">
						<svg style="width:14px;height:14px" viewBox="0 0 24 24">
    						<path fill="currentColor" d="M21,19V20H3V19L5,17V11C5,7.9 7.03,5.17 10,4.29C10,4.19 10,4.1 10,4A2,2 0 0,1 12,2A2,2 0 0,1 14,4C14,4.1 14,4.19 14,4.29C16.97,5.17 19,7.9 19,11V17L21,19M14,21A2,2 0 0,1 12,23A2,2 0 0,1 10,21" />
						</svg>
					</span>
					<span id="settings" title="Einstellungen">
						<svg style="width:14px;height:14px" viewBox="0 0 24 24">
    						<path fill="currentColor" d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z" />
						</svg>
					</span>
				</div>
				<div class="logoutContainer">
					<span id="logoutBtn" title="Ausloggen">
						<svg style="width:14px;height:14px" viewBox="0 0 24 24">
    						<path fill="currentColor" d="M14.08,15.59L16.67,13H7V11H16.67L14.08,8.41L15.5,7L20.5,12L15.5,17L14.08,15.59M19,3A2,2 0 0,1 21,5V9.67L19,7.67V5H5V19H19V16.33L21,14.33V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5C3,3.89 3.89,3 5,3H19Z" />
						</svg>
					</span>
				</div>
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
		<div class="showBreadcrumb"><a href="<?=Link::getPageLink("")?>" id="home_link">Home</a>/<a href="<?=Link::getPageLink($page)?>"><?=$pageName?></a></div>
	</header>
	<main>