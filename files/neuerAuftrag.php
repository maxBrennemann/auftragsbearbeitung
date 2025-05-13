<?php

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

use Classes\Link;

$idCustomer = Tools::get("id");

if ($idCustomer != null) {
	$mitarbeiter = DBAccess::selectQuery("SELECT prename, lastname, id FROM user");
	$annahme = DBAccess::selectQuery("SELECT Bezeichnung, id FROM angenommen");
	$auftragstyp = DBAccess::selectQuery("SELECT * FROM auftragstyp");

	$kundendaten = DBAccess::selectQuery("SELECT Vorname, Nachname, Firmenname FROM kunde WHERE Kundennummer = :kdnr", [
		"kdnr" => $idCustomer
	]);
	$kundendaten = $kundendaten[0];

	$ansprechpartner = DBAccess::selectQuery("SELECT Vorname, Nachname, Nummer FROM ansprechpartner WHERE Kundennummer = :kdnr", [
		"kdnr" => $idCustomer
	]);
}

if (Tools::get("id")) : ?>
	<div class="defCont">
		<div class="grid grid-cols-2">
			<div>
				<p class="font-semibold">Kunden- und Auftragsdaten</p>
				<div class="mt-2">
					<div class="w-full flex flex-col">
						<label for="idCustomer">Kundennummer:</label>
						<input disabled class="input-primary" id="customerId" value="<?= $idCustomer ?>" data-variable="true">
					</div>
				</div>

				<?php if ($kundendaten["Vorname"] != "" && $kundendaten["Nachname"] != ""): ?>
					<div class="mt-2">
						<div class="w-full flex flex-col">
							<label for="idCustomer">Name:</label>
							<input disabled class="input-primary" value="<?= $kundendaten["Vorname"] ?> <?= $kundendaten["Nachname"] ?>">
						</div>
					</div>
				<?php endif; ?>

				<div class="mt-2">
					<div class="w-full flex flex-col">
						<label for="idCustomer">Firma:</label>
						<input disabled class="input-primary" value="<?= $kundendaten["Firmenname"] ?>">
					</div>
				</div>

				<div class="mt-2">
					<div class="w-full flex flex-col">
						<label for="idCustomer">Ansprechpartner:</label>
						<select class="input-primary" id="selectAnsprechpartner">
							<option value="-1" selected disabled>Bitte auswählen</option>
							<?php foreach ($ansprechpartner as $m): ?>
								<option value="<?= $m["Nummer"] ?>"><?= $m["Vorname"] ?> <?= $m["Nachname"] ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="mt-2">
					<div class="w-full flex flex-col">
						<label for="idCustomer">Kurzbeschreibung:</label>
						<input class="input-primary" id="bezeichnung" maxlength="255">
					</div>
				</div>

				<div class="mt-2">
					<div class="w-full flex flex-col">
						<label for="idCustomer">Beschreibung:</label>
						<textarea class="input-primary" id="beschreibung" maxlength="65535" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea>
					</div>
				</div>

				<div class="mt-2">
					<div class="w-full flex flex-col">
						<label for="idCustomer">Termin:</label>
						<input class="input-primary" id="termin" type="date">
					</div>
				</div>
			</div>

			<div>
				<p class="font-semibold">Statistik</p>
				<div class="mt-2">
					<div class="w-full flex flex-col">
						<label for="idCustomer">Auftragstyp:</label>
						<select class="input-primary" id="selectTyp">
							<option value="-1" selected disabled>Bitte auswählen</option>
							<?php foreach ($auftragstyp as $t): ?>
								<option value="<?= $t["id"] ?>"><?= $t["Auftragstyp"] ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="mt-2">
					<div class="w-full flex flex-col">
						<label for="idCustomer">Angenommen durch:</label>
						<select class="input-primary" id="selectMitarbeiter">
							<?php foreach ($mitarbeiter as $m): ?>
								<option value="<?= $m["id"] ?>"><?= $m["prename"] ?> <?= $m["lastname"] ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="mt-2">
					<div class="w-full flex flex-col">
						<label for="idCustomer">Angenommen per:</label>
						<select class="input-primary" id="selectAngenommen">
							<option value="-1" selected disabled>Bitte auswählen</option>
							<?php foreach ($annahme as $m): ?>
								<option value="<?= $m["id"] ?>"><?= $m["Bezeichnung"] ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
		</div>
		<button class="btn-primary mt-3" data-binding="true" data-fun="sendData">Absenden</button>
	</div>
<?php else: ?>
	<div class="defCont">
		<p>Kundennummer eingeben oder Kunde suchen:</p>
		<input class="input-primary" data-input="true" data-fun="searchCustomer">
	</div>
	<div class="defCont hidden" id="searchResults"></div>
	<div class="defCont">
		<span>Oder <a href="<?= Link::getPageLink("angebot") ?>?open" class="link-primary">Angebot übernehmen</a></span>
	</div>
<?php endif; ?>