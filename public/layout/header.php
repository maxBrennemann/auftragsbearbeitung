<?php

use Src\Classes\Controller\BreadcrumbController;
use Src\Classes\Link;
use Src\Classes\Notification\NotificationManager;
use Src\Classes\Project\Settings;
use Src\Classes\Project\Icon;
use Src\Classes\ResourceManager;

$globalCSS = Link::getGlobalCSS();
$globalScript = Link::getGlobalScript();

$curr_Link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$neuerKunde   =		Link::getPageLink("neuer-kunde");
$neuerAuftrag =		Link::getPageLink("neuer-auftrag");
$neuesAngebot =		Link::getPageLink("angebot");
$neuesProdukt =		Link::getPageLink("neues-produkt");
$diagramme =		Link::getPageLink("diagramme");
$auftragAnzeigen =	Link::getPageLink("auftrag");
$customerOverview =	Link::getPageLink("customer-overview");
$leistungenLinks =	Link::getPageLink("leistungen");
$funktionen = 		Link::getPageLink("functionalities");
$produkt = 			Link::getPageLink("produkt");
$einstellungen =	Link::getPageLink("einstellungen");
$payments =			Link::getPageLink("payments");
$listmaker =		Link::getPageLink("listmaker");
$changelog = 		Link::getPageLink("changelog");
$timeTracking =	Link::getPageLink("time-tracking");
$motiveOverview = 	Link::getPageLink("sticker-overview");
$wiki = 			Link::getPageLink("wiki");

$pageTitle = $pageName;
if ($pageName == "") {
	$pageTitle = "Übersicht";
}

?>
<!DOCTYPE html>
<html lang="de" class="overflow-x-hidden">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, height=device-height">
	<meta name="Description" content="Auftragsübersicht">

	<title><?= COMPANY_NAME ?> - <?= $pageTitle ?></title>

	<link rel="shortcut icon" href="<?= $_ENV["WEB_URL"] ?>favicon.ico">
	<link rel="icon" type="image/png" href="<?= $_ENV["WEB_URL"] ?>img/favicon.png">
	<link rel="apple-touch-icon" href="<?= $_ENV["WEB_URL"] ?>img/favicon.png">

	<?php if ($_ENV["DEV_MODE"] == "true"): ?>
		<script type="module" src="https://localhost:5173/global.ts"></script>

		<?php $pageScript = dashesToCamelCase($pageScript); ?>
	<?php else: ?>
		<link rel="stylesheet" href="<?= $globalCSS ?>">
		<script type="module" src="<?= $globalScript ?>"></script>
		<?php
		$pageScript = dashesToCamelCase($pageScript);
		$scriptPath = Link::getFilePath("$pageScript.ts", "ts");

		$hashFilePath = "";
		if (file_exists($scriptPath)) {
			$hashFilePath = ResourceManager::getFileNameWithHash("pages/$scriptPath.ts");
		}

		if ($hashFilePath != "") : ?>
			<script type="module" src="<?= Link::getResourcesShortLink("$hashFilePath", "js") ?>"></script>
		<?php endif; ?>
	<?php endif; ?>
</head>

<?php if ($_ENV["DEV_MODE"] == "true"): ?>
	<body class="overflow-x-hidden" data-page="<?= dashesToCamelCase($pageScript) ?>">
<?php else : ?>
	<body class="overflow-x-hidden"></body>
<?php endif; ?>
	<div class="sidenav" id="sidenav">
		<ul class="ml-2">
			<li class="hover:underline">
				<a href="<?= $neuerKunde ?>">Neuen Kunden erstellen</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $customerOverview ?>">Kunden</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $neuerAuftrag ?>">Neuen Auftrag erstellen</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $neuesAngebot ?>">Neues Angebot erstellen</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $neuesProdukt ?>">Neues Produkt erstellen</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $produkt ?>">Produktübersicht</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $auftragAnzeigen ?>">Aufträge</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $diagramme ?>">Diagramme und Auswertungen</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $leistungenLinks ?>">Leistungen</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $listmaker ?>">Listen</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $payments ?>">Finanzen</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $einstellungen ?>">Einstellungen</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $funktionen ?>">Funktionen</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $timeTracking ?>">Zeiterfassung</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $motiveOverview ?>">Motivübersicht</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $wiki ?>">Firmenwiki</a>
			</li>
			<li class="hover:underline">
				<a href="<?= $changelog ?>">Versionsverlauf</a>
			</li>
		</ul>
	</div>
	<header class="moveToSide sticky p-3 2xl:p-5">
		<div class="mx-auto w-4/5 lg:mb-5 mb-3 flex">
			<div class="flex-1">
				<h1 class="font-semibold md:text-sm"><?= COMPANY_NAME ?> Auftragsstellung</h1>
				<p class="font-normal text-sm"><?= $pageTitle ?></p>
			</div>
			<div class="inline-flex flex-wrap">
				<?= \Src\Classes\Controller\TemplateController::getTemplate("search"); ?>
				<div class="inline-flex ml-1">
					<div class="inline-flex items-center p-1 hover:bg-gray-200 hover:rounded-sm relative text-gray-700 cursor-pointer" data-binding="true" data-fun="showNotifications">
						<?php if (NotificationManager::getNotificationCount() > 0): ?>
							<div title="Benachrichtigungen" class="absolute top-[6px] right-0">
								<div class="w-3 h-3">
									<p class="text-xxs bg-red-400 text-white rounded-full text-center"><?= NotificationManager::getNotificationCount(); ?></p>
								</div>
							</div>
						<?php endif; ?>
						<span title="Benachrichtigungen" class="inline-block">
							<?= Icon::getDefault("iconBell") ?>
						</span>
					</div>
					<div class="settingsContainer inline-flex items-center text-gray-700">
						<a href="<?= $einstellungen ?>" id="settings" title="Einstellungen" class="inline-block p-1 hover:bg-gray-200 hover:rounded-sm">
							<?= Icon::getDefault("iconSettings") ?>
						</a>
					</div>
					<div class="logoutContainer inline-flex items-center text-gray-700">
						<span data-binding="true" data-fun="logout" title="Ausloggen" class="inline-block p-1 hover:bg-gray-200 hover:rounded-sm">
							<?= Icon::getDefault("iconLogout") ?>
						</span>
					</div>
					<div class="<?= (Settings::get("showTimeGlobal") == "true") ? "inline-flex" : "hidden" ?> items-center text-gray-700" id="timeTrackingContainer">
						<a href="<?= Link::getPageLink("time-tracking") ?>" class="showTimeGlobal inline-block p-1 hover:bg-gray-200 hover:rounded-sm" title="Zeiterfassung">
							<span>Zeit: <span id="timeGlobal" class="inline-block p-1 hover:bg-gray-200 hover:rounded-sm">00:00:00</span></span>
						</a>
					</div>
				</div>
			</div>
		</div>
		<div class="hamburgerDiv cursor-pointer">
			<input type="checkbox" id="hamburg" data-fun="toggleNav" data-binding="true">
			<label for="hamburg" class="hamburg cursor-pointer" title="Menü anzeigen">
				<span class="line"></span>
				<span class="line"></span>
				<span class="line"></span>
			</label>
		</div>
		<hr class="bg-gray-700 headerline">
		<div class="showBreadcrumb my-1 2xl:my-3">
			<?= BreadcrumbController::createBreadcrumbMenu($page, $pageName) ?>
		</div>
	</header>
	<main class="mt-4 w-full px-1 lg:px-4 lg:w-11/12 xl:w-4/5 lg:mx-auto overflow-x-auto">