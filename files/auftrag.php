<script src="<?=Link::getResourcesShortLink("tableeditor.js", "js")?>"></script>
<script src="<?=Link::getResourcesShortLink("classes/fileUploader.js", "js")?>"></script>
<script src="<?=Link::getResourcesShortLink("print.js", "js")?>"></script>
<script src="<?=Link::getResourcesShortLink("colorpicker.js", "js")?>"></script>
<script src="<?=Link::getResourcesShortLink("list.js", "js")?>"></script>

<?php
require_once('classes/project/Auftrag.php');
require_once('classes/project/Search.php');
require_once('classes/project/Rechnung.php');
require_once('classes/project/Auftragsverlauf.php');
require_once('classes/project/Fahrzeug.php');
require_once('classes/project/Kunde.php');
require_once('classes/DBAccess.php');
require_once('classes/Upload.php');
require_once('classes/project/Liste.php');
require_once('classes/project/Table.php');

$auftragsId = -1;
$auftragAnzeigen = Link::getPageLink("auftrag");
$show = false;
$searchTable = "";
$auftragsTypBezeichnung = "";

if (isset($_GET['id'])) {
	$auftragsId = (int) $_GET['id'];
	if ($auftragsId > 0) {
		$auftrag = new Auftrag($auftragsId);
		$fahrzeugTable = $auftrag->getFahrzeuge();
		$farbTable = $auftrag->getFarben();
		$kunde = new Kunde($auftrag->getKundennummer());

		$fahrzeuge = Fahrzeug::getSelection($auftrag->getKundennummer());
		$fahrzeugeAuftrag = $auftrag->getLinkedVehicles();
		
		$leistungen = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");

		$showFiles = Upload::getFilesAuftrag($auftragsId);
		$auftragsverlauf = (new Auftragsverlauf($auftragsId))->representHistoryAsHTML();
		$showLists = Liste::chooseList();
		$showAttachedLists = $auftrag->showAttachedLists();
		$ansprechpartner = $auftrag->bekommeAnsprechpartner();

		$auftragsTypBezeichnung = $auftrag->getAuftragstypBezeichnung();
		$auftragsTyp = $auftrag->getAuftragstyp();
		$auftragsTypen = Auftrag::getAllOrderTypes();
	} else {
		$auftragsId = -1;
	}
}

if (isset($_GET['query'])) {
	$query = $_GET['query'];
	$searchTable = Search::getSearchTable($query, "order", null, true);
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

$mitarbeiter = DBAccess::selectQuery("SELECT Vorname, Nachname, id FROM mitarbeiter");
$colors = DBAccess::selectQuery("SELECT Farbe, Bezeichnung, Hersteller, Farbwert, id AS Nummer FROM color");

if ($auftragsId == -1): ?>
	<style>
		main {
			display: inline; /* quick fix */
		}
	</style>
	<input type="number" min="1" oninput="document.getElementById('auftragsLink').href = '<?=$auftragAnzeigen?>?id=' + this.value;">
	<a href="#" id="auftragsLink">Auftrag anzeigen</a>
	<br>
	<input type="text" oninput="document.getElementById('auftragSuche').href = '<?=$auftragAnzeigen?>?query=' + this.value;">
	<a href="#" id="auftragSuche">Auftrag suchen</a>
	<br><br>
	<?=$searchTable?>
<?php elseif ($auftrag->istRechnungGestellt() && $show == false): ?>
	<div class="defCont">
		<p>Auftrag <?=$auftrag->getAuftragsnummer()?> wurde abgeschlossen. Rechnungsnummer: <span id="rechnungsnummer"><?=$auftrag->getRechnungsnummer()?></span></p>
		<button onclick="print('rechnungsnummer', 'Rechnung');">Rechnungsblatt anzeigen</button>
		<button onclick="showAuftrag()">Auftrag anzeigen</button>
		<?php
			$invoiceLink = $auftrag->getKundennummer() . "_" . $auftrag->getRechnungsnummer() . ".pdf";
			$invoiceLink = Link::getResourcesShortLink($invoiceLink, "pdf");
		?>
		<a href="<?=$invoiceLink?>">Zur Rechnung</a>
		<embed type="application/pdf"
		src="<?=$invoiceLink?>"
		width="100%"
		height="400">
	</div>
	<style>
		main {
			display: inline; /* quick fix */
		}
	</style>
<?php else: ?>
	<div class="defCont">
		<p class="font-bold">Kundeninfo</p>
		<p><?=$kunde->getVorname()?> <?=$kunde->getNachname()?></p>
		<p><?=$kunde->getFirmenname()?></p>
		<p class="mt-2"><?=$kunde->getStrasse()?> <?=$kunde->getHausnummer()?></p>
		<p><?=$kunde->getPostleitzahl()?> <?=$kunde->getOrt()?></p>
		<p><?=$kunde->getTelefonFestnetz()?></p>
		<p><?=$kunde->getTelefonMobil()?></p>
		<p><a href="mailto:<?=$kunde->getEmail()?>"><?=$kunde->getEmail()?></a></p>
		<p id="showAnspr"><?php if ($ansprechpartner != -1): ?>Ansprechpartner: <?=$ansprechpartner['Vorname']?> <?=$ansprechpartner['Nachname']?><?php endif;?></p>
		<span>Ansprechpartner ändern<button class="actionButton" onclick="changeContact()">✎</button></span>
		<br>
		<a class="text-blue-500	font-semibold" href="<?=Link::getPageLink("kunde")?>?id=<?=$auftrag->getKundennummer()?>">Kunde <span id="kundennummer"><?=$auftrag->getKundennummer()?></span> zeigen</a>
	</div>
	<div class="defCont auftragsinfo">
		<p class="font-bold">Auftrag <span id="auftragsnummer"><?=$auftrag->getAuftragsnummer()?></span></p>
		<div class="inputCont">
			<label for="orderTitle">Auftragsbezeichnung:</label>
			<input class="data-input" id="orderTitle" value="<?=$auftrag->getAuftragsbezeichnung()?>" autocomplete="none" onchange="editTitle()">
		</div>
		<div class="inputCont">
			<label for="orderDescription">Auftragsbeschreibung:</label>
			<textarea class="data-input" id="orderDescription" autocomplete="none" onchange="editDescription();" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"><?=$auftrag->getAuftragsbeschreibung()?></textarea>
		</div>
		<div class="inputCont">
			<label for="orderType">Auftragstyp:</label>
			<select class="data-input" id="orderType"  onchange="editOrderType();"><?=$auftrag->getAuftragsbeschreibung()?>
				<?php foreach($auftragsTypen as $type): ?>
				<option value="<?=$type["id"]?>" <?=$auftragsTyp == $type["id"] ? "selected" : "" ?>><?=$type["Auftragstyp"]?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="m-2">
			<p>Auftragseingang: 
				<input class="m-1" type="date" value="<?=$auftrag->getDate()?>" onchange="updateDate(event)">
			</p>
			<p>Termin: 
				<input class="m-1" type="date" value="<?=$auftrag->getDeadline()?>" onchange="updateDeadline(event)" id="inputDeadline">
				<input type="checkbox" onclick="setDeadlineState(event)" <?=$auftrag->getDeadline() == "" ? "checked" : "" ?>> Kein Termin
			</p>
		</div>
		<div>
			<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" onclick="location.href= '<?=Link::getPageLink('rechnung')?>?target=create&id=<?=$auftragsId?>'">Rechnung generieren</button>
			<?php if ($auftrag->getIsArchiviert() == false) :?>
				<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" onclick="archvieren();">Auftrag archivieren</button>
			<?php endif; ?>
			<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" onclick="setOrderFinished()">Auftrag ist fertig</button>
		</div>
	</div>
	<div class="defCont schritte">
		<p class="font-bold">Bearbeitungsschritte und Aufgaben</p>
		<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" onclick="showBearbeitungsschritt()">Neuen Bearbeitungsschritt hinzufügen</button>
		<div class="innerDefCont" id="bearbeitungsschritte" style="display: none">
			<div>
				<p>Bezeichnung:</p>
				<textarea class="bearbeitungsschrittInput m-1 text-slate-600 rounded-lg p-2" type="text" max="128" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea>
			</div>
			<span>Datum: <br><input class="bearbeitungsschrittInput m-1 text-slate-600 rounded-lg p-2" type="date" max="32"></span><br>
			<span>Priorität: <br><input class="bearbeitungsschrittInput" type="range" min="0" max="100" step="1.0"></span><br>
			<form name="isAlreadyDone">
				<input type="radio" name="isDone" value="show" checked>Noch zu erledigen<br>
				<input type="radio" name="isDone" value="hide">Schon erledigt<br>
			</form>
			<input type="checkbox" name="assignTo" onclick="document.getElementById('selectMitarbeiter').disabled = false;">Zuweisen an:</input>
			<br>
			<select id="selectMitarbeiter" disabled>
				<?php foreach ($mitarbeiter as $m): ?>
					<option value="<?=$m['id']?>"><?=$m['Vorname']?> <?=$m['Nachname']?></option>
				<?php endforeach; ?>
			</select>
			<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" onclick="addBearbeitungsschritt()">Hinzufügen</button>
		</div>
		<div>
			<input onchange="radio('hide')" type="radio" name="showDone" value="hide" checked> Zu erledigende Schritte anzeigen<br>
			<input onchange="radio('show')" type="radio" name="showDone" value="show"> Alle Schritte anzeigen
		</div>
		<div id="stepTable">
			<?=$auftrag->getOpenBearbeitungsschritteTable()?>
		</div>
	</div>
	<div class="defCont schritteAdd">
		<p class="font-bold">Notizen</p>
		<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" onclick="addNewNote()">Neue Notiz hinzufügen</button>
		<div class="innerDefCont" id="addNotes" style="display: none">
			<div>
				<p>Notiz:</p>
				<textarea class="noteInput m-1 text-slate-600 rounded-lg p-2" type="text" max="128"></textarea>
			</div>
			<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" onclick="addNote();">Hinzufügen</button>
		</div>
		<div id="noteContainer">
			<?=$auftrag->getNotes()?>
		</div>
	</div>
	<div class="defCont posten">
		<p class="font-bold">Zeiten, Produkte und Kosten (netto)</p>
		<div id="auftragsPostenTable">
			<?=$auftrag->getAuftragspostenAsTable()?>
		</div>
		<div class="buttonDiv">
			<button class="addToTable" onclick="showPostenAdd();">+</button>
		</div>
		<div id="showPostenAdd" style="display: none;">
			<div class="tabcontainer">
				<button class="tablinks activetab" onclick="openTab(event, 0)">Zeiterfassung</button>
				<button class="tablinks" onclick="openTab(event, 1)">Kostenerfassung</button>
				<!--<button class="tablinks" onclick="openTab(event, 2)">Produkt</button>-->
				<button class="tablinks" onclick="openTab(event, 3)">Produkte</button>
			</div>
			<div class="tabcontent" id="tabZeit" style="display: block;">
				<div id="addPostenZeit">
					<div class="container">
						<span>Zeit in Minuten<br><input class="postenInput" id="time" type="number" min="0"></span><br>
						<span>Stundenlohn in €<br><input class="postenInput" id="wage" type="number" value="<?=$auftrag->getDefaultWage()?>"></span><br>
						<span>Beschreibung<br><textarea id="descr" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea></span><br>
						<button id="addTimeButton" onclick="addTime()">Hinzufügen</button>
					</div>
					<div class="container">
						<span>Erweiterte Zeiterfassung:</span>
						<br>
						<span>Arbeitszeit(en)</span>
						<p class="timeInputWrapper">von <input class="timeInput" type="time" min="05:00" max="23:00"> bis <input class="timeInput"  type="time" min="05:00" max="23:00"> am <input class="dateInput" type="date"></p>
						<button class="addToTable" onclick="addTimeInputs(event)">+</button>
						<p id="showTimeSummary"></p>
					</div>
				</div>
			</div>
			<div class="tabcontent" id="tabLeistung">
				<div id="addPostenLeistung">
					<select id="selectLeistung" onchange="selectLeistung(event);">
						<?php foreach ($leistungen as $leistung): ?>
							<option value="<?=$leistung['Nummer']?>" data-aufschlag="<?=$leistung['Aufschlag']?>"><?=$leistung['Bezeichnung']?></option>
						<?php endforeach; ?>
					</select>
					<br>
					<span>Menge:<br><input class="postenInput" id="anz" value="1"></span><br>
					<span>Mengeneinheit:<br>
						<input class="postenInput" id="meh">
						<span id="meh_dropdown">▼</span>
						<div class="selectReplacer" id="selectReplacerMEH">
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Stück</p>
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">m²</p>
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Meter</p>
							<p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Stunden</p>
						</div>
					</span><br>
					<span>Beschreibung:<br><textarea id="bes" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea></span><br>
					<span>Einkaufspreis:<br><input class="postenInput" id="ekp" value="0"></span><br>
					<span>Verkaufspreis:<br><input class="postenInput" id="pre" value="0"></span><br>
					<button onclick="addLeistung()" id="addLeistungButton">Hinzufügen</button>
				</div>		
			</div>
			<div class="tabcontent" id="tabProdukt">
				<div id="addPostenProdukt">
					<span>Menge: <input class="postenInput" id="posten_produkt_menge" type="number"></span>
					<span>Marke: <input class="postenInput" id="posten_produkt_marke" type="text"></span>
					<span>EK-Preis: <input class="postenInput" id="posten_produkt_ek" type="text"></span>
					<span>VK-Preis: <input class="postenInput" id="posten_produkt_vk" type="text"></span>
					<span>Name: <input class="postenInput" id="posten_produkt_name" type="text"></span>
					<span>Beschreibung: <textarea id="posten_produkt_besch" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea></span>
					<button onclick="addProductCompactOld()">Hinzufügen</button>
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
					<button onclick="addProductCompact()">Hinzufügen</button>
					<br>
					<a href="<?=Link::getPageLink("neues-produkt");?>">Neues Produkt hinzufügen</a>
				</div>
			</div>
			<div class="tabcontentEnd">
				<div>
					<span id="showOhneBerechnung">
						<input id="ohneBerechnung" type="checkbox">Ohne Berechnung
					</span>
					<br>
					<span id="showAddToInvoice">
						<input id="addToInvoice" type="checkbox">Der Rechnung hinzufügen
					</span>
					<br>
					<span id="showDiscount">
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
			<span>Fahrzeug hinzufügen</span>
			<br>
			<select id="selectVehicle" onchange="selectVehicle(event);">
				<option value="0" selected disabled>Bitte auswählen</option>
				<?php foreach ($fahrzeuge as $f): ?>
					<option value="<?=$f['Nummer']?>"><?=$f['Kennzeichen']?> <?=$f['Fahrzeug']?></option>
				<?php endforeach; ?>
				<option value="addNew">Neues Fahrzeug hinzufügen</option>
			</select>
			<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" onclick="addFahrzeug(true)">Für diesen Auftrag übernehmen</button>
		</div>
		<div class="innerDefCont" id="addVehicle" style="display: none;">
			<span>Kfz-Kennzeichen:<br><input id="kfz"></span><br>
			<span>Fahrzeug:<br><input id="fahrzeug"></span><br>
			<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" onclick="addFahrzeug()">Neues Fahrzeug hinzufügen</button>
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
		<button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none" onclick="addColor()">Neuen Farbe hinzufügen</button>
	</div>
	<?php if ($show == false): ?>
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
		<p onclick="showAuftragsverlauf();" class="font-bold">Auftragsverlauf anzeigen</p>
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
			<span class="block">Farbbezeichnung: <input class="colorInput" type="text" max="32" placeholder="619 verkehrsgrün"></span>
			<span class="block">Farbtyp: <input class="colorInput" type="text" max="32" placeholder="751C"></span>
			<span class="block">Hersteller: <input class="colorInput" tyep="text" max="32" placeholder="Oracal"></span>
			<span id="hexinputspan">Farbe (Hex): <input class="colorInput jscolor" type="text" max="32" onchange="checkHexCode(this);"></span>
			<button onclick="sendColor();">Hinuzufügen</button>
			<button onclick="toggleCS();">Vorhandene Farbe auswählen</button>
		</div>
		<div class="defCont" id="cpContainer"></div>
		<div class="defCont" id="csContainer" style="display: none">
			<p>Vorhandene Farben:</p>
			<?php foreach ($colors as $color): ?>
				<div class="singleColorContainer" data-colorid=<?=$color['Nummer']?>>
					<p class="singleColorName"><?=$color['Farbe']?> <?=$color['Bezeichnung']?> <?=$color['Hersteller']?></p>
					<div class="farbe" style="background-color: #<?=$color['Farbwert']?>"></div>
				</div>
				<br>
			<?php endforeach; ?>
			<button onclick="addSelectedColors()">Farbe(n) übernehmen</button>
		</div>
	</template>
	<?php endif; ?>
<?php endif; ?>