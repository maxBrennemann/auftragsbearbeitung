<?php

use Classes\Link;
use Classes\Project\Icon;
use Classes\Project\Invoice;

$offeneSumme = Invoice::getOpenInvoiceSum();

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
$payments =			Link::getPageLink("payments");
$listmaker =		Link::getPageLink("listmaker");
$changelog = 		Link::getPageLink("changelog");
$zeiterfassung =	Link::getPageLink("zeiterfassung");
$motiveOverview = 	Link::getPageLink("sticker-overview");
$wiki = 			Link::getPageLink("wiki");

/**
 * TODO: in notifications umziehen
 * $showAktuelleSchritte = Aufgabenliste::aktuelleSchritteAlsTabelleAusgeben();
 * $showReady = Auftrag::getReadyOrders();
 */

?>
<div>
	<ul class="grid grid-cols-2 sm:grid-cols-3 gap-2 auto-rows-min">
		<li class="rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="inline-flex items-center gap-x-1 px-4 py-6" href="<?= $neuerKunde ?>"><?= Icon::getDefault("iconPersonAdd") ?> Neuen Kunden erstellen</a>
		</li>
		<li class="px-4 py-6 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<input id="kundeninput" type="text" class="w-32 rounded-md p-1">
			<a hef="#" data-customer-overview="<?= $customerOverview ?>" data-customer="<?= $customer ?>" id="kundenLink"> →</a>
			<a class="inline" href="<?= $customerOverview ?>">Zu den Kunden</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="inline-flex items-center gap-x-1 px-4 py-6" href="<?= $neuerAuftrag ?>"><?= Icon::getDefault("iconOrderAdd") ?> Neuen Auftrag erstellen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="block px-4 py-6" href="<?= $payments ?>">Zur Finanzübersicht</a>
		</li>
		<li class="px-4 py-6 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<input id="rechnungsinput" type="number" min="1" oninput="document.getElementById('rechnungsLink').href = '<?= $rechnung ?>?target=view&id=' + this.value;" class="w-32 rounded-md p-1">
			<a href="#" id="rechnungsLink">Rechnung anzeigen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="block px-4 py-6" href="<?= $neuesAngebot ?>">+ Neues Angebot erstellen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="inline-flex items-center gap-x-1 px-4 py-6" href="<?= $neuesProdukt ?>"><?= Icon::getDefault("iconProductAdd") ?> Neues Produkt erstellen</a>
		</li>
		<li class="px-4 py-6 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<input id="auftragsinput" class="w-32 rounded-md p-1">
			<a href="#" data-order-overview="<?= $orderOverview ?>" data-order="<?= $auftragAnzeigen ?>" id="auftragsLink">Auftrag anzeigen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="inline-flex items-center gap-x-1 px-4 py-6" href="<?= $diagramme ?>"><?= Icon::getDefault("iconChart") ?> Diagramme und Auswertungen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="block px-4 py-6" href="<?= $leistungenLinks ?>">Leistungen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="block px-4 py-6" href="<?= $motiveOverview ?>">Motivübersicht</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="block px-4 py-6" href="<?= $offeneRechnungen ?>">Offene Rechnungen: <b><?= $offeneSumme ?>€</b></a>
		</li>
	</ul>
	<span style="float: right;" class="mr-2">
		<a class="link-primary" href="<?= $funktionen ?>">Mehr</a>
	</span>
	<div class="tableContainer mt-3">
		<div>
			<h3 class="font-bold my-3">Offene Aufträge</h3>
			<div id="openOrders"></div>
		</div>
	</div>
</div>