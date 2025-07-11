<?php

use Classes\Controller\TemplateController;
use Classes\Link;
use Classes\Project\Auftrag;
use Classes\Project\ClientSettings;
use Classes\Project\Fahrzeug;
use Classes\Project\Icon;
use Classes\Project\Kunde;
use Classes\Project\OrderHistory;
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
			<div class="defCont col-span-6 md:col-span-4 2xl:col-span-3">
				<div class="relative">
					<span class="font-bold">Auftrag <span id="auftragsnummer"><?= $auftrag->getAuftragsnummer() ?></span><?php if ($auftrag->getIsArchiviert()) : ?> (archiviert)<?php endif; ?><button class="float-right border-none w-4" id="extraOptions">⋮</button></span>
					<div class="hidden absolute right-0 top-0 bg-white rounded-lg drop-shadow-lg p-3 mt-5" id="showExtraOptions">
						<button class="btn-primary mt-5" data-binding="true" data-fun="changeCustomer">Anderem Kuden zuweisen</button>
						<button class="btn-delete" data-binding="true" data-fun="deleteOrder">Auftrag löschen</button>
					</div>
				</div>
				<div class="mt-1">
					<label for="orderTitle">Auftragsbezeichnung:</label>
					<input class="input-primary w-full" id="orderTitle" value="<?= $auftrag->getAuftragsbezeichnung() ?>" autocomplete="none" data-write="true" data-fun="editTitle">
				</div>
				<div class="mt-1">
					<div>
						<span>Auftragsbeschreibung:</span>
						<span class="cursor-pointer" data-fun="toggleOrderDescription" data-binding="true">
							<span class="toggle-up"><?= Icon::getDefault("iconChevronUp") ?></span>
							<span class="toggle-down hidden"><?= Icon::getDefault("iconChevronDown") ?></span>
						</span>
					</div>
					<div>
						<textarea class="orderDescription input-primary w-full" autocomplete="none" data-write="true" data-fun="editDescription" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"><?= $auftrag->getAuftragsbeschreibung() ?></textarea>
						<input class="orderDescription input-primary w-full hidden" autocomplete="none" data-write="true" data-fun="editDescription" value="<?= $auftrag->getAuftragsbeschreibung() ?>">
					</div>
				</div>
				<div class="mt-1">
					<label for="orderType">Auftragstyp:</label>
					<select class="input-primary w-full" id="orderType" data-write="true" data-fun="editOrderType">
						<?php foreach ($auftragsTypen as $type): ?>
							<option value="<?= $type["id"] ?>" <?= $auftragsTyp == $type["id"] ? "selected" : "" ?>><?= $type["Auftragstyp"] ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="m-2">
					<p>Auftragseingang:
						<input class="input-primary m-1" type="date" value="<?= $auftrag->getDate() ?>" data-write="true" data-fun="updateDate">
					</p>
					<p>Termin:
						<input class="input-primary m-1" type="date" value="<?= $auftrag->getDeadline() ?>" id="inputDeadline" data-write="true" data-fun="updateDeadline">
						<input type="checkbox" data-binding="true" data-fun="setDeadlineState" <?= $auftrag->getDeadline() == "" ? "checked" : "" ?>> Kein Termin
					</p>
				</div>
				<div>
					<?php if (Tools::get("show") == "true" && $auftrag->getInvoiceId() != 0) : ?>
						<button class="btn-primary" onclick="location.href= '<?= Link::getPageLink('rechnung') ?>?target=view&id=<?= $orderId ?>'">Rechnung anzeigen</button>
					<?php else: ?>
						<button class="btn-primary" onclick="location.href= '<?= Link::getPageLink('rechnung') ?>?target=create&id=<?= $orderId ?>'">Rechnung generieren</button>
						<?php if ($auftrag->getIsArchiviert() == false) : ?>
							<button class="btn-primary" data-binding="true" data-fun="archivieren">Auftrag archivieren</button>
						<?php endif; ?>
						<button class="btn-primary" data-binding="true" data-fun="setOrderFinished">Auftrag ist fertig</button>
					<?php endif; ?>
				</div>
			</div>

			<div class="defCont col-span-6 md:col-span-2 2xl:col-span-3">
				<p class="font-bold">Kundeninfo</p>
				<div class="bg-white p-2 rounded-sm mt-1">
					<p><?= $kunde->getVorname() ?> <?= $kunde->getNachname() ?></p>
					<p><?= $kunde->getFirmenname() ?></p>
					<p class="mt-2"><?= $kunde->getStrasse() ?> <?= $kunde->getHausnummer() ?></p>
					<p><?= $kunde->getPostleitzahl() ?> <?= $kunde->getOrt() ?></p>
					<p><?= $kunde->getTelefonFestnetz() ?></p>
					<p><?= $kunde->getTelefonMobil() ?></p>
					<p><a href="mailto:<?= $kunde->getEmail() ?>"><?= $kunde->getEmail() ?></a></p>
					<select class="input-primary mt-2 w-60" data-write="true" data-fun="changeContact" id="showAnspr">
						<?php
	                    $optionsDefault = true;
foreach ($contactPersons as $contact): ?>
							<?php
    $selected = $contact["isSelected"] ? "selected" : "";
    $optionsDefault = $contact["isSelected"] ? false : true;
    ?>
							<option value="<?= $contact["id"] ?>" <?= $selected ?>><?= $contact["firstName"] ?> <?= $contact["lastName"] ?></option>
						<?php endforeach; ?>
						<?php if ($optionsDefault): ?>
							<option disabled selected>Kein Ansprechpartner ausgewählt</option>
						<?php endif; ?>
					</select>
				</div>
				<a class="text-blue-500	font-semibold mt-3" href="<?= Link::getPageLink("kunde") ?>?id=<?= $auftrag->getKundennummer() ?>">Kunde <span id="kundennummer"><?= $auftrag->getKundennummer() ?></span> anzeigen</a>
			</div>

			<div class="defCont col-span-6 md:col-span-4 2xl:col-span-3 schritte">
				<p class="font-bold">Bearbeitungsschritte und Aufgaben</p>
				<div class="flex mt-2 items-center">
					<div>
						<button class="btn-primary" data-binding="true" data-fun="toggleAddStep">Neu</button>
					</div>
					<div class="px-2 rounded ml-2">
						<?= TemplateController::getTemplate("inputSwitch", [
    "id" => "toggleSteps",
    "name" => "Alle Schritte anzeigen",
    "write" => "toggleSteps",
                        ]); ?>
					</div>
				</div>
				<div class="mt-2 bg-white p-2 rounded-md hidden" id="bearbeitungsschritte">
					<div>
						<p>Bezeichnung:</p>
						<input class="bearbeitungsschrittInput input-primary w-full mt-1" type="text" max="128" id="processingStepName">
					</div>
					<div class="mt-2">
						<p>Datum:</p>
						<div>
							<input class="bearbeitungsschrittInput input-primary mt-1" type="date" max="32" id="processingStepDate">
						</div>
					</div>
					<div class="mt-2">
						<p>Priorität:</p>
						<input class="bearbeitungsschrittInput mt-1" type="range" list="priority" id="processingStepPriority">
						<datalist id="priority">
							<option value="0"></option>
							<option value="25"></option>
							<option value="50"></option>
							<option value="75"></option>
							<option value="100"></option>
						</datalist>
					</div>
					<div class="mt-2">
						<?= TemplateController::getTemplate("inputSwitch", [
    "id" => "processingStepStatus",
    "name" => "Noch zu erledigen",
    "value" => "checked",
                        ]); ?>
					</div>
					<div class="mt-2">
						<?= TemplateController::getTemplate("inputSwitch", [
    "id" => "processingStepBy",
    "name" => "Zuweisen an:",
    "binding" => "updateSelectBy",
                        ]); ?>
					</div>
					<div class="mt-2">
						<select id="processingStepSelectBy" disabled>
							<?php foreach ($mitarbeiter as $m): ?>
								<option value="<?= $m["id"] ?>"><?= $m["prename"] ?> <?= $m["lastname"] ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mt-2">
						<button class="btn-primary" data-binding="true" data-fun="addBearbeitungsschritt" class="btn-primary">Hinzufügen</button>
						<button class="btn-cancel ml-1" data-binding="true" data-fun="toggleAddStep" class="btn-primary">Abbrechen</button>
					</div>
				</div>
				<div id="stepTable" class="mt-2"></div>
			</div>

			<div class="defCont col-span-6 md:col-span-2 2xl:col-span-3 schritteAdd">
				<div>
					<p class="font-bold">Notizen hinzufügen</p>
					<button class="btn-primary mt-2" data-binding="true" data-fun="addNewNote" id="addNewNote">Neu</button>
				</div>
				<div class="hidden mt-2" id="addNotes">
					<div class="bg-white w-full lg:w-2/3 rounded-lg p-2">
						<input type="text" class="noteTitle text-slate-700 font-bold outline-0" max="128">
						<textarea class="noteText mt-2 text-slate-500 resize-none w-full outline-0" type="text" max="128"></textarea>
						<div class="relative flex text-slate-600">
							<input type="date" value="<?= date("Y-m-d") ?>" class="noteDate">
						</div>
					</div>
					<div class="mt-2">
						<button class="btn-primary" data-binding="true" data-fun="sendNote">Hinzufügen</button>
						<button class="btn-cancel" data-binding="true" data-fun="cancelNote">Abbrechen</button>
					</div>
				</div>
			</div>

			<div class="defCont col-span-6 md:col-span-6 notes hidden" id="notesContainer">
				<p class="font-bold">Alle Notizen</p>
				<div class="grid grid-cols-4" id="noteContainer"></div>
			</div>

			<div class="defCont col-span-6 md:col-span-6 posten">
				<p class="inline-flex items-center">
					<span class="font-bold">Zeiten, Produkte und Kosten (netto)</span>
					<label class="inline-flex items-center">
						<input data-binding="true" data-fun="toggleInvoiceItems" type="checkbox" class="ml-3" <?= ClientSettings::getFilterOrderPosten() == true ? "checked" : "" ?>>
						<span class="ml-1">Rechnungsposten ausblenden</span>
					</label>
				</p>
				<div class="overflow-x-auto md:w-full">
					<?= TemplateController::getTemplate("invoiceItems", [
                        "services" => $services
                    ]); ?>
				</div>
			</div>

			<div class="defCont col-span-6 md:col-span-6 invoice">
				<p class="font-bold">Rechnungsposten (netto)</p>
				<div id="invoicePostenTable" class="mt-2 overflow-x-auto md:w-full"><?= $auftrag->getInvoicePostenTable() ?></div>
			</div>

			<div class="defCont col-span-6 md:col-span-3 fahrzeuge">
				<p>
					<span class="font-bold">Fahrzeuge</span>
					<button class="ml-1 info-button" data-info="1"></button>
				<p>
				<div class="mt-2">
					<select id="selectVehicle" class="input-primary" data-fun="selectVehicle" data-write="true">
						<option value="0" selected disabled>Bitte auswählen</option>
						<?php foreach ($fahrzeuge as $f): ?>
							<option value="<?= $f['Nummer'] ?>"><?= $f['Kennzeichen'] ?> <?= $f['Fahrzeug'] ?></option>
						<?php endforeach; ?>
						<option value="addNew">Neues Fahrzeug hinzufügen</option>
					</select>
					<button class="m-1 btn-primary" data-binding="true" data-fun="addExistingVehicle">Übernehmen</button>
				</div>
				<div class="innerDefCont hidden" id="addVehicle">
					<p>Kfz-Kennzeichen:<br><input id="kfz" class="input-primary"></p>
					<p>Fahrzeug:<br><input id="fahrzeug" class="input-primary"></p>
					<button class="btn-primary mt-2" data-binding="true" data-fun="addNewVehicle">Hinzufügen</button>
				</div>
				<div id="fahrzeugTable" class="mt-2 overflow-x-auto md:w-full"></div>
			</div>

			<div class="defCont col-span-3 md:col-span-1 farben">
				<p class="font-bold">Farben</p>
				<span id="showColors"><?= $farbTable ?></span>
				<div class="mt-2">
					<button class="btn-primary" data-binding="true" data-fun="addColor">Neue Farbe</button>
					<br>
					<button class="btn-primary mt-2" data-fun="toggleCS" data-binding="true">Vorhandene Farbe</button>
				</div>
			</div>

			<div class="defCont col-span-3 md:col-span-2 preis">
				<p class="font-bold">Kalkulation:</p>
				<p>Rechnungsbetrag:
				<p id="totalPrice" class="font-bold text-2xl">
					<?= number_format($auftrag->preisBerechnen(), 2, ',', '') . "€" ?>
				</p>
				</p>
				<p>
					<span>Gewinn (netto): </span>
					<?= number_format($auftrag->gewinnBerechnen(), 2, ',', '') . "€" ?>
				</p>
			</div>

			<div class="defCont col-span-6 md:col-span-4 upload">
				<p class="font-bold">Dateien zum Auftrag hinzufügen</p>
				<?= TemplateController::getTemplate("uploadFile", [
                    "target" => "order",
                ]); ?>
				<div id="showFilePrev">
					<?= $showFiles ?>
				</div>
			</div>

			<div class="defCont col-span-6 md:col-span-2 verlauf">
				<p class="font-bold" data-binding="true" data-fun="showAuftragsverlauf">Auftragsverlauf anzeigen</p>
				<div class="mt-2 orderHistory">
					<?= $auftragsverlauf ?>
				</div>
				<div class="mt-2">
					<button class="btn-primary mt-2" data-binding="true" data-fun="addList">Liste hinzufügen</button>
					<button class="info-button ml-1" data-info="2"></button>
				</div>
			</div>

			<template id="templateFarbe">
				<div class="defCont">
					<label>
						<p class="text-sm">Farbbezeichnung</p>
						<input class="colorInput input-primary" type="text" max="32" placeholder="619 verkehrsgrün">
					</label>
					<label>
						<p class="text-sm">Farbtyp</p>
						<input class="colorInput input-primary" type="text" max="32" placeholder="751C">
					</label>
					<label>
						<p class="text-sm">Hersteller</p>
						<input class="colorInput input-primary" type="text" max="32" placeholder="Oracal">
					</label>
					<label class="ml-1">
						<p class="text-sm">Farbe (Hex)</p>
						<input class="colorInput jscolor input-primary outline outline-offset-1" type="text" max="32" data-write="true" data-fun="checkHexCode">
					</label>
					<br>
					<button class="btn-primary" data-fun="sendColor">Hinzufügen</button>
				</div>
				<div class="defCont font-mono" id="cpContainer"></div>
			</template>

			<template id="templateExistingColor">
				<p class="font-semibold">Vorhandene Farben:</p>
				<div class="w-full h-60 overflow-y-scroll mt-1 bg-white rounded-md"></div>
			</template>

			<template id="templateAlertBox">
				<p>Möchtest Du den Auftrag sicher löschen?</p>
				<button id="deleteOrder" class="btn-delete">Ja</button>
				<button id="closeAlert" class="btn-primary">Nein</button>
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
				<div class="bg-white rounded-lg p-2 m-2 relative">
					<input type="text" class="noteTitle text-slate-900 font-bold outline-0" max="128">
					<textarea class="noteText mt-2 text-slate-600 resize-none w-full outline-0 h-32" type="text" max="128"></textarea>
					<hr class="p-2 mt-1">
					<div class="relative flex text-slate-600">
						<span class="noteDate"></span>
						<button class="border-0 ml-auto showDelete">...</button>
					</div>
					<button class="btn-delete noteDelete hidden absolute right-0 bottom-4">Löschen</button>
				</div>
			</template>
		<?php endif; ?>
	<?php endif; ?>
</div>