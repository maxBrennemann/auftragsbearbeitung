<?php

use Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;
use Classes\Upload;

use Classes\Project\Auftrag;
use Classes\Project\Icon;
use Classes\Project\Kunde;
use Classes\Project\Fahrzeug;
use Classes\Project\Auftragsverlauf;
use Classes\Project\Liste;
use Classes\Project\Search;
use Classes\Project\ClientSettings;
use Classes\Project\Color;

?>

<script src="<?=Link::getResourcesShortLink("colorpicker.js", "js")?>"></script>
<script src="<?=Link::getResourcesShortLink("list.js", "js")?>"></script>

<?php

$auftragsId = -1;
$auftragAnzeigen = Link::getPageLink("auftrag");
$show = false;
$searchTable = "";
$auftragsTypBezeichnung = "";
$optionsDefault = true;

if (isset($_GET['id'])) {
	$auftragsId = (int) $_GET['id'];
	if ($auftragsId > 0) {
		$auftrag = new Auftrag($auftragsId);
		$fahrzeugTable = $auftrag->getFahrzeuge();
		$farbTable = $auftrag->getColors();
		$kunde = new Kunde($auftrag->getKundennummer());

		$fahrzeuge = Fahrzeug::getSelection($auftrag->getKundennummer());
		$fahrzeugeAuftrag = $auftrag->getLinkedVehicles();
		
		$leistungen = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");

		$showFiles = Auftrag::getFiles($auftragsId);
		$auftragsverlauf = (new Auftragsverlauf($auftragsId))->representHistoryAsHTML();
		$showLists = Liste::chooseList();
		$showAttachedLists = $auftrag->showAttachedLists();
		$contactPersons = $auftrag->getContactPersons();

		$auftragsTypBezeichnung = $auftrag->getAuftragstypBezeichnung();
		$auftragsTyp = $auftrag->getAuftragstyp();
		$auftragsTypen = Auftrag::getAllOrderTypes();
	} else {
		$auftragsId = -1;
	}
}

if (isset($_POST['filesubmitbtnV'])) {
	$vehicleId = $_POST['vehicleImageId'];
	echo $vehicleId;
	$upload = new Upload();
	$upload->uploadFilesVehicle($vehicleId, $auftragsId);
}

/* Paremter wird gebraucht, falls Rechnung gestellt wurde, aber der Auftrag trotzdem gezeigt werden soll */
if (isset($_GET['show'])) {
	$show = true;
}

$mitarbeiter = DBAccess::selectQuery("SELECT prename, lastname, id FROM user");
$colors = Color::get();

if ($auftrag->istRechnungGestellt() && $show == false): ?>
<div>
	<div class="defCont" id="orderFinished">
		<p>Auftrag <?=$auftrag->getAuftragsnummer()?> wurde abgeschlossen. Rechnungsnummer: <span id="rechnungsnummer"><?=$auftrag->getRechnungsnummer()?></span></p>
		<button class="btn-primary-new" data-fun="showAuftrag" data-binding="true">Auftrag anzeigen</button>
		<?php
			$invoiceLink = $auftrag->getKundennummer() . "_" . $auftrag->getRechnungsnummer() . ".pdf";
			$invoiceLink = Link::getResourcesShortLink($invoiceLink, "pdf");
		?>
		<a class="link-primary" href="<?=$invoiceLink?>">Zur Rechnung</a>
	</div>
	<?php if (!$auftrag->getIsPayed()): ?>
		<div class="defCont">
			<div id="orderPaymentState">
				<p>Die Rechnung wurde noch nicht beglichen.</p>
				<label>
					<input type="date" id="inputPayDate">
				</label>
				<select id="paymentType">
					<option value="unbezahlt">Unbezahlt</option>
					<option value="ueberweisung">Überweisung</option>
					<option value="bar">Bar</option>
					<option value="paypal">PayPal</option>
					<option value="kreditkarte">Kreditkarte</option>
					<option value="amazonpay">AmazonPay</option>
					<option value="weiteres">Weiteres</option>
				</select>
				<button class="btn-primary" onclick="setPayed()">Rechnung wurde bezahlt</button>
			</div>
			<script>
				function setPayed() {
					const date = document.getElementById('inputPayDate').value;
					const paymentType = document.getElementById('paymentType').value;

					ajax.post({
						r: "setInvoiceData",
						id: <?=$auftragsId?>,
						invoice: <?=$auftrag->getRechnungsnummer()?>,
						date: date,
						paymentType: paymentType,
					}).then(r => {
						if (r.status == "success") {
							document.getElementById('orderPaymentState').innerHTML = `<p>Die Rechnung wurde am ${date} mit ${paymentType} bezahlt.</p>`;
						}
					});
				}
			</script>
		</div>
	<?php else: ?>
		<div class="defCont">
			<p>Die Rechnung wurde am <?=$auftrag->getPaymentDate()?> mit <?=$auftrag->getPaymentType()?> bezahlt.</p>
		</div>
	<?php endif; ?>
	<div class="defCont">
		<embed type="application/pdf" src="<?=$invoiceLink?>" width="100%" height="400">
	</div>
	<style>
		main {
			display: inline; /* quick fix */
		}
	</style>
<?php else: ?>
	<div class="defCont">
		<p class="font-bold">Kundeninfo</p>
		<div class="bg-white p-2 rounded-sm">
			<p><?=$kunde->getVorname()?> <?=$kunde->getNachname()?></p>
			<p><?=$kunde->getFirmenname()?></p>
			<p class="mt-2"><?=$kunde->getStrasse()?> <?=$kunde->getHausnummer()?></p>
			<p><?=$kunde->getPostleitzahl()?> <?=$kunde->getOrt()?></p>
			<p><?=$kunde->getTelefonFestnetz()?></p>
			<p><?=$kunde->getTelefonMobil()?></p>
			<p><a href="mailto:<?=$kunde->getEmail()?>"><?=$kunde->getEmail()?></a></p>
			<select class="input-primary-new mt-2 w-60" data-write="true" data-fun="changeContact" id="showAnspr">
				<?php foreach ($contactPersons as $contact): ?>
					<?php
						$selected = $contact['isSelected'] ? "selected" : "";
						$optionsDefault = $contact['isSelected'] ? false : true;
					?>
					<option value="<?=$contact['id']?>" <?=$selected?>><?=$contact['firstName']?> <?=$contact['lastName']?></option>
				<?php endforeach; ?>
				<?php if ($optionsDefault): ?>
					<option disabled selected>Kein Ansprechpartner ausgewählt</option>
				<?php endif; ?>
			</select>
		</div>
		<a class="text-blue-500	font-semibold mt-3" href="<?=Link::getPageLink("kunde")?>?id=<?=$auftrag->getKundennummer()?>">Kunde <span id="kundennummer"><?=$auftrag->getKundennummer()?></span> anzeigen</a>
	</div>
	<div class="defCont auftragsinfo">
		<div class="relative">
			<span class="font-bold">Auftrag <span id="auftragsnummer"><?=$auftrag->getAuftragsnummer()?></span><button class="float-right border-none w-4" id="extraOptions">⋮</button></span>
			<div class="hidden absolute right-0 top-0 bg-white rounded-lg drop-shadow-lg p-3 mt-5" id="showExtraOptions">
				<button class="btn-attention mt-5" id="deleteOrder">Auftrag löschen</button>
			</div>
		</div>
		<div class="inputCont">
			<label for="orderTitle">Auftragsbezeichnung:</label>
			<input class="input-primary-new w-full" id="orderTitle" value="<?=$auftrag->getAuftragsbezeichnung()?>" autocomplete="none" data-write="true" data-fun="editTitle">
		</div>
		<div class="inputCont">
			<div>
				<span>Auftragsbeschreibung:</span>
				<span class="cursor-pointer" data-fun="toggleOrderDescription" data-binding="true">
					<span class="toggle-up"><?=Icon::getDefault("iconChevronUp")?></span>
					<span class="toggle-down hidden"><?=Icon::getDefault("iconChevronDown")?></span>
				</span>
			</div>
			<div>
				<textarea class="orderDescription input-primary-new w-full" autocomplete="none" data-write="true" data-fun="editDescription" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"><?=$auftrag->getAuftragsbeschreibung()?></textarea>
				<input class="orderDescription input-primary-new w-full hidden" autocomplete="none" data-write="true" data-fun="editDescription" value="<?=$auftrag->getAuftragsbeschreibung()?>">
			</div>
		</div>
		<div class="inputCont">
			<label for="orderType">Auftragstyp:</label>
			<select class="input-primary-new w-full" id="orderType" data-write="true" data-fun="editOrderType">
				<?php foreach($auftragsTypen as $type): ?>
				<option value="<?=$type["id"]?>" <?=$auftragsTyp == $type["id"] ? "selected" : "" ?>><?=$type["Auftragstyp"]?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="m-2">
			<p>Auftragseingang: 
				<input class="input-primary-new m-1" type="date" value="<?=$auftrag->getDate()?>" data-write="true" data-fun="updateDate">
			</p>
			<p>Termin: 
				<input class="input-primary-new m-1" type="date" value="<?=$auftrag->getDeadline()?>" id="inputDeadline" data-write="true" data-fun="updateDeadline">
				<input type="checkbox" data-binding="true" data-fun="setDeadlineState" <?=$auftrag->getDeadline() == "" ? "checked" : "" ?>> Kein Termin
			</p>
		</div>
		<div>
			<button class="btn-primary-new" onclick="location.href= '<?=Link::getPageLink('rechnung')?>?target=create&id=<?=$auftragsId?>'">Rechnung generieren</button>
			<?php if ($auftrag->getIsArchiviert() == false) :?>
				<button class="btn-primary-new" data-binding="true" data-fun="archvieren">Auftrag archivieren</button>
			<?php endif; ?>
			<button class="btn-primary-new" data-binding="true" data-fun="setOrderFinished">Auftrag ist fertig</button>
		</div>
	</div>
	<div class="defCont schritte">
		<p class="font-bold">Bearbeitungsschritte und Aufgaben</p>
		<div class="flex mt-2 items-center">
			<div>
				<button class="btn-primary-new" data-binding="true" data-fun="addStep">Neu</button>
			</div>
			<div class="px-2 rounded ml-2">
				<label class="flex items-center cursor-pointer">
					<input type="checkbox" id="toggleSteps" value="" class="sr-only peer">
					<div class="relative w-11 h-6 bg-gray-400 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
					<span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Alle Schritte anzeigen</span>
				</label>
			</div>
		</div>
		<div class="bg-white p-2 rounded-md hidden" id="bearbeitungsschritte">
			<div>
				<div class="flex flex-col">
					<p>Bezeichnung:</p>
					<input class="bearbeitungsschrittInput input-primary-new w-full" type="text" max="128">
				</div>
			</div>
			<div>
				<div class="flex flex-col">
					<p>Datum:</p>
					<div>
						<input class="bearbeitungsschrittInput input-primary-new" type="date" max="32">
					</div>
				</div>
			</div>
			<div>
				<div class="flex flex-col">
					<p>Priorität:</p>
					<div>
						<input class="bearbeitungsschrittInput" type="range" list="priority">
					</div>
				</div>
				<datalist id="priority">
					<option value="0"></option>
					<option value="25"></option>
					<option value="50"></option>
					<option value="75"></option>
					<option value="100"></option>
				</datalist>
			</div>
			<div>
				<div class="flex flex-col">
					<p>Noch zu erledigen:</p>
					<label>
						<input type="checkbox" id="" value="checked" class="sr-only peer">
						<div class="relative w-11 h-6 bg-gray-400 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
					</label>
				</div>
			</div>
			<form name="isAlreadyDone" class="hidden">
				<input type="radio" name="isDone" value="show" checked>Noch zu erledigen<br>
				<input type="radio" name="isDone" value="hide">Schon erledigt<br>
			</form>
			<div>
				<input type="checkbox" name="assignTo" onclick="document.getElementById('selectMitarbeiter').disabled = false;">Zuweisen an:</input>
				<br>
				<select id="selectMitarbeiter" disabled>
					<?php foreach ($mitarbeiter as $m): ?>
						<option value="<?=$m['id']?>"><?=$m['prename']?> <?=$m['lastname']?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="mt-2">
				<button class="btn-primary-new" data-binding="true" data-fun="addBearbeitungsschritt" class="btn-primary">Hinzufügen</button>
				<button class="btn-cancel" data-binding="true" data-fun="addBearbeitungsschritt" class="btn-primary">Abbrechen</button>
			</div>
		</div>
		<div id="stepTable">
			<?=$auftrag->getOpenBearbeitungsschritteTable()?>
		</div>
	</div>
	<div class="defCont schritteAdd">
		<div>
			<p class="font-bold">Notizen hinzufügen</p>
			<button class="btn-primary-new mt-2" data-binding="true" data-fun="addNewNote" id="addNewNote">Neu</button>
		</div>
		<div class="hidden mt-2" id="addNotes">
			<div class="bg-white w-2/3 rounded-lg p-2">
				<input type="text" class="noteTitle text-slate-700 font-bold outline-0" max="128">
				<textarea class="noteText mt-2 text-slate-500 resize-none w-full outline-0" type="text" max="128"></textarea>
				<div class="relative flex text-slate-600">
					<span class="noteDate"><?=date("d.m.Y")?></span>
				</div>
			</div>
			<div class="mt-2">
				<button class="btn-primary-new" data-binding="true" data-fun="sendNote">Hinzufügen</button>
				<button class="btn-cancel" data-binding="true" data-fun="cancelNote">Abbrechen</button>
			</div>
		</div>
	</div>
	<div class="defCont notes hidden" id="notesContainer">
		<p class="font-bold">Alle Notizen</p>
		<div class="grid grid-cols-4" id="noteContainer"></div>
	</div>
	<div class="defCont posten">
		<p><span class="font-bold inline-flex items-center">Zeiten, Produkte und Kosten (netto)</span> <input id="rechnungspostenAusblenden" type="checkbox" <?=ClientSettings::getFilterOrderPosten() == true ? "checked" : ""?>> Rechnungsposten ausblenden</p>
		<div id="auftragsPostenTable" class="overflow-x-auto lg:w-full">
			<?=$auftrag->getAuftragspostenAsTable()?>
		</div>
		<div class="buttonDiv">
			<button class="addToTable" data-binding="true" data-fun="showPostenAdd">+</button>
		</div>
		<div id="showPostenAdd" style="display: none;">
			<div class="tabcontainer">
				<button class="tablinks activetab" onclick="openTab(event, 0)">Zeiterfassung</button>
				<button class="tablinks" onclick="openTab(event, 1)">Kostenerfassung</button>
				<button class="tablinks" onclick="openTab(event, 2)">Produkte</button>
			</div>
			<div class="tabcontent" id="tabZeit" style="display: block;">
				<div id="addPostenZeit">
					<div class="container">
						<span>Zeit in Minuten<br><input class="postenInput" id="time" type="number" min="0"></span>
						<br>
						<span>Stundenlohn in €<br><input class="postenInput" id="wage" type="number" value="<?=$auftrag->getDefaultWage()?>"></span>
						<br>
						<span>Beschreibung<br>
							<textarea id="descr" class="border-2 rounded-md p-2" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea>
						</span>
						<br>
						<button id="addTimeButton" data-binding="true" data-fun="addTime" class="btn-primary">Hinzufügen</button>
					</div>
					<div class="container">
						<span>Erweiterte Zeiterfassung:</span>
						<br>
						<span>Arbeitszeit(en)</span>
						<div id="extendedTimeInput">
						</div>
						<button class="addToTable" data-binding="true" data-fun="createTimeInputRow">+</button>
						<p id="showTimeSummary"></p>
					</div>
				</div>
			</div>
			<div class="tabcontent" id="tabLeistung">
				<div id="addPostenLeistung">
					<select id="selectLeistung" data-write="true" data-fun="selectLeistung">
						<?php foreach ($leistungen as $leistung): ?>
							<option value="<?=$leistung['Nummer']?>" data-aufschlag="<?=$leistung['Aufschlag']?>"><?=$leistung['Bezeichnung']?></option>
						<?php endforeach; ?>
					</select>
					<br>
					<span>Menge:<br><input class="postenInput" id="anz" type="number" value="1"></span><br>
					<span>Mengeneinheit:<br>
						<input class="postenInput" id="meh" data-binding="true" data-fun="mehListener">
						<span id="meh_dropdown" data-binding="true" data-fun="mehListener">▼</span>
						<div class="selectReplacer" id="selectReplacerMEH">
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Stück</p>
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">m²</p>
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Meter</p>
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Stunden</p>
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">lfm</p>
						</div>
					</span><br>
					<span>Beschreibung:
						<br>
						<textarea id="bes" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'" class="border-2 rounded-md p-2"></textarea>
					</span>
					<br>
					<span>Einkaufspreis:<br><input class="postenInput" type="number" id="ekp" value="0"></span><br>
					<span>Verkaufspreis:<br><input class="postenInput" type="number" id="pre" value="0"></span><br>
					<button data-binding="true" data-fun="addLeistung" id="addLeistungButton" class="btn-primary">Hinzufügen</button>
				</div>		
			</div>
			<div class="tabcontent" id="tabProdukte">
				<div id="addPostenProdukt">
					<span>Produkt suchen:</span>
					<div>
						<input type="search" id="productSearch">
						<span class="lupeSpan searchProductEvent"><span class="lupe searchProductEvent">&#9906;</span></span>
						<p><i>Zubehör, Montagematerial, Textilien...</i></p>
					</div>
					<div id="resultContainer"></div>
					<span>Menge: <input class="postenInput" id="posten_produkt_menge" type="number"></span>
					<button data-binding="true" data-fun="addProductCompact" class="btn-primary">Hinzufügen</button>
					<br>
					<a href="<?=Link::getPageLink("neues-produkt");?>">Neues Produkt hinzufügen</a>
				</div>
			</div>
			<div class="tabcontentEnd">
				<div>
					<span id="showOhneBerechnung" class="ml-2">
						<input id="ohneBerechnung" type="checkbox">
						<span class="ml-2">Ohne Berechnung</span>
					</span>
					<br>
					<span id="showAddToInvoice" class="ml-2">
						<input id="addToInvoice" type="checkbox">
						<span class="ml-2">Der Rechnung hinzufügen</span>
					</span>
					<br>
					<span id="showDiscount" class="ml-2 mt-2">
						<input type="range" min="0" max="100" value="0" name="discountInput" id="discountInput" oninput="showDiscountValue.value = discountInput.value + '%'">
						<output id="showDiscountValue" name="showDiscountValue" for="discountInput">0%</output> Rabatt
					</span>
					<div id="generalPosten"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="defCont invoice">
		<p class="font-bold">Rechnungsposten (netto)</p>
		<div id="invoicePostenTable"><?=$auftrag->getInvoicePostenTable()?></div>
	</div>
	<div class="defCont preis">
		<p class="font-bold">Gesamtpreis (netto):</p>
		<span id="gesamtpreis">
			<?=number_format($auftrag->preisBerechnen(), 2, ',', '') . "€"?>
		</span>
		<span>
			<?=number_format($auftrag->gewinnBerechnen(), 2, ',', '') . "€"?> (Gewinn netto)
		</span>
	</div>
	<div class="defCont fahrzeuge">
		<p><span class="font-bold">Fahrzeuge</span><button class="ml-1 infoButton" data-info="1">i</button><p>
		<div>
			<select id="selectVehicle" data-write="true" data-fun="selectVehicle" class="input-primary-new">
				<option value="0" selected disabled>Bitte auswählen</option>
				<?php foreach ($fahrzeuge as $f): ?>
					<option value="<?=$f['Nummer']?>"><?=$f['Kennzeichen']?> <?=$f['Fahrzeug']?></option>
				<?php endforeach; ?>
				<option value="addNew">Neues Fahrzeug hinzufügen</option>
			</select>
			<button class="m-1 btn-primary-new" data-binding="true" data-fun="addExistingVehicle">Übernehmen</button>
		</div>
		<div class="innerDefCont" id="addVehicle" style="display: none;">
			<p>Kfz-Kennzeichen:<br><input id="kfz" class="input-primary-new"></p>
			<p>Fahrzeug:<br><input id="fahrzeug" class="input-primary-new"></p>
			<button class="btn-primary-new mt-2" data-binding="true" data-fun="addNewVehicle">Hinzufügen</button>
		</div>
		<div><?=$auftrag->getFahrzeuge();?></div>
		<br>
		<form class="fileUploader" data-target="vehicle" name="vehicle" method="post" enctype="multipart/form-data" id="fileVehicle" style="display: none">
			<input type="file" name="uploadedFile" multiple form="fileVehicle">
			<input name="orderid" value="<?=$auftragsId?>" hidden>
		</form>
	</div>
	<div class="defCont farben">
		<p class="font-bold">Farben</p>
		<span id="showColors"><?=$farbTable?></span>
		<div class="mt-2">
			<button class="btn-primary-new" data-binding="true" data-fun="addColor">Neue Farbe</button>
			<br>
			<button class="btn-primary-new mt-2" data-fun="toggleCS" data-binding="true">Vorhandene Farbe</button>
		</div>
	</div>
	<div class="defCont upload">
		<p class="font-bold">Dateien zum Auftrag hinzufügen</p>
		<form class="fileUploader" method="post" enctype="multipart/form-data" data-target="order" name="auftragUpload" id="uploadFilesOrder">
			<input name="auftrag" value="<?=$auftragsId?>" hidden>
		</form>
		<p>Hier Dateien per Drag&Drop ablegen oder 
			<label class="uploadWrapper">
				<input type="file" name="uploadedFile" multiple class="fileUploadBtn" form="uploadFilesOrder">
				hier hochladen
			</label>
		</p>
		<div class="filesList defCont"></div>
		<div id="showFilePrev">
			<?=$showFiles?>
		</div>
	</div>
	<div class="defCont verlauf">
		<p class="font-bold" data-binding="true" data-fun="showAuftragsverlauf">Auftragsverlauf anzeigen</p>
		<?=$auftragsverlauf?>
		<br>
		<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" onclick="addList();">Liste hinzufügen</button><button class="infoButton" data-info="2">i</button>
		<div class="defCont" id="listauswahl" style="display: none;">
			<?=$showLists?>
		</div>
	</div>
	<div class="liste">
		<?=$showAttachedLists?>
	</div>
	<template id="templateFarbe">
		<div class="defCont">
			<label>
				<p class="text-sm">Farbbezeichnung</p>
				<input class="colorInput input-primary-new" type="text" max="32" placeholder="619 verkehrsgrün">
			</label>
			<label>
				<p class="text-sm">Farbtyp</p>
				<input class="colorInput input-primary-new" type="text" max="32" placeholder="751C">
			</label>
			<label>
				<p class="text-sm">Hersteller</p>
				<input class="colorInput input-primary-new" tyep="text" max="32" placeholder="Oracal">
			</label>
			<label class="ml-1">
				<p class="text-sm">Farbe (Hex)</p>
				<input class="colorInput jscolor input-primary-new outline outline-offset-1" type="text" max="32" data-write="true" data-fun="checkHexCode">
			</label>
			<br>
			<button class="btn-primary-new" data-fun="sendColor">Hinzufügen</button>
		</div>
		<div class="defCont" id="cpContainer"></div>
	</template>
	<template id="templateExistingColor">
		<div class="defCont" id="csContainer">
			<p class="font-semibold mb-2">Vorhandene Farben:</p>
			<div class="w-full h-60 overflow-y-scroll p-2 m-2 bg-white rounded-md">
			<?php insertTemplate('files/res/views/colorView.php', ["colors" => $colors,]);?>
			</div>
			<button class="btn-primary-new" data-binding="true" data-fun="addSelectedColors">Farbe(n) übernehmen</button>
		</div>
	</template>
	<template id="templateAlertBox">
		<p>Möchtest Du den Auftrag sicher löschen?</p>
		<button id="deleteOrder" class="btn-attention">Ja</button>
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
	<template id="templateTimeInput">
		<p class="timeInputWrapper">von <input class="timeInput" type="time" min="05:00" max="23:00"> bis <input class="timeInput"  type="time" min="05:00" max="23:00"> am <input class="dateInput" type="date"></p>
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
			<button class="btn-attention noteDelete hidden absolute right-0 bottom-4">Löschen</button>
		</div>
	</template>
</div>
<?php endif; ?>