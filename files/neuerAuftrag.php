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
		$ansprechpartner = DBAccess::selectQuery("SELECT Vorname, Nachname, Nummer FROM ansprechpartner WHERE Kundennummer = $kdnr");
	}
?>


<?php if ($kdnr != -1) : ?>
	<div class="addOrder">
		<div>
			<span>Kundennummer: <?=$kdnr?></span><br>
			<span>Name: <?=$kundendaten['Vorname']?> <?=$kundendaten['Nachname']?></span><br>
			<span>Firma: <?=$kundendaten['Firmenname']?></span><br>
			<span>Kurzbeschreibung: <input id="bezeichnung" maxlength="255"></span><br>
			<span>Auftragsbeschreibung: <br><textarea id="beschreibung" maxlength="65535"></textarea></span><br>
			<span>Auftragstyp: <input id="typ" placeholder="Textil | Fahrzeugbeschriftung | ..."></span><br>
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
			<?php if (sizeOf($ansprechpartner) > 0) : ?>
			<span>Ansprechpartner: 
				<select id="selectAnsprechpartner">
					<?php foreach ($ansprechpartner as $m): ?>
						<option value="<?=$m['Nummer']?>"><?=$m['Vorname']?> <?=$m['Nachname']?></option>
					<?php endforeach; ?>
				</select>
			</span><br>
			<?php endif; ?>
		</div>
		<button id="absenden" onclick="auftragHinzufuegen()">Absenden</button>
	</div>
<?php else: ?>
	<div class="defCont">
		<span>Kundennummer oder Suchen: <input id="kundensuche" onkeyup="performSearchEnter(event, this.value);"><button onclick="performSearchButton(event)">&#x1F50E;</button></span>
	</div>
	<span id="searchResults"></span>
<?php endif; ?>
<style>
	.addOrder {
		border-radius: 6px;
		margin: 10px;
		background-color: #eff0f1;
	}

	.addOrder > div {
		padding: 15px;
	}

	input {
		border: none;
		border-radius: 6px;
		padding: 6px;
		margin: 4px;
	}

	textarea {
		border: none;
		border-radius: 6px;
		padding: 6px;
	}

	::placeholder {
		font-size: 0.8em;
		font-style: italic;
	}

	#absenden {
		width: 100%;
		margin: 0;
		padding: 12px;
		border-radius: 0 0 6px 6px;
		border: none;
		background: #B2B2BE;
	}

	#selectMitarbeiter, #selectAngenommen, #selectAnsprechpartner {
		float: right;
	}
</style>