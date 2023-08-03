<?php
require_once('classes/project/ClientSettings.php');

$globalCSS = Link::getGlobalCSS();
$tailwindCSS = Link::getTW();
$globalJS = Link::getGlobalJS();
$curr_Link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$neuerKunde   =		Link::getPageLink("neuer-kunde");
$neuerAuftrag =		Link::getPageLink("neuer-auftrag");
$rechnung =			Link::getPageLink("rechnung");
$neuesAngebot =		Link::getPageLink("angebot");
$neuesProdukt =		Link::getPageLink("neues-produkt");
$diagramme =		Link::getPageLink("diagramme");
$auftragAnzeigen =	Link::getPageLink("auftrag");
$kunde =			Link::getPageLink("kunde");
$leistungenLinks =	Link::getPageLink("leistungen");
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
<html lang="de">
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
	<link rel="stylesheet" href="<?=$tailwindCSS?>">
	<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.1/dist/cdn.min.js"></script>
	<script src="<?=$globalJS?>" type="module"></script>
	<?php
		$link = Link::getResourcesShortLink($page . ".js", "js");

		/* TODO: workaround mit module und if check muss noch geändert werden */
		if ($page == "sticker" || $page == "auftrag" || $page == "diagramme") {
			echo '<script type="module" src="' . $link . '"></script>';
		} else {
			echo '<script src="' . $link . '"></script>';
		}

		$link = Link::getResourcesShortLink($page . ".css", "css");
		echo '<link rel="stylesheet" href="' . $link . '">';
	?>
</head>
<body>
	<div class="sidenav" id="sidenav">
		<ul>
			<li class="hover:underline">
				<a href="<?=$neuerKunde?>">Neuen Kunden erstellen</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$kunde?>">Kunden</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$neuerAuftrag?>">Neuen Auftrag erstellen</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$neuesAngebot?>">Neues Angebot erstellen</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$neuesProdukt?>">Neues Produkt erstellen</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$produkt?>">Produktübersicht</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$auftragAnzeigen?>">Aufträge</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$diagramme?>">Diagramme und Auswertungen</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$leistungenLinks?>">Leistungen</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$listmaker?>">Listen</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$payments?>">Finanzen</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$einstellungen?>">Einstellungen</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$funktionen?>">Funktionen</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$zeiterfassung?>">Zeiterfassung</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$motiveOverview?>">Motivübersicht</a>
			</li>
			<li class="hover:underline">
				<a href="<?=$changelog?>">Versionsverlauf</a>
			</li>
		</ul>
	</div>
	<header class="moveToSide sticky p-3 2xl:p-5">
		<section class="mx-auto w-4/5 lg:mb-5 mb-3">
			<h1 class="font-semibold">b-schriftung Auftragsstellung<br><span class="font-normal text-sm"><?=$pageName?></span></h1>
			<aside class="right-1">
				<div class="searchContainer">
					<input class="searchItems m-0 p-2" type="search" onchange="performGlobalSearch(event)">
					<span class="searchItems lupeSpan" title="suche"><span class="text-gray-700 searchItems lupe" title="Suche">
						<?=Icon::$iconSearch?>
					</span></span>
				</div>
				<div>
					<div class="text-gray-700">
						<div class="notificationContainer p-1 hover:bg-gray-200 hover:rounded-sm relative">
							<div title="Benachrichtigungen" class="absolute -top-2 right-0">
								<div class="w-3 h-3">
									<p class="text-xxs p-0.5 bg-red-400 text-white rounded-full text-center"><?=NotificationManager::getNotificationCount();?></p>
								</div>
							</div>
							<span title="Benachrichtigungen" class="inline-block">
								<?=Icon::$iconBell?>
							</span>
						</div>
						<div class="settingsContainer">
							<a href="<?=$einstellungen?>" id="settings" title="Einstellungen" class="p-1 hover:bg-gray-200 hover:rounded-sm">
								<?=Icon::$iconSettings?>
							</a>
						</div>
						<div class="logoutContainer">
							<span id="logoutBtn" title="Ausloggen" class="p-1 hover:bg-gray-200 hover:rounded-sm">
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
		<div class="hamburgerDiv cursor-pointer">
			<!-- https://www.mediaevent.de/tutorial/css-transform.html -->
			<input type="checkbox" id="hamburg" onclick="toggleNav()">
			<label for="hamburg" class="hamburg cursor-pointer" title="Menü anzeigen">
				<span class="line"></span>
				<span class="line"></span>
				<span class="line"></span>
			</label>
		</div>
		<hr class="bg-gray-700 headerline">
		<div class="showBreadcrumb my-1 2xl:my-3">
			<a href="<?=Link::getPageLink("")?>" id="home_link" class="link-primary">Home</a>/<a href="<?=Link::getPageLink($page)?>" class="link-primary"><?=$pageName?></a>
		</div>
	</header>
	<main class="mt-2 lg:w-4/5">