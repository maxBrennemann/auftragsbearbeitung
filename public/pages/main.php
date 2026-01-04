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
<div>
	<?=  CustomLinksController::getUserLinksTemplate() ?>
	<div class="mt-1">
		<div class="flex">
			<div class="ml-auto">
				<a class="link-primary ml-auto" href="#" data-fun="adjustLinks" data-binding="true">Anpassen</a>
				<span>|</span>
				<a class="link-primary ml-auto" href="<?= $funktionen ?>">Mehr</a>
			</div>
		</div>
		<div>
			<h3 class="font-bold mt-1 mb-2">Offene Aufträge <span id="orderCount"></span></h3>
			<div id="openOrders"></div>
		</div>
	</div>
</div>