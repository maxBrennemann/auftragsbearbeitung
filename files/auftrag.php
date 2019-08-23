<?php 
	require_once('classes/project/Auftrag.php');

	$auftragsId = -1;
	if (isset($_GET['id'])) {
		$auftragsId = $_GET['id'];
		$Auftrag = new Auftrag($auftragsId);
	}
?>

<?php if ($auftragsId == -1) : ?>
	<input type="number" min="1" oninput="document.getElementById('auftragsLink').href = '<?=$auftragAnzeigen?>?id=' + this.value;">
	<a href="#" id="auftragsLink">Auftrag anzeigen</a>
<?php else: ?>
	<div>
		<span>Auftragsnummer: <?=$Auftrag->getAuftragsnummer()?></span>
		<span>Beschreibung: <?=$Auftrag->getAuftragsbeschreibung()?></span>
		<span>Schritte: <?=$Auftrag->getBearbeitungsschritte()?></span>
		<span>Posten: <?=$Auftrag->getAuftragsposten()?></span>
	</div>
	<div>
		<select id="selectPosten">
			<option value="zeit">Zeit</option>
			<option value="leistung">Leistung</option>
			<option value="produkt">Produkt</option>
		</select>
		<div id="selectProdukt"></div>
		<button>Posten hinzufügen</button>
	</div>
<?php endif; ?>