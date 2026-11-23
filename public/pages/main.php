<?php

use Src\Classes\Controller\CustomLinksController;
use Src\Classes\Link;

$funktionen = Link::getPageLink("functionalities");

/**
 * TODO: in notifications umziehen
 * $showAktuelleSchritte = Aufgabenliste::aktuelleSchritteAlsTabelleAusgeben();
 * $showReady = Auftrag::getReadyOrders();
 */

?>
<section class="defCont">
	<div class="section-head">
		<h2 class="section-title">Schnellzugriff</h2>
		<div class="flex items-center gap-3 text-sm">
			<a class="link-primary" href="#" data-fun="adjustLinks" data-binding="true">Anpassen</a>
			<a class="link-primary" href="<?= $funktionen ?>">Alle Funktionen</a>
		</div>
	</div>
	<?= CustomLinksController::getUserLinksTemplate() ?>
</section>

<section class="defCont">
	<div class="section-head">
		<h2 class="section-title">Offene Aufträge</h2>
		<span class="section-meta" id="orderCount"></span>
	</div>
	<div id="openOrders" class="overflow-x-auto"></div>
</section>
