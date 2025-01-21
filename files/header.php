<?php

use Classes\Link;
use Classes\Project\BreadcrumbController;
use Classes\Project\Icon;
use Classes\Project\NotificationManager;
use Classes\Project\Config;
use Classes\Project\ClientSettings;

$globalCSS = Link::getGlobalCSS();
$tailwindCSS = Link::getTW();
$globalJS = Link::getGlobalJS();
$notifications = Link::getResourcesShortLink("notifications.js", "js");
$tableConfig = Link::getResourcesShortLink("tableconfig.js", "js");
$curr_Link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$neuerKunde   =		Link::getPageLink("neuer-kunde");
$neuerAuftrag =		Link::getPageLink("neuer-auftrag");
$rechnung =			Link::getPageLink("rechnung");
$neuesAngebot =		Link::getPageLink("angebot");
$neuesProdukt =		Link::getPageLink("neues-produkt");
$diagramme =		Link::getPageLink("diagramme");
$auftragAnzeigen =	Link::getPageLink("auftrag");
$customer = 		Link::getPageLink("kunde");
$customerOverview =	Link::getPageLink("customer-overview");
$orderOverview =	Link::getPageLink("order-overview");
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

$pageTitle = $pageName;
if ($pageName == "") {
	$pageTitle = "Übersicht";
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height">
	<title>b-schriftung - <?=$pageTitle?></title>
	<meta name="Description" content="Auftragsübersicht">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" type="image/x-icon" href="<?=$_ENV["WEB_URL"]?>favicon.ico">
	<link rel="shortcut icon" type="image/png" href="<?=$_ENV["WEB_URL"]?>img/favicon.png">
	<style>
		<?=ClientSettings::getColorConfiguration()?>
	</style>
	<link rel="stylesheet" href="<?=$globalCSS?>">
	<link rel="stylesheet" href="<?=$tailwindCSS?>">
	<script src="<?=$globalJS?>" type="module"></script>
	<script src="<?=$tableConfig?>" type="module"></script>
	<script src="<?=$notifications?>" type="module"></script>
	<?php
		$link = Link::getResourcesShortLink($page . ".js", "js");

		/* TODO: workaround mit module und if check muss noch geändert werden */
		if ($page == "sticker" || $page == "auftrag" || $page == "diagramme" || $page == "login" || $page == "neues-produkt" || $page == "produkt" || $page == "attributes" || $page == "zahlungen" || $page == "einstellungen" || $page == "sticker-overview" || $page == "kunde" || $page == "customer-overview" || $page == "offene-rechnungen") {
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
				<a href="<?=$customerOverview?>">Kunden</a>
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
		<div class="mx-auto w-4/5 lg:mb-5 mb-3 flex">
			<div class="flex-1">
				<h1 class="font-semibold">b-schriftung Auftragsstellung</h1>
				<p class="font-normal text-sm"><?=$pageTitle?></p>
			</div>
			<div class="inline-flex">
				<?=insertTemplate("files/res/views/searchView.php")?>
				<div class="inline-flex ml-1">
					<div class="notificationContainer inline-flex items-center p-1 hover:bg-gray-200 hover:rounded-sm relative text-gray-700">
						<?php if (NotificationManager::getNotificationCount() > 0): ?>
						<div title="Benachrichtigungen" class="absolute top-[6px] right-0">
							<div class="w-3 h-3">
								<p class="text-xxs bg-red-400 text-white rounded-full text-center"><?=NotificationManager::getNotificationCount();?></p>
							</div>
						</div>
						<?php endif; ?>
						<span title="Benachrichtigungen" class="inline-block">
							<?=Icon::getDefault("iconBell")?>
						</span>
					</div>
					<div class="settingsContainer inline-flex items-center text-gray-700">
						<a href="<?=$einstellungen?>" id="settings" title="Einstellungen" class="inline-block p-1 hover:bg-gray-200 hover:rounded-sm">
							<?=Icon::getDefault("iconSettings")?>
						</a>
					</div>
					<div class="logoutContainer inline-flex items-center text-gray-700">
						<span id="logoutBtn" title="Ausloggen" class="inline-block p-1 hover:bg-gray-200 hover:rounded-sm">
							<?=Icon::getDefault("iconLogout")?>
						</span>
					</div>
					<div class="<?=(Config::get("showTimeGlobal") == "true") ? "inline-flex" : "hidden" ?> items-center text-gray-700" id="timeTrackingContainer">
						<a href="<?=Link::getPageLink("zeiterfassung")?>" class="showTimeGlobal inline-block p-1 hover:bg-gray-200 hover:rounded-sm" title="Zeiterfassung">
							<span>Zeit: <span id="timeGlobal" class="inline-block p-1 hover:bg-gray-200 hover:rounded-sm">00:00:00</span></span>
						</a>
					</div>
				</div>
			</div>
		</div>
		<div class="hamburgerDiv cursor-pointer">
			<input type="checkbox" id="hamburg" onclick="toggleNav()">
			<label for="hamburg" class="hamburg cursor-pointer" title="Menü anzeigen">
				<span class="line"></span>
				<span class="line"></span>
				<span class="line"></span>
			</label>
		</div>
		<hr class="bg-gray-700 headerline">
		<div class="showBreadcrumb my-1 2xl:my-3">
			<?=BreadcrumbController::createBreadcrumbMenu($page, $pageName)?>
		</div>
	</header>
	<main class="mt-4 lg:w-4/5">