<script src="<?=Link::getResourcesShortLink("tableeditor.js", "js")?>"></script>
<script src="<?=Link::getResourcesShortLink("print.js", "js")?>"></script>

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

	$mitarbeiter = DBAccess::selectQuery("SELECT prename, lastname, id FROM user");
	$annahme = DBAccess::selectQuery("SELECT Bezeichnung, id FROM angenommen");
	$auftragstyp = DBAccess::selectQuery("SELECT * FROM auftragstyp");

	if ($kdnr != -1) {
		$kundendaten = DBAccess::selectQuery("SELECT Vorname, Nachname, Firmenname FROM kunde WHERE Kundennummer = $kdnr");
		$kundendaten = $kundendaten[0];
		$ansprechpartner = DBAccess::selectQuery("SELECT Vorname, Nachname, Nummer FROM ansprechpartner WHERE Kundennummer = $kdnr");
	}

if ($kdnr != -1) : ?>
	<div class="addOrder">
		<div>
			<span>Kundennummer: <?=$kdnr?></span><br>
			<span>Name: <?=$kundendaten['Vorname']?> <?=$kundendaten['Nachname']?></span><br>
			<span>Firma: <?=$kundendaten['Firmenname']?></span><br>
			<span>Kurzbeschreibung: <input id="bezeichnung" maxlength="255"></span><br>
			<span>Beschreibung: <br><textarea id="beschreibung" maxlength="65535" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea></span><br>
			<span>Auftragstyp:
				<select id="selectTyp">
					<option value="-1" selected disabled>Bitte auswählen</option>
					<?php foreach ($auftragstyp as $t): ?>
						<option value="<?=$t['id']?>"><?=$t['Auftragstyp']?></option>
					<?php endforeach; ?>
				</select>
			</span><br>
			<span>Termin: <input id="termin" type="date"></span><br>
			<span>Angenommen durch: 
				<select id="selectMitarbeiter">
					<option value="-1" selected disabled>Bitte auswählen</option>
					<?php foreach ($mitarbeiter as $m): ?>
						<option value="<?=$m['id']?>"><?=$m['prename']?> <?=$m['lastname']?></option>
					<?php endforeach; ?>
				</select>
			</span><br>
			<span>Angenommen per: 
				<select id="selectAngenommen">
					<option value="-1" selected disabled>Bitte auswählen</option>
					<?php foreach ($annahme as $m): ?>
						<option value="<?=$m['id']?>"><?=$m['Bezeichnung']?></option>
					<?php endforeach; ?>
				</select>
			</span><br>
			<?php if (sizeOf($ansprechpartner) > 0) : ?>
			<span>Ansprechpartner: 
				<select id="selectAnsprechpartner">
					<option value="-1" selected disabled>Bitte auswählen</option>
					<?php foreach ($ansprechpartner as $m): ?>
						<option value="<?=$m['Nummer']?>"><?=$m['Vorname']?> <?=$m['Nachname']?></option>
					<?php endforeach; ?>
				</select>
			</span><br>
			<?php endif; ?>
		</div>
		<button id="absenden" onclick="auftragHinzufuegen()">Absenden</button>
	</div>
	<div id="showLinkToOrder" style="display: none;"></div>
<?php else: ?>
	<div class="defCont">
		<span>Kundennummer oder Suchen: <input id="kundensuche" onkeyup="performSearchEnter(event, this.value);"><button onclick="performSearchButton(event)">&#x1F50E;</button></span>
	</div>
	<div class="defCont">
		<span>Oder <a href="<?=Link::getPageLink("angebot")?>?open">Angebot übernehmen</a></span>
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
<script src="<?=Link::getResourcesShortLink("neuer-auftrag_f.js", "js")?>"></script>