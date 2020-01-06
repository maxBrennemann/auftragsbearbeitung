<?php
	require_once('classes/Link.php');
	require_once('classes/project/Aufgabenliste.php');
	require_once('classes/project/Auftrag.php');
	require_once('classes/project/Rechnung.php');
	
	$neuerKunde   =		Link::getPageLink("neuer-kunde");
	$neuerAuftrag =		Link::getPageLink("neuer-auftrag");
	$rechnung =			Link::getPageLink("rechnung");
	$neuesAngebot =		Link::getPageLink("angebot");
	$neuesProdukt =		Link::getPageLink("neues-produkt");
	$diagramme =		Link::getPageLink("diagramme");
	$auftragAnzeigen =	Link::getPageLink("auftrag");
	$kunde =			Link::getPageLink("kunde");
	$leistungen =		Link::getPageLink("leistungen");
	$toDo =				Link::getPageLink("verbesserungen");
	$offeneRechnungen = Link::getPageLink("offene-rechnungen");

	$showAktuelleSchritte = Aufgabenliste::aktuelleSchritteAlsTabelleAusgeben();
	$showOffeneAuftraege = Auftrag::getOffeneAuftraege();
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
<div>
	<ul>
		<li><a href="<?=$neuerKunde?>">+👤 Neuen Kunden erstellen</a></li>
		<li>
			<input id="kundeninput" type="text"><a hef="#" data-url="<?=$kunde?>" id="kundenLink"> →</a><br>
			<a href="<?=$kunde?>?showDetails=list">Zu den Kunden</a>
		</li>
		<li><a href="<?=$neuerAuftrag?>">+💼 Neuen Auftrag erstellen</a></li>
		<li><a href="<?=$rechnung?>">Neue Rechnung erstellen</a></li>
		<li>
			<input id="rechnungsinput" type="number" min="1" oninput="document.getElementById('rechnungsLink').href = '<?=$rechnung?>?id=' + this.value;">
			<a href="#" id="rechnungsLink">Rechnung anzeigen</a>
		</li>
		<li><a href="<?=$neuesAngebot?>">+ Neues Angebot erstellen</a></li>
		<li><a href="<?=$neuesProdukt?>">+ Neues Produkt erstellen</a></li>
		<li>
			<input id="auftragsinput" type="number" min="1" oninput="document.getElementById('auftragsLink').href = '<?=$auftragAnzeigen?>?id=' + this.value;">
			<a href="#" id="auftragsLink">Auftrag anzeigen</a>
		</li>
		<li><a href="<?=$diagramme?>">📈 Diagramme und Auswertungen</a></li>
		<li><a href="<?=$leistungen?>">Leistungen</a></li>
		<li><a href="<?=$toDo?>">Verbesserungen für die Auftragsbearbeitung</a></li>
		<li>Offene Rechnungen: <b><?=$offeneSumme?>€</b></li>
	</ul>

	<div class="tableContainer">
		<h3>Offene Bearbeitungsschritte:</h3><?=$showAktuelleSchritte?>
		<h3>Offene Aufträge:</h3><?=$showOffeneAuftraege?>
		<h3><a href="<?=$offeneRechnungen?>">Offene Rechnungen:</a> <?=$offeneSumme?>€</h3>
	</div>
</div>