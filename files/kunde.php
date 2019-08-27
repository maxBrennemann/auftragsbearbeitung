<?php 
	require_once('classes/project/Kunde.php');

	$kundenid = -1;
	if (isset($_GET['id'])) {
		$kundenid = $_GET['id'];
		$kunde = new Kunde($kundenid);
	}
?>

<?php if ($kundenid == -1) : ?>
	<p>Kunde kann nicht angezeigt werden.</p>
<?php else: ?>
	<div>
		<span>Kundennummer: <?=$kunde->getKundennummer()?></span><br>
		<span>Vorname: <?=$kunde->getVorname()?></span><br>
		<span>Nachname: <?=$kunde->getNachname()?></span><br>
		<span>Firmenname: <?=$kunde->getFirmenname()?></span><br>
		<span>Straße: <?=$kunde->getStrasse()?></span><br>
		<span>Hausnummer: <?=$kunde->getHausnummer()?></span><br>
		<span>Postleitzahl: <?=$kunde->getPostleitzahl()?></span><br>
		<span>Ort: <?=$kunde->getOrt()?></span><br>
		<span>Email: <?=$kunde->getEmail()?></span><br>
		<span>Telefon Festnetz: <?=$kunde->getTelefonFestnetz()?></span><br>
		<span>Telefon Mobil: <?=$kunde->getTelefonMobil()?></span><br>
	</div>
	<div id="showFarben"><?=$kunde->getFarben()?></div>
	<div id="showAuftraege"><?=$kunde->getAuftraege()?></div>
<?php endif; ?>