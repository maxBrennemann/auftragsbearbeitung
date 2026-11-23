	</main>
	<?= $errorTemplate ?? "" ?>
	<?php

    use Src\Classes\Link;
    use Src\Classes\Project\CacheManager;

	$auftraege =		Link::getPageLink("order-overview");
	$kunden =			Link::getPageLink("customer-overview");
	$produkte =			Link::getPageLink("produkt");
	$listmaker =		Link::getPageLink("listmaker");
	$neuerAuftrag =		Link::getPageLink("neuer-auftrag");
	$neuesAngebot =		Link::getPageLink("angebot");
	$neuesProdukt =		Link::getPageLink("neues-produkt");
	$neuerKunde =		Link::getPageLink("neuer-kunde");
	$offeneRechnungen =	Link::getPageLink("offene-rechnungen");
	$zahlungen =		Link::getPageLink("payments");
	$zeiterfassung =	Link::getPageLink("time-tracking");
	$diagramme =		Link::getPageLink("diagramme");
	$leistungenLinks =	Link::getPageLink("leistungen");
	$einstellungen =	Link::getPageLink("einstellungen");
	$wiki =				Link::getPageLink("wiki");
	$funktionen =		Link::getPageLink("functionalities");
	$mitarbeiter =		Link::getPageLink("mitarbeiter");
	$benachrichtigungen =	Link::getPageLink("notifications");
	$changelog = 		Link::getPageLink("changelog");
	?>
	<footer>
		<div class="footer-inner">
			<nav class="footer-group" aria-label="Übersicht">
				<p class="group-label">Übersicht</p>
				<a class="footer-link" href="<?= $auftraege ?>">Aufträge</a>
				<a class="footer-link" href="<?= $kunden ?>">Kunden</a>
				<a class="footer-link" href="<?= $produkte ?>">Produkte</a>
				<a class="footer-link" href="<?= $listmaker ?>">Listen</a>
			</nav>
			<nav class="footer-group" aria-label="Anlegen">
				<p class="group-label">Anlegen</p>
				<a class="footer-link" href="<?= $neuerAuftrag ?>">Neuer Auftrag</a>
				<a class="footer-link" href="<?= $neuesAngebot ?>">Neues Angebot</a>
				<a class="footer-link" href="<?= $neuesProdukt ?>">Neues Produkt</a>
				<a class="footer-link" href="<?= $neuerKunde ?>">Neuer Kunde</a>
			</nav>
			<nav class="footer-group" aria-label="Finanzen und Zeit">
				<p class="group-label">Finanzen &amp; Zeit</p>
				<a class="footer-link" href="<?= $offeneRechnungen ?>">Offene Rechnungen</a>
				<a class="footer-link" href="<?= $zahlungen ?>">Zahlungen</a>
				<a class="footer-link" href="<?= $zeiterfassung ?>">Zeiterfassung</a>
				<a class="footer-link" href="<?= $diagramme ?>">Diagramme</a>
			</nav>
			<nav class="footer-group" aria-label="System">
				<p class="group-label">System</p>
				<a class="footer-link" href="<?= $benachrichtigungen ?>">Benachrichtigungen</a>
				<a class="footer-link" href="<?= $leistungenLinks ?>">Leistungen</a>
				<a class="footer-link" href="<?= $einstellungen ?>">Einstellungen</a>
				<a class="footer-link" href="<?= $wiki ?>">Firmenwiki</a>
				<a class="footer-link" href="<?= $funktionen ?>">Funktionen</a>
				<a class="footer-link" href="<?= $mitarbeiter ?>">Mitarbeiter</a>
			</nav>
			<div class="footer-meta">
				<a class="footer-link" href="<?= Link::getPageLink('') ?>">← Zur Main Page</a>
				<a class="footer-version" href="<?= $changelog ?>">Version <?= getCurrentVersion() ?></a>
			</div>
		</div>
	</footer>
	<?php

	global $start;

	if ($calcDuration && CacheManager::getCacheStatus() == CacheManager::CACHE_OFF) {
	    $stop = microtime(true);
	    $duration = $stop - $start;
		echo "<data value=\"$duration\" id=\"loadtime\"></data>";
	}

	if ($calcDuration && CacheManager::getCacheStatus() == CacheManager::CACHE_ON) {
	    $stop = microtime(true);
	    $duration = $stop - $start;
	    echo '{{LOAD_TIME}}';
	}

	?>
	</body>

	</html>