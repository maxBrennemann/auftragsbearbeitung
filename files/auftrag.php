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
		<span>Auftragsnummer: <?=$Auftrag->getAuftragsnummer()?></span><br>
		<span>Beschreibung: <?=$Auftrag->getAuftragsbeschreibung()?></span><br>
		<span>Schritte: <?=$Auftrag->getBearbeitungsschritte()?></span><br>
		<span>Posten: <?=$Auftrag->getHTMLTable()?></span>
	</div>
	<br>
	<div id="newPosten" style="display: none;">
		<select id="selectPosten">
			<option value="zeit">Zeit</option>
			<option value="leistung">Leistung</option>
			<option value="produkt">Produkt</option>
		</select>
		<button onclick="getSelection()">Posten hinzufügen</button>
		<div id="addPosten"></div>
		<div id="selectProdukt"></div>
		<div id="generalPosten"></div>
	</div>
	<button>Neuen Posten hinzufügen</button>
<?php endif; ?>