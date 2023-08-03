<?php
$kdnr = -1;

if (isset($_GET['showDetails'])) {
	$kdnr = $_GET['showDetails'];
} else if (isset($_GET['kdnr'])) {
	$kdnr = $_GET['kdnr'];
}

if ($kdnr != -1) {
	$mitarbeiter = DBAccess::selectQuery("SELECT prename, lastname, id FROM user");
	$annahme = DBAccess::selectQuery("SELECT Bezeichnung, id FROM angenommen");
	$auftragstyp = DBAccess::selectQuery("SELECT * FROM auftragstyp");
	
	$kundendaten = DBAccess::selectQuery("SELECT Vorname, Nachname, Firmenname FROM kunde WHERE Kundennummer = :kdnr", [":kdnr" => $kdnr]);
	$kundendaten = $kundendaten[0];
	$ansprechpartner = DBAccess::selectQuery("SELECT Vorname, Nachname, Nummer FROM ansprechpartner WHERE Kundennummer = :kdnr", [":kdnr" => $kdnr]);
}

if ($kdnr != -1) : ?>
<div class="defCont">
	<div>
		<p>Kundennummer: <input class="block mt-1 p-1 pl-2 w-64 disabled:bg-gray-300 rounded-lg" disabled value="<?=$kdnr?>"></p>
		<?php if ($kundendaten['Vorname'] != "" && $kundendaten['Nachname'] != ""): ?>
			<p>Name: <input class="block mt-1 p-1 pl-2 w-64 disabled:bg-gray-300 rounded-lg" disabled value="<?=$kundendaten['Vorname']?> <?=$kundendaten['Nachname']?>"></p>
		<?php endif; ?> 
		<p>Firma: <input class="block mt-1 p-1 pl-2 w-64 disabled:bg-gray-300 rounded-lg" disabled value="<?=$kundendaten['Firmenname']?>"></p>
		<p>Kurzbeschreibung: <input id="bezeichnung" class="block mt-1 p-1 pl-2 w-64 disabled:bg-gray-300 rounded-lg" maxlength="255"></p>
		<p>Beschreibung: <br>
			<textarea id="beschreibung" maxlength="65535" class="block mt-1 p-1 pl-2 w-64 disabled:bg-gray-300 rounded-lg" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea>
		</p>
		<p>Auftragstyp:
			<select class="block mt-1 p-1 pl-2 w-64 disabled:bg-gray-300 rounded-lg" id="selectTyp">
				<option value="-1" selected disabled>Bitte auswählen</option>
				<?php foreach ($auftragstyp as $t): ?>
					<option value="<?=$t['id']?>"><?=$t['Auftragstyp']?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>Termin: <input id="termin" type="date" class="block mt-1 p-1 pl-2 w-64 disabled:bg-gray-300 rounded-lg"></p>
		<p>Angenommen durch: 
			<select class="block mt-1 p-1 pl-2 w-64 disabled:bg-gray-300 rounded-lg" id="selectMitarbeiter">
				<option value="-1" selected disabled>Bitte auswählen</option>
				<?php foreach ($mitarbeiter as $m): ?>
					<option value="<?=$m['id']?>"><?=$m['prename']?> <?=$m['lastname']?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>Angenommen per: 
			<select class="block mt-1 p-1 pl-2 w-64 disabled:bg-gray-300 rounded-lg" id="selectAngenommen">
				<option value="-1" selected disabled>Bitte auswählen</option>
				<?php foreach ($annahme as $m): ?>
					<option value="<?=$m['id']?>"><?=$m['Bezeichnung']?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>Ansprechpartner: 
			<select class="block mt-1 p-1 pl-2 w-64 disabled:bg-gray-300 rounded-lg" id="selectAnsprechpartner">
				<option value="-1" selected disabled>Bitte auswählen</option>
				<?php foreach ($ansprechpartner as $m): ?>
					<option value="<?=$m['Nummer']?>"><?=$m['Vorname']?> <?=$m['Nachname']?></option>
				<?php endforeach; ?>
			</select>
		</p>
	</div>
	<button class="btn-primary" id="absenden">Absenden</button>
</div>
<div id="showLinkToOrder" style="display: none;"></div>
<?php else: ?>
	<div class="defCont">
		<p>Kundennummer eingeben oder Kunde suchen:</p>
		<input id="kundensuche" class="px-4 py-2 m-1 rounded-lg focus:border-gray-700">
	</div>
	<div class="defCont hidden" id="searchResults"></div>
	<div class="defCont">
		<span>Oder <a href="<?=Link::getPageLink("angebot")?>?open" class="link-primary">Angebot übernehmen</a></span>
	</div>
<?php endif; ?>