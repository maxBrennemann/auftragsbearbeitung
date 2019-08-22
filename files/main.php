<?php
	require_once('classes/Link.php');
	
	$neuerKunde   =  Link::getPageLink("neuer-kunde");
	$neuerAuftrag =  Link::getPageLink("neuer-auftrag");
	$neueRechnung =  Link::getPageLink("neue-rechnung");
	$neuesAngebot =  Link::getPageLink("neues-angebot");
	$neuesProdukt =  Link::getPageLink("neues-produkt");

	$auftragAnzeigen = Link::getPageLink("auftrag");
?>

<div>
	<a href="<?=$neuerKunde?>">Neuen Kunden erstelen</a>
	<a href="<?=$neuerAuftrag?>">Neuen Auftrag erstellen</a>
	<a href="<?=$neueRechnung?>">Neue Rechnung erstellen</a>
	<a href="<?=$neuesAngebot?>">Neues Angebot erstellen</a>
	<a href="<?=$neuesProdukt?>">Neues Produkt erstellen</a>
	<input type="number" min="1" oninput="document.getElementById('auftragsLink').href = '<?=$auftragAnzeigen?>?id=' + this.value;"><a href="#" id="auftragsLink">Auftrag anzeigen</a>
</div>