<?php 
	$kdnr = -1;
	if (isset($_GET['kdnr'])) {
		$kdnr = $_GET['kdnr'];
	}

	if (isset($_GET['showDetails'])) {
		if (isset($_GET['id'])) {
			$kdnr = $_GET['id'];
		}
	}

	$mitarbeiter = DBAccess::selectQuery("SELECT Vorname, Nachname, id FROM mitarbeiter");
	$annahme = DBAccess::selectQuery("SELECT Bezeichnung, id FROM angenommen");
	if ($kdnr != -1) {
		$kundendaten = DBAccess::selectQuery("SELECT Vorname, Nachname, Firmenname FROM kunde WHERE Kundennummer = $kdnr")[0];
	}
?>


<?php if ($kdnr != -1) : ?>
	<span>Kundennummer: <?=$kdnr?></span><br>
	<span>Name: <?=$kundendaten['Vorname']?> <?=$kundendaten['Nachname']?></span><br>
	<span>Firma: <?=$kundendaten['Firmenname']?></span><br>
	<span>Auftragsbezeichnung: <input id="bezeichnung" maxlength="255"></span><br>
	<span>Auftragsbeschreibung: <textarea id="beschreibung" maxlength="65535"></textarea></span><br>
	<span>Auftragstyp: <input id="typ"></span><br>
	<span>Termin: <input id="termin" type="date"></span><br>
	<span>Angenommen durch: 
		<select id="selectMitarbeiter">
			<?php foreach ($mitarbeiter as $m): ?>
				<option value="<?=$m['id']?>"><?=$m['Vorname']?> <?=$m['Nachname']?></option>
			<?php endforeach; ?>
		</select>
	</span><br>
	<span>Angenommen per: 
		<select id="selectAngenommen">
			<?php foreach ($annahme as $m): ?>
				<option value="<?=$m['id']?>"><?=$m['Bezeichnung']?></option>
			<?php endforeach; ?>
		</select>
	</span><br>
	<button onclick="auftragHinzufuegen()">Absenden</button>
<?php else: ?>
	<span>Kundennummer oder Suchen: <input id="kundensuche" onkeyup="performSearchEnter(event, this.value);"><button onclick="performSearchButton(event)">&#x1F50E;</button></span>
	<input type="number" min="1" id="auftragsnummer"><button onclick="print('auftragsnummer', 'Auftrag');">Auftragsblatt generieren</button>
	<span id="searchResults"></span>
<?php endif; ?>
