<?php

use Src\Classes\Controller\TemplateController;
use Src\Classes\Link;
use Src\Classes\Project\Auftrag;
use Src\Classes\Project\ClientSettings;
use Src\Classes\Project\Fahrzeug;
use Src\Classes\Project\Icon;
use Src\Classes\Project\Kunde;
use Src\Classes\Project\OrderHistory;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

$orderId = (int) Tools::get("id");

try {
    $auftrag = new Auftrag($orderId);
} catch (Exception $e) {
    $orderId = 0;
}

?>

<div class="grid grid-cols-6">
	<?php if ($orderId <= 0): ?>
		<div class="mt-4 bg-gray-50 p-3 rounded-lg col-span-6">
			<p class="font-semibold">Kein (gültiger) Auftrag ausgewählt.</p>
			<p>Liste aller offenen Aufträge</p>
			<?= Auftrag::getOverview(); ?>
		</div>
	<?php else: ?>
		<input type="hidden" data-variable="true" value="<?= $auftrag->getKundennummer() ?>" id="customerId">
		<input type="hidden" data-variable="true" value="<?= $orderId ?>" id="orderId">
		<input type="hidden" data-variable="true" value="<?= $auftrag->getInvoiceId() ?>" id="invoiceId">

		<?php

        $kunde = new Kunde($auftrag->getKundennummer());

	    $farbTable = $auftrag->getColors();
	    $fahrzeuge = Fahrzeug::getSelection($auftrag->getKundennummer());
	    $services = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");

	    $showFiles = Auftrag::getFiles($orderId);
	    $auftragsverlauf = OrderHistory::representHistoryAsHTML($orderId);
	    $contactPersons = $auftrag->getContactPersons();

	    $auftragsTyp = $auftrag->getAuftragstyp();
	    $auftragsTypen = Auftrag::getAllOrderTypes();

	    $mitarbeiter = DBAccess::selectQuery("SELECT prename, lastname, id FROM user");

	    ?>

		<?php if ($auftrag->istRechnungGestellt() && Tools::get("show") == null): ?>
			<?= TemplateController::getTemplate("orderFinished", [
	            "auftrag" => $auftrag,
	        ]); ?>
		<?php else: ?>
			<?php $isInvoiceView = Tools::get("show") == "true" && $auftrag->getInvoiceId() != 0; ?>

			<section class="defCont col-span-6 md:col-span-4 2xl:col-span-3">
				<div class="section-head relative">
					<div class="flex items-center gap-2">
						<h2 class="section-title">Auftrag <span id="auftragsnummer"><?= $auftrag->getAuftragsnummer() ?></span></h2>
						<?php if ($auftrag->getIsArchiviert()) : ?>
							<span class="stamp stamp-muted">Archiviert</span>
						<?php endif; ?>
					</div>
					<?= TemplateController::getTemplate("toggleOptions", [
						"toggleClass" => "orderDetailsOpen",
						"toggleBtnId" => "extraOptions",
						"toggleId" => "showExtraOptions",
						"options" => [
							[
								"text" => "Auftrag archivieren",
								"icon" => "archive",
								"function" => "archivieren",
								"show" => !$auftrag->getIsArchiviert() && !$isInvoiceView,
							],
							[
								"text" => "Aus dem Archiv holen",
								"icon" => "archive-restore",
								"function" => "rearchiveOrder",
								"show" => $auftrag->getIsArchiviert(),
							],
							[
								"text" => "Anderem Kunden zuweisen",
								"icon" => "user-round-pen",
								"function" => "changeCustomer",
							],
							[
								"text" => "Auftrag löschen",
								"icon" => "trash-2",
								"function" => "deleteOrder",
								"danger" => true,
								"dividerBefore" => true,
							],
						],
					]); ?>
				</div>

				<div class="field-grid">
					<label class="field col-span-full">
						<span class="field-label">Auftragsbezeichnung</span>
						<input class="input-primary" id="orderTitle" value="<?= $auftrag->getAuftragsbezeichnung() ?>" autocomplete="none" data-write="true" data-fun="editTitle">
					</label>
					<div class="field col-span-full">
						<span class="field-label">
							Auftragsbeschreibung
							<button type="button" class="btn-ghost btn-icon btn-sm" data-fun="toggleOrderDescription" data-binding="true" title="Beschreibung ein- oder ausklappen" aria-label="Beschreibung ein- oder ausklappen">
								<span class="toggle-up"><?= Icon::get("chevron-up") ?></span>
								<span class="toggle-down hidden"><?= Icon::get("chevron-down") ?></span>
							</button>
						</span>
						<textarea class="orderDescription input-primary" autocomplete="none" data-write="true" data-fun="editDescription"><?= $auftrag->getAuftragsbeschreibung() ?></textarea>
						<input class="orderDescription input-primary hidden" autocomplete="none" data-write="true" data-fun="editDescription" value="<?= $auftrag->getAuftragsbeschreibung() ?>">
					</div>
					<label class="field col-span-full">
						<span class="field-label">Auftragstyp</span>
						<select class="input-primary" id="orderType" data-write="true" data-fun="editOrderType">
							<?php foreach ($auftragsTypen as $type): ?>
								<option value="<?= $type["id"] ?>" <?= $auftragsTyp == $type["id"] ? "selected" : "" ?>><?= $type["Auftragstyp"] ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="field">
						<span class="field-label">Auftragseingang</span>
						<input class="input-primary" type="date" value="<?= $auftrag->getDate() ?>" data-write="true" data-fun="updateDate">
					</label>
					<div class="field">
						<label class="field-label" for="inputDeadline">Termin</label>
						<input class="input-primary" type="date" value="<?= $auftrag->getDeadline() ?>" id="inputDeadline" data-write="true" data-fun="updateDeadline">
						<label class="field-hint inline-flex items-center gap-1.5">
							<input type="checkbox" data-binding="true" data-fun="setDeadlineState" <?= $auftrag->getDeadline() == "" ? "checked" : "" ?>> Kein Termin
						</label>
					</div>
				</div>

				<div class="flex flex-wrap items-center gap-2 mt-4">
					<?php if ($isInvoiceView) : ?>
						<button class="btn-primary" data-link="<?= Link::getPageLink('rechnung') ?>?target=view&id=<?= $auftrag->getInvoiceNumber() ?>" data-binding="true" data-fun="showInvoice" id="showInvoice" title="Rechnung <?= $auftrag->getInvoiceNumber() ?> anzeigen"><?= Icon::get("file-text") ?>Rechnung anzeigen</button>
					<?php else: ?>
						<button class="btn-primary" data-link="<?= Link::getPageLink('rechnung') ?>?target=create&id=<?= $orderId ?>" data-binding="true" data-fun="showInvoicePreview" id="showInvoicePreview"><?= Icon::get("file-text") ?>Rechnungsvorschau</button>
						<button class="btn-secondary" data-binding="true" data-fun="setOrderFinished"><?= Icon::get("check") ?>Als fertig markieren</button>
					<?php endif; ?>
					<a class="btn-secondary" href="/api/v1/order/<?= $orderId ?>/delivery-note/pdf" target="_blank" title="Lieferschein als PDF öffnen"><?= Icon::get("truck") ?>Lieferschein</a>
				</div>
			</section>

			<section class="defCont col-span-6 md:col-span-2 2xl:col-span-3">
				<div class="section-head">
					<h2 class="section-title">Kunde</h2>
					<a class="link-primary text-sm" href="<?= Link::getPageLink("kunde") ?>?id=<?= $auftrag->getKundennummer() ?>">Kunde <span id="kundennummer"><?= $auftrag->getKundennummer() ?></span> anzeigen</a>
				</div>
				<div class="sheet">
					<p class="font-semibold"><?= $kunde->getFirmenname() ?></p>
					<p><?= $kunde->getVorname() ?> <?= $kunde->getNachname() ?></p>
					<p class="mt-2"><?= $kunde->getStrasse() ?> <?= $kunde->getHausnummer() ?></p>
					<p><?= $kunde->getPostleitzahl() ?> <?= $kunde->getOrt() ?></p>
					<?php if ($kunde->getTelefonFestnetz() || $kunde->getTelefonMobil() || $kunde->getEmail()) : ?>
						<div class="mt-2 space-y-0.5 text-sm">
							<?php if ($kunde->getTelefonFestnetz()) : ?><p class="flex items-center gap-1.5 text-text-muted"><?= Icon::get("phone", 14) ?><span class="text-text"><?= $kunde->getTelefonFestnetz() ?></span></p><?php endif; ?>
							<?php if ($kunde->getTelefonMobil()) : ?><p class="flex items-center gap-1.5 text-text-muted"><?= Icon::get("smartphone", 14) ?><span class="text-text"><?= $kunde->getTelefonMobil() ?></span></p><?php endif; ?>
							<?php if ($kunde->getEmail()) : ?><p class="flex items-center gap-1.5 text-text-muted"><?= Icon::get("mail", 14) ?><a class="link-primary font-normal" href="mailto:<?= $kunde->getEmail() ?>"><?= $kunde->getEmail() ?></a></p><?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
				<label class="field mt-3">
					<span class="field-label">Ansprechpartner</span>
					<select class="input-primary" data-write="true" data-fun="changeContact" id="showAnspr">
						<?php
						$optionsDefault = true;
						foreach ($contactPersons as $contact):
						    $selected = $contact["isSelected"] ? "selected" : "";
						    $optionsDefault = $contact["isSelected"] ? false : true;
						    ?>
							<option value="<?= $contact["id"] ?>" <?= $selected ?>><?= $contact["firstName"] ?> <?= $contact["lastName"] ?></option>
						<?php endforeach; ?>
						<?php if ($optionsDefault): ?>
							<option disabled selected>Kein Ansprechpartner ausgewählt</option>
						<?php endif; ?>
					</select>
				</label>
			</section>

			<section class="defCont col-span-6 md:col-span-4 2xl:col-span-3" id="stepCont">
				<div class="section-head">
					<h2 class="section-title">Bearbeitungsschritte</h2>
					<div class="flex flex-wrap items-center gap-3">
						<?= TemplateController::getTemplate("inputSwitch", [
						    "id" => "toggleSteps",
						    "name" => "Alle anzeigen",
						    "write" => "toggleSteps",
						]); ?>
						<button class="btn-secondary btn-sm" data-binding="true" data-fun="toggleAddStep"><?= Icon::get("plus", 14) ?>Neuer Schritt</button>
					</div>
				</div>
				<div class="sheet mb-3 hidden" id="bearbeitungsschritte">
					<div class="field-grid">
						<label class="field col-span-full">
							<span class="field-label">Bezeichnung</span>
							<input class="bearbeitungsschrittInput input-primary" type="text" maxlength="128" id="processingStepName">
						</label>
						<label class="field">
							<span class="field-label">Datum</span>
							<input class="bearbeitungsschrittInput input-primary" type="date" id="processingStepDate">
						</label>
						<label class="field">
							<span class="field-label">Priorität</span>
							<input class="bearbeitungsschrittInput accent-ink h-8" type="range" list="priority" id="processingStepPriority">
							<datalist id="priority">
								<option value="0"></option>
								<option value="25"></option>
								<option value="50"></option>
								<option value="75"></option>
								<option value="100"></option>
							</datalist>
						</label>
						<div class="field">
							<?= TemplateController::getTemplate("inputSwitch", [
							    "id" => "processingStepStatus",
							    "name" => "Noch zu erledigen",
							    "value" => "checked",
							]); ?>
						</div>
						<div class="field">
							<?= TemplateController::getTemplate("inputSwitch", [
							    "id" => "processingStepBy",
							    "name" => "Zuweisen an",
							    "binding" => "updateSelectBy",
							]); ?>
							<select id="processingStepSelectBy" class="input-primary" disabled aria-label="Mitarbeiter">
								<?php foreach ($mitarbeiter as $m): ?>
									<option value="<?= $m["id"] ?>"><?= $m["prename"] ?> <?= $m["lastname"] ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="flex gap-2 mt-4">
						<button class="btn-primary" data-binding="true" data-fun="addBearbeitungsschritt">Schritt hinzufügen</button>
						<button class="btn-secondary" data-binding="true" data-fun="toggleAddStep">Abbrechen</button>
					</div>
				</div>
				<div id="stepTable"></div>
			</section>

			<section class="defCont col-span-6 md:col-span-2 2xl:col-span-3">
				<div class="section-head">
					<h2 class="section-title">Notizen</h2>
					<button class="btn-secondary btn-sm" data-binding="true" data-fun="addNewNote" id="addNewNote"><?= Icon::get("plus", 14) ?>Neue Notiz</button>
				</div>
				<div class="hidden" id="addNotes">
					<div class="sheet flex flex-col gap-2">
						<input type="text" class="noteTitle font-semibold text-ink outline-0" maxlength="128" placeholder="Titel" aria-label="Titel der Notiz">
						<textarea class="noteText resize-none w-full outline-0 text-text" placeholder="Notiz" aria-label="Text der Notiz"></textarea>
						<input type="date" value="<?= date("Y-m-d") ?>" class="noteDate self-start text-sm text-text-muted" aria-label="Datum der Notiz">
					</div>
					<div class="flex gap-2 mt-3">
						<button class="btn-primary" data-binding="true" data-fun="sendNote">Notiz speichern</button>
						<button class="btn-secondary" data-binding="true" data-fun="cancelNote">Abbrechen</button>
					</div>
				</div>
			</section>

			<section class="defCont col-span-6 hidden" id="notesContainer">
				<div class="section-head">
					<h2 class="section-title">Alle Notizen</h2>
				</div>
				<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3" id="noteContainer"></div>
			</section>

			<section class="defCont col-span-6">
				<div class="section-head">
					<h2 class="section-title">Zeiten, Produkte und Kosten</h2>
					<div class="flex flex-wrap items-center gap-3">
						<label class="inline-flex items-center gap-1.5 text-sm">
							<input data-binding="true" data-fun="toggleInvoiceItems" type="checkbox" <?= ClientSettings::getFilterOrderPosten() == true ? "checked" : "" ?>>
							Rechnungsposten ausblenden
						</label>
						<span class="section-meta">netto</span>
					</div>
				</div>
				<div class="overflow-x-auto md:w-full">
					<?= TemplateController::getTemplate("invoiceItems", [
					    "services" => $services
					]); ?>
				</div>
			</section>

			<section class="defCont col-span-6">
				<div class="section-head">
					<h2 class="section-title">Rechnungsposten</h2>
					<span class="section-meta">netto, so wie sie auf der Rechnung erscheinen</span>
				</div>
				<div id="invoicePostenTable" class="overflow-x-auto md:w-full"><?= $auftrag->getInvoicePostenTable() ?></div>
			</section>

			<section class="defCont col-span-6 md:col-span-3">
				<div class="section-head">
					<h2 class="section-title inline-flex items-center">Fahrzeuge
						<?= TemplateController::getTemplate("infoButton", [
						    "infoKey" => "",
						]) ?>
					</h2>
				</div>
				<div class="flex flex-wrap items-center gap-2">
					<select id="selectVehicle" class="input-primary w-auto min-w-48" data-fun="selectVehicle" data-write="true" aria-label="Fahrzeug auswählen">
						<option value="0" selected disabled>Fahrzeug auswählen</option>
						<?php foreach ($fahrzeuge as $f): ?>
							<option value="<?= $f['Nummer'] ?>"><?= $f['Kennzeichen'] ?> <?= $f['Fahrzeug'] ?></option>
						<?php endforeach; ?>
						<option value="addNew">Neues Fahrzeug anlegen …</option>
					</select>
					<button class="btn-secondary" data-binding="true" data-fun="addExistingVehicle">Übernehmen</button>
				</div>
				<div class="sheet mt-3 hidden" id="addVehicle">
					<div class="field-grid">
						<label class="field">
							<span class="field-label">Kfz-Kennzeichen</span>
							<input id="kfz" class="input-primary">
						</label>
						<label class="field">
							<span class="field-label">Fahrzeug</span>
							<input id="fahrzeug" class="input-primary">
						</label>
					</div>
					<button class="btn-primary mt-3" data-binding="true" data-fun="addNewVehicle">Fahrzeug anlegen</button>
				</div>
				<div id="fahrzeugTable" class="mt-3 overflow-x-auto md:w-full"></div>
			</section>

			<section class="defCont col-span-3 md:col-span-1">
				<div class="section-head">
					<h2 class="section-title">Farben</h2>
				</div>
				<span id="showColors"><?= $farbTable ?></span>
				<div class="flex flex-col items-start gap-1 mt-2">
					<button class="btn-secondary btn-sm" data-binding="true" data-fun="addColor"><?= Icon::get("plus", 14) ?>Neue Farbe</button>
					<button class="btn-secondary btn-sm" data-fun="toggleCS" data-binding="true"><?= Icon::get("palette", 14) ?>Aus Bestand</button>
				</div>
			</section>

			<section class="defCont col-span-3 md:col-span-2">
				<div class="section-head">
					<h2 class="section-title">Kalkulation</h2>
					<span class="section-meta">netto</span>
				</div>
				<p class="kpi-label">Rechnungsbetrag</p>
				<p id="totalPrice" class="kpi-value text-ink"><?= number_format($auftrag->preisBerechnen(), 2, ',', '.') . " €" ?></p>
				<p class="mt-3 kpi-label">Gewinn</p>
				<p class="text-lg font-semibold tabular-nums"><?= number_format($auftrag->gewinnBerechnen(), 2, ',', '.') . " €" ?></p>
			</section>

			<section class="defCont col-span-6 md:col-span-4">
				<div class="section-head">
					<h2 class="section-title">Dateien</h2>
				</div>
				<?= TemplateController::getTemplate("uploadFile", [
				    "target" => "order",
				]); ?>
				<div id="showFilePrev" class="mt-3">
					<?= $showFiles ?>
				</div>
			</section>

			<section class="defCont col-span-6 md:col-span-2">
				<div class="section-head">
					<h2 class="section-title">Auftragsverlauf</h2>
				</div>
				<div class="orderHistory">
					<?= $auftragsverlauf ?>
				</div>
				<div class="flex items-center mt-3">
					<button class="btn-secondary btn-sm" data-binding="true" data-fun="addList"><?= Icon::get("list-plus", 14) ?>Liste hinzufügen</button>
					<?= TemplateController::getTemplate("infoButton", [
					    "infoKey" => "lists",
					]) ?>
				</div>
			</section>

			<template id="templateFarbe">
				<div class="defCont">
					<div class="field-grid">
						<label class="field">
							<span class="field-label">Farbbezeichnung</span>
							<input class="colorInput input-primary" type="text" maxlength="32" placeholder="619 verkehrsgrün">
						</label>
						<label class="field">
							<span class="field-label">Farbtyp</span>
							<input class="colorInput input-primary" type="text" maxlength="32" placeholder="751C">
						</label>
						<label class="field">
							<span class="field-label">Hersteller</span>
							<input class="colorInput input-primary" type="text" maxlength="32" placeholder="Oracal">
						</label>
						<label class="field">
							<span class="field-label">Farbe (Hex)</span>
							<input class="colorInput jscolor input-primary outline-solid outline-offset-1" type="text" maxlength="32" data-write="true" data-fun="checkHexCode">
						</label>
					</div>
					<button class="btn-primary mt-4" data-fun="sendColor">Farbe hinzufügen</button>
				</div>
				<div class="defCont font-mono" id="cpContainer"></div>
			</template>

			<template id="templateExistingColor">
				<p class="font-semibold">Vorhandene Farben:</p>
				<div class="w-full h-60 overflow-y-scroll mt-1 bg-white rounded-md"></div>
			</template>

			<template id="templateCalculateGas">
				<p>Spritpreisrechner</p>
				<label>
					Fahrzeug auswählen
					<select>
						<option>Dacia Duster</option>
						<option>Fiat</option>
						<option>Verbrauch selbst eingeben</option>
					</select>
				</label>
				<label>
					Strecke in km
					<input type="number">
				</label>
				<label>
					Preis pro Liter
					<input>
				</label>
			</template>

			<template id="templateNote">
				<div class="noteCard sheet relative flex flex-col">
					<input type="text" class="noteTitle font-semibold text-ink outline-0" maxlength="128" aria-label="Titel der Notiz">
					<textarea class="noteText mt-2 text-text resize-none w-full outline-0 h-32" aria-label="Text der Notiz"></textarea>
					<div class="relative flex items-center mt-2 pt-2 border-t border-line-soft text-xs text-text-muted">
						<span class="noteDate"></span>
						<button type="button" class="showDelete btn-ghost btn-icon btn-sm ml-auto" title="Weitere Aktionen" aria-label="Weitere Aktionen"><?= Icon::get("ellipsis", 16) ?></button>
					</div>
					<button type="button" class="noteDelete btn-danger btn-sm hidden absolute right-2 bottom-10">Notiz löschen</button>
				</div>
			</template>
		<?php endif; ?>
	<?php endif; ?>
</div>