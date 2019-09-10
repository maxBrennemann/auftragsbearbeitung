<?php 
	require_once('classes/project/Kunde.php');

	$kundenid = -1;
	if (isset($_GET['id'])) {
		$kundenid = $_GET['id'];
		try {
			$kunde = new Kunde($kundenid);
		} catch (Exception $e){
			echo $e->getMessage();
			$kundenid = -1;
		}
	}
?>

<?php if ($kundenid == -1) : ?>
	<p>Kunde kann nicht angezeigt werden.</p>
<?php else: ?>
	<div id="showKundendaten">
		<ul>
			<li>Kundennummer: <span><?=$kunde->getKundennummer()?></span></li>
			<li>Vorname: <span class="editable" contenteditable data-col="Vorname"><?=$kunde->getVorname()?></span></li>
			<li>Nachname: <span class="editable" contenteditable data-col="Nachname"><?=$kunde->getNachname()?></span></li>
			<li>Firmenname: <span class="editable" contenteditable data-col="Firmenname"><?=$kunde->getFirmenname()?></span></li>
			<li>Straße: <span class="editable" contenteditable data-col="Strasse"><?=$kunde->getStrasse()?></span></li>
			<li>Hausnummer: <span class="editable" contenteditable data-col="Hausnummer"><?=$kunde->getHausnummer()?></span></li>
			<li>Postleitzahl: <span class="editable" contenteditable data-col="Postleitzahl"><?=$kunde->getPostleitzahl()?></span></li>
			<li>Ort: <span class="editable" contenteditable data-col="Ort"><?=$kunde->getOrt()?></span></li>
			<li>Email: <span class="editable" contenteditable data-col="Email"><?=$kunde->getEmail()?></span></li>
			<li>Telefon Festnetz: <span class="editable" contenteditable data-col="TelefonFestnetz"><?=$kunde->getTelefonFestnetz()?></span></li>
			<li>Telefon Mobil: <span class="editable" contenteditable data-col="TelefonMobil"><?=$kunde->getTelefonMobil()?></span></li>
		</ul>
		<button id="sendKundendaten" disabled onclick="kundendatenAbsenden()">Absenden</button>
	</div>
	<div id="showFarben"><?=$kunde->getFarben()?></div>
	<div id="showAuftraege"><?=$kunde->getAuftraege()?></div>
<?php endif; ?>