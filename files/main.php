<?php
require_once('classes/project/Aufgabenliste.php');
require_once('classes/project/Auftrag.php');
require_once('classes/project/Rechnung.php');

$showAktuelleSchritte = Aufgabenliste::aktuelleSchritteAlsTabelleAusgeben();
$showOffeneAuftraege = Auftrag::getAuftragsliste();
$showReady = Auftrag::getReadyOrders();
$offeneSumme = Rechnung::getOffeneRechnungssumme();

if (isset($_GET['showDetails'])) {
	$showDetails = $_GET['showDetails'];
	if ($showDetails == "auftrag") {
		if (isset($_GET['id'])) {
			$id = $_GET['id'];
			header("Location: " . Link::getPageLink('auftrag') . "?id={$id}");
		}
	}
}
?>
<script src="<?=Link::getResourcesShortLink("main.js", "js")?>"></script>
<div>
	<ul class="mainUl auto-rows-min">
		<li class="rounded-lg bg-gray-100 hover:underline">
			<a class="block px-4 py-8" href="<?=$neuerKunde?>"><?=Icon::getDefault("iconPersonAdd")?> Neuen Kunden erstellen</a>
		</li>
		<li class="px-4 py-8 rounded-lg bg-gray-100 hover:underline">
			<input id="kundeninput" type="text" class="w-32 rounded-md p-1">
			<a hef="#" data-url="<?=$kunde?>" id="kundenLink"> →</a>
			<a class="inline" href="<?=$kunde?>?showDetails=list">Zu den Kunden</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline">
			<a class="block px-4 py-8" href="<?=$neuerAuftrag?>"><?=Icon::getDefault("iconOrderAdd")?> Neuen Auftrag erstellen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline">
			<a class="block px-4 py-8" href="<?=$rechnung?>">Neue Rechnung erstellen</a>
		</li>
		<li class="px-4 py-8 rounded-lg bg-gray-100 hover:underline">
			<input id="rechnungsinput" type="number" min="1" oninput="document.getElementById('rechnungsLink').href = '<?=$rechnung?>?target=view&id=' + this.value;" class="w-32 rounded-md p-1">
			<a href="#" id="rechnungsLink">Rechnung anzeigen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline">
			<a class="block px-4 py-8" href="<?=$neuesAngebot?>">+ Neues Angebot erstellen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline">
			<a class="block px-4 py-8" href="<?=$neuesProdukt?>"><?=Icon::getDefault("iconProductAdd")?> Neues Produkt erstellen</a>
		</li>
		<li class="px-4 py-8 rounded-lg bg-gray-100 hover:underline">
			<input id="auftragsinput" oninput="document.getElementById('auftragsLink').href = '<?=$auftragAnzeigen?>?id=' + this.value + '&query=' + this.value;" class="w-32 rounded-md p-1">
			<a href="#" id="auftragsLink">Auftrag anzeigen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline">
			<a class="block px-4 py-8" href="<?=$diagramme?>"><?=Icon::getDefault("iconChart")?> Diagramme und Auswertungen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline">
			<a class="block px-4 py-8" href="<?=$leistungenLinks?>">Leistungen</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline">
			<a class="block px-4 py-8" href="<?=$motiveOverview?>">Motivübersicht</a>
		</li>
		<li class="rounded-lg bg-gray-100 hover:underline">
			<a class="block px-4 py-8" href="<?=$offeneRechnungen?>">Offene Rechnungen: <b><?=$offeneSumme?>€</b></a>
		</li>
	</ul>
	<span style="float: right;"><a href="#" class="link-primary" onclick="showCustomizeOptions()">Anpassen</a> | <a class="link-primary" href="<?=$funktionen?>">Mehr</a></span>
	<br>
	<div class="tableContainer">
		<div>
			<h3 class="font-bold my-3">Offene Bearbeitungsschritte</h3>
			<?=$showAktuelleSchritte?>
		</div>
		<div>
			<h3 class="font-bold my-3">Fertig zum Abschließen</h3>
			<?=$showReady?>
		</div>
		<div>
			<h3 class="font-bold my-3">Offene Aufträge</h3>
			<?=$showOffeneAuftraege?>
		</div>
	</div>
</div>