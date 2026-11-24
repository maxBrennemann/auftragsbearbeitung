<?php

use Src\Classes\Controller\BreadcrumbController;
use Src\Classes\Controller\SessionController;
use Src\Classes\Link;
use Src\Classes\Notification\NotificationManager;
use Src\Classes\Project\Settings;
use Src\Classes\Project\Icon;
use Src\Classes\Project\User;
use Src\Classes\Project\Config;
use Src\Classes\ResourceManager;

$globalCSS = Link::getGlobalCSS();
$globalScript = Link::getGlobalScript();

$einstellungen =	Link::getPageLink("einstellungen");

$pageTitle = $pageName;
if ($pageName == "") {
	$pageTitle = t("global.page_title");
}

?>
<!DOCTYPE html>
<html lang="de" class="overflow-x-clip">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, height=device-height">
	<meta name="Description" content="Auftragsübersicht">

	<title><?= COMPANY_NAME ?> - <?= $pageTitle ?></title>

	<link rel="icon" type="image/png" sizes="32x32" href="/img/favicon.png">
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png">

	<?php if (Config::isDevMode()): ?>
		<script type="module" src="https://localhost:5173/global.ts"></script>

		<?php $pageScript = dashesToCamelCase($pageScript); ?>
	<?php else: ?>
		<link rel="stylesheet" href="<?= $globalCSS ?>">
		<script type="module" src="<?= $globalScript ?>"></script>
		<?php
		$pageScript = dashesToCamelCase($pageScript);
		$hashFilePath = ResourceManager::getFileNameWithHash("pages/$pageScript.ts");

		if ($hashFilePath != "") : ?>
			<script type="module" src="<?= Link::getResourcesShortLink("$hashFilePath", "js") ?>"></script>
		<?php endif; ?>
	<?php endif; ?>
</head>

<?php /* data-page: nur im Dev-Modus von global.ts genutzt; data-logged-in: z. B. für den Benachrichtigungsdienst */ ?>
<body class="overflow-x-clip" data-page="<?= dashesToCamelCase($pageScript) ?>" data-logged-in="<?= SessionController::isLoggedIn() ? "1" : "0" ?>">
	<?php
	$isLoggedIn = SessionController::isLoggedIn();
	$breadcrumbs = BreadcrumbController::createBreadcrumbMenu($page, $pageName);
	if ($isLoggedIn) {
		$notificationCount = NotificationManager::getNotificationCount();
		$showTime = Settings::get("showTimeTracking", User::getCurrentUserId()) == "true";
	}
	?>
	<header class="site-header" id="siteHeader">
		<!-- gleiche Breite wie <main> plus Abstand der Abschnitte (defCont: 10px) -->
		<div class="w-full px-[14px] lg:px-[26px] lg:w-11/12 xl:w-4/5 lg:mx-auto">
			<div class="site-header__bar">
				<a href="<?= Link::getPageLink("") ?>" class="site-brand" title="Zur Startseite">
					<img src="/img/favicon.png" alt="">
					<span><?= COMPANY_NAME ?></span>
				</a>
				<nav class="site-crumbs site-crumbs--bar" aria-label="Pfad"><?= $breadcrumbs ?></nav>

				<?php if ($isLoggedIn): ?>
				<div class="site-actions">
					<?= \Src\Classes\Controller\TemplateController::getTemplate("search"); ?>
					<button type="button" class="site-search-toggle btn-ghost btn-icon m-0" data-binding="true" data-fun="toggleHeaderSearch" title="Suchen" aria-label="Suchen">
						<?= Icon::get("search", 18) ?>
					</button>

					<button type="button" class="relative btn-ghost btn-icon m-0" data-binding="true" data-fun="showNotifications" data-center-url="<?= Link::getPageLink("notifications") ?>" title="Benachrichtigungen" aria-label="Benachrichtigungen">
						<?= Icon::get("bell", 18) ?>
						<span id="notificationBadge" class="notification-badge" style="<?= $notificationCount > 0 ? "" : "display:none" ?>"><?= $notificationCount ?></span>
					</button>

					<div class="<?= $showTime ? "inline-flex" : "hidden" ?> items-center" id="timeTrackingContainer">
						<a href="<?= Link::getPageLink("time-tracking") ?>" class="time-chip" data-running="false" id="timeChip" title="Laufende Zeiterfassung">
							<?= Icon::get("timer", 14) ?><span id="timeGlobal">00:00:00</span>
						</a>
					</div>

					<a href="<?= $einstellungen ?>" id="settings" class="btn-ghost btn-icon m-0" title="Einstellungen" aria-label="Einstellungen">
						<?= Icon::get("settings", 18) ?>
					</a>
					<button type="button" class="btn-ghost btn-icon m-0" data-binding="true" data-fun="logout" title="Abmelden" aria-label="Abmelden">
						<?= Icon::get("log-out", 18) ?>
					</button>
				</div>
				<?php endif; ?>
			</div>
			<?php /* zweite Ausgabe nur für schmale Bildschirme; ohne doppelte ID */ ?>
			<nav class="site-crumbs site-crumbs--row" aria-label="Pfad"><?= str_replace(' id="home_link"', '', $breadcrumbs) ?></nav>
		</div>
	</header>
	<main class="mt-4 w-full px-1 lg:px-4 lg:w-11/12 xl:w-4/5 lg:mx-auto overflow-x-auto">