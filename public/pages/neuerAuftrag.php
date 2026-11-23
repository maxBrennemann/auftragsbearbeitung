<?php

use Src\Classes\Link;
use Src\Classes\Project\Icon;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

$idCustomer = Tools::get("id");
$fromOffer = (int) Tools::get("fromOffer");

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

if (Tools::get("id")) :
	$personName = trim($kundendaten["Vorname"] . " " . $kundendaten["Nachname"]);
?>
	<input type="hidden" id="customerId" value="<?= $idCustomer ?>" data-variable="true">
	<?php if ($fromOffer): ?>
		<input type="hidden" id="fromOffer" value="<?= $fromOffer ?>" data-variable="true">
	<?php endif; ?>

	<div class="mx-2.5 mt-2 mb-4">
		<span class="group-label text-text-muted">Neuer Auftrag<?= $fromOffer ? " aus Angebot " . $fromOffer : "" ?></span>
		<h2 class="page-title"><?= $kundendaten["Firmenname"] ?: $personName ?></h2>
		<p class="text-xs text-text-muted">Kunde <?= $idCustomer ?><?= $kundendaten["Firmenname"] && $personName ? " · " . $personName : "" ?></p>
	</div>

	<div class="grid grid-cols-1 lg:grid-cols-3">
		<section class="defCont lg:col-span-2">
			<div class="section-head">
				<h2 class="section-title">Auftrag</h2>
			</div>
			<div class="field-grid">
				<label class="field col-span-full">
					<span class="field-label">Auftragsbezeichnung</span>
					<input class="input-primary" id="bezeichnung" maxlength="255" placeholder="z. B. Fahrzeugbeschriftung Caddy">
				</label>
				<label class="field col-span-full">
					<span class="field-label">Beschreibung</span>
					<textarea class="input-primary" id="beschreibung" maxlength="65535"></textarea>
				</label>
				<label class="field">
					<span class="field-label">Ansprechpartner</span>
					<select class="input-primary" id="selectAnsprechpartner">
						<option value="-1" selected disabled><?= empty($ansprechpartner) ? "Keine hinterlegt" : "Bitte auswählen" ?></option>
						<?php foreach ($ansprechpartner as $m): ?>
							<option value="<?= $m["Nummer"] ?>"><?= $m["Vorname"] ?> <?= $m["Nachname"] ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="field">
					<span class="field-label">Termin</span>
					<input class="input-primary" id="termin" type="date">
				</label>
			</div>
		</section>

		<section class="defCont">
			<div class="section-head">
				<h2 class="section-title">Für die Auswertung</h2>
			</div>
			<div class="field-grid">
				<label class="field col-span-full">
					<span class="field-label">Auftragstyp</span>
					<select class="input-primary" id="selectTyp">
						<option value="-1" selected disabled>Bitte auswählen</option>
						<?php foreach ($auftragstyp as $t): ?>
							<option value="<?= $t["id"] ?>"><?= $t["Auftragstyp"] ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="field col-span-full">
					<span class="field-label">Angenommen von</span>
					<select class="input-primary" id="selectMitarbeiter">
						<?php foreach ($mitarbeiter as $m): ?>
							<option value="<?= $m["id"] ?>"><?= $m["prename"] ?> <?= $m["lastname"] ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="field col-span-full">
					<span class="field-label">Angenommen per</span>
					<select class="input-primary" id="selectAngenommen">
						<option value="-1" selected disabled>Bitte auswählen</option>
						<?php foreach ($annahme as $m): ?>
							<option value="<?= $m["id"] ?>"><?= $m["Bezeichnung"] ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
		</section>
	</div>

	<div class="mx-2.5 mb-2">
		<button class="btn-primary" data-binding="true" data-fun="sendData"><?= Icon::get("clipboard-plus") ?>Auftrag anlegen</button>
	</div>
<?php else: ?>
	<section class="defCont">
		<div class="section-head">
			<h2 class="section-title">Neuer Auftrag: Kunde wählen</h2>
			<div class="flex items-center gap-3 text-sm">
				<a href="<?= Link::getPageLink("neuer-kunde") ?>" class="link-primary">Neuen Kunden anlegen</a>
				<a href="<?= Link::getPageLink("angebot") ?>?open" class="link-primary">Aus Angebot übernehmen</a>
			</div>
		</div>
		<label class="field max-w-md">
			<span class="field-label">Kundennummer oder Name</span>
			<input class="input-primary" data-input="true" data-fun="searchCustomer" id="customerSearch" autocomplete="off" autofocus placeholder="Suche startet beim Tippen">
			<span class="field-hint">Eine Kundennummer mit Enter öffnet den Kunden direkt.</span>
		</label>
		<div class="hidden w-full max-h-96 overflow-y-auto mt-4" id="searchResults"></div>
	</section>
<?php endif; ?>
