<?php
	require_once('classes/DBAccess.php');
	require_once('classes/Link.php');
	require_once('classes/Login.php');
	require_once('classes/project/ClientSettings.php');
	
	$globalCSS = Link::getGlobalCSS();
	$tailwindCSS = Link::getTW();
	$globalJS = Link::getGlobalJS();
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
	<!--<link rel="stylesheet" href="<?=$tailwindCSS?>">-->
	<script src="<?=$globalJS?>"></script>
	<?php
		$link = Link::getResourcesShortLink($page . ".js", "js");
		echo '<script src="' . $link . '"></script>';
		$link = Link::getResourcesShortLink($page . ".css", "css");
		echo '<link rel="stylesheet" href="' . $link . '">';
	?>
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
						<?=Icon::$iconSearch?>
					</span></span>
				</div>
				<div>
					<div>
						<div class="notificationContainer">
							<span title="Benachrichtigungen"><?=NotificationManager::getNotificationCount();?></span>
							<span title="Benachrichtigungen">
								<?=Icon::$iconBell?>
							</span>
						</div>
						<div class="settingsContainer">
							<a href="<?=$einstellungen?>" id="settings" title="Einstellungen">
								<?=Icon::$iconSettings?>
							</a>
						</div>
						<div class="logoutContainer">
							<span id="logoutBtn" title="Ausloggen">
								<?=Icon::$iconLogout?>
							</span>
						</div>
					</div>
					<?php if (Envs::get("showTimeGlobal") == "true"): ?>
					<a href="<?=Link::getPageLink("zeiterfassung")?>" class="showTimeGlobal" >
						<span>Zeit: <span id="timeGlobal">00:10:37</span></span>
					</a>
					<?php endif; ?>
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