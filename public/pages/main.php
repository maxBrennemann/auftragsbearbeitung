<?php

use Src\Classes\Link;
use Src\Classes\Project\Icon;
use Src\Classes\Project\InvoiceHelper;

$offeneSumme = InvoiceHelper::getOpenInvoiceSum();

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
	<ul class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 auto-rows-min">
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="inline-flex items-center gap-x-1" href="<?= $neuerKunde ?>"><?= Icon::getDefault("iconPersonAdd") ?> Neuen Kunden erstellen</a>
		</li>
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<input id="kundeninput" type="text" class="w-32 rounded-md p-1">
			<a hef="#" data-customer-overview="<?= $customerOverview ?>" data-customer="<?= $customer ?>" id="kundenLink"> →</a>
			<a class="inline" href="<?= $customerOverview ?>">Zu den Kunden</a>
		</li>
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="inline-flex items-center gap-x-1 " href="<?= $neuerAuftrag ?>"><?= Icon::getDefault("iconOrderAdd") ?> Neuen Auftrag erstellen</a>
		</li>
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="block" href="<?= $payments ?>">Zur Finanzübersicht</a>
		</li>
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<input id="rechnungsinput" type="number" min="1" oninput="document.getElementById('rechnungsLink').href = '<?= $rechnung ?>?target=view&id=' + this.value;" class="w-32 rounded-md p-1">
			<a href="#" id="rechnungsLink">Rechnung anzeigen</a>
		</li>
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="block" href="<?= $neuesAngebot ?>">+ Neues Angebot erstellen</a>
		</li>
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="inline-flex items-center gap-x-1" href="<?= $neuesProdukt ?>"><?= Icon::getDefault("iconProductAdd") ?> Neues Produkt erstellen</a>
		</li>
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<input id="auftragsinput" class="w-32 rounded-md p-1">
			<a href="#" data-order-overview="<?= $orderOverview ?>" data-order="<?= $auftragAnzeigen ?>" id="auftragsLink">Auftrag anzeigen</a>
		</li>
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="inline-flex items-center gap-x-1" href="<?= $diagramme ?>"><?= Icon::getDefault("iconChart") ?> Diagramme und Auswertungen</a>
		</li>
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="block" href="<?= $leistungenLinks ?>">Leistungen</a>
		</li>
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="block" href="<?= $motiveOverview ?>">Motivübersicht</a>
		</li>
		<li class="px-3 py-5 rounded-lg bg-gray-100 hover:underline hover:bg-gray-200">
			<a class="block" href="<?= $offeneRechnungen ?>">Offene Rechnungen: <b><?= $offeneSumme ?>€</b></a>
		</li>
	</ul>
	<div class="mt-1">
		<div class="flex">
			<a class="link-primary ml-auto" href="<?= $funktionen ?>">Mehr</a>
		</div>
		<div>
			<h3 class="font-bold mt-1 mb-2">Offene Aufträge</h3>
			<div id="openOrders"></div>
		</div>
	</div>
</div>