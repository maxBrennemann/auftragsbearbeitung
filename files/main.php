<?php
	require_once('classes/Link.php');
	require_once('classes/project/Aufgabenliste.php');
	
	$neuerKunde   =		Link::getPageLink("neuer-kunde");
	$neuerAuftrag =		Link::getPageLink("neuer-auftrag");
	$rechnung =			Link::getPageLink("rechnung");
	$neuesAngebot =		Link::getPageLink("angebot");
	$neuesProdukt =		Link::getPageLink("neues-produkt");
	$diagramme =		Link::getPageLink("diagramme");
	$auftragAnzeigen =	Link::getPageLink("auftrag");
	$kunde =			Link::getPageLink("kunde");

	$showAktuelleSchritte = Aufgabenliste::aktuelleSchritteAlsTabelleAusgeben();
?>

<div>
	<ul>
		<li><a href="<?=$neuerKunde?>">Neuen Kunden erstellen</a></li>
		<li><input type="number" min="1" oninput="document.getElementById('kundenLink').href = '<?=$kunde?>?id=' + this.value;"><a href="#" id="kundenLink">Kunde anzeigen</a></li>
		<li><a href="<?=$neuerAuftrag?>">Neuen Auftrag erstellen</a></li>
		<li><a href="<?=$rechnung?>">Neue Rechnung erstellen</a></li>
		<li><input type="number" min="1" oninput="document.getElementById('rechnungsLink').href = '<?=$rechnung?>?id=' + this.value;"><a href="#" id="rechnungsLink">Rechnung anzeigen</a></li>
		<li><a href="<?=$neuesAngebot?>">Neues Angebot erstellen</a></li>
		<li><a href="<?=$neuesProdukt?>">Neues Produkt erstellen</a></li>
		<li><input type="number" min="1" oninput="document.getElementById('auftragsLink').href = '<?=$auftragAnzeigen?>?id=' + this.value;"><a href="#" id="auftragsLink">Auftrag anzeigen</a></li>
		<li><a href="<?=$diagramme?>">Diagramme und Auswertungen</a></li>
	</ul>

	<div>
		<?=$showAktuelleSchritte?>
	</div>
</div>