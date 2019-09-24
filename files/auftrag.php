<?php 
	require_once('classes/project/Auftrag.php');
	require_once('classes/DBAccess.php');

	$auftragsId = -1;
	if (isset($_GET['id'])) {
		$auftragsId = $_GET['id'];
		try {
			$Auftrag = new Auftrag($auftragsId);
		} catch (Exception $e){
			echo $e->getMessage();
			$auftragsId = -1;
		}
	}

	$leistungen = DBAccess::selectQuery("SELECT Bezeichnung, Nummer FROM leistung");
?>

<?php if ($auftragsId == -1) : ?>
	<input type="number" min="1" oninput="document.getElementById('auftragsLink').href = '<?=$auftragAnzeigen?>?id=' + this.value;">
	<a href="#" id="auftragsLink">Auftrag anzeigen</a>
<?php else: ?>
	<a href="<?=Link::getPageLink("kunde")?>?id=<?=$Auftrag->getKundennummer()?>">Kunde <span><?=$Auftrag->getKundennummer()?></span> zeigen</a>
	<div>
		<span><u>Auftragsnummer:</u> <?=$Auftrag->getAuftragsnummer()?></span><br>
		<span><u>Beschreibung:</u><br><?=$Auftrag->getAuftragsbeschreibung()?></span><br>
		<span><u>Schritte:</u><br>
			<form name="showSteps">
				<input onchange="radio('hide')" type="radio" name="showDone" value="hide" checked> Zu erledigende Schritte anzeigen<br>
				<input onchange="radio('show')" type="radio" name="showDone" value="show"> Alle Schritte anzeigen<br>
			</form>
			<span id="stepTable"><?=$Auftrag->getOpenBearbeitungsschritteAsTable()?></span>
		</span><br>
		<span><u>Posten:</u> <?=$Auftrag->getAuftragspostenAsTable()?></span>
		<span><u>Gesamtpreis:</u> <?=$Auftrag->preisBerechnen()?>€</span>
	</div>
	<br>
	<hr>
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
					<?php foreach ($leistungen as $leistung): ?>
						<option value="<?=$leistung['Nummer']?>"><?=$leistung['Bezeichnung']?></option>
					<?php endforeach; ?>
				</select>
				<br>
				<span><input id="bes"> Beschreibung</span><br>
				<span><input id="ekp" value="0"> Einkaufspreis</span><br>
				<span><input id="pre" value="0"> Speziefischer Preis</span><br>
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