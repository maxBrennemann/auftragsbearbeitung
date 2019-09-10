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
	<a href="<?=Link::getPageLink("kunde")?>?id=<?=$Auftrag->getKundennummer()?>">Kunde <span><?=$Auftrag->getKundennummer()?></span> zeigen</a>
	<div>
		<span>Auftragsnummer: <?=$Auftrag->getAuftragsnummer()?></span><br>
		<span>Beschreibung: <?=$Auftrag->getAuftragsbeschreibung()?></span><br>
		<span>Schritte: <?=$Auftrag->getBearbeitungsschritteAsTable()?></span><br>
		<span>Posten: <?=$Auftrag->getHTMLTable()?></span>
	</div>
	<br>
	<div id="newPosten" style="display: none;">
		<select id="selectPosten">
			<option value="zeit">Zeit</option>
			<option value="leistung">Leistung</option>
			<option value="produkt">Produkt</option>
		</select>
		<button onclick="getSelections()">Posten hinzufügen</button>
		<div id="addPosten">
			<div id="addPostenZeit" style="display: none">
				<span><input id="time" type="number" min="0">Zeit in Minuten</span><br>
				<span><input id="wage" type="number" value="44">Stundenlohn in €</span>
				<span><input id="descr" type="text">Beschreibung</span>
				<button onclick="addTime()">Hinzufügen</button>
			</div>
			<div id="addPostenLeistung" style="display: none">
				<select id="selectLeistung">
					<option value="aufkleber">Aufkleber</option>
					<option value="plane">Plane</option>
					<option value="digitaldruck">Digitaldruck</option>
				</select>
				<br>
				<span><input id="bez">Bezeichnung</span><br>
				<span><input id="bes">Beschreibung</span><br>
				<span><input id="pre" value="0">Preis</span><br>
				<span><input id="ekp" value="0">Einkaufspreis</span><br>
				<button onclick="addLeistung()">Hinzufügen</button>
			</div>
		</div>
		<div id="selectProdukt">
			<hr>
			<span>Produkt suchen: <input type="text"><button onclick="performSearch()">&#x1F50E;</button></span>
			<div id="searchResults"></div>
		</div>
		<div id="generalPosten"></div>
	</div>
	<button onclick="showSelection(event.target)">Neuen Posten hinzufügen</button>
	<button onclick="addBearbeitungsschritte()">Neuen Bearbeitungsschritt hinzufügen</button>
	<div id="bearbeitungsschritte"></div>
<?php endif; ?>