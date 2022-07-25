<?php
require_once('classes/project/Auftrag.php');
require_once('classes/project/Search.php');
require_once('classes/project/Rechnung.php');
require_once('classes/project/FormGenerator.php');
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

	if (isset($_GET['id'])) {
		$auftragsId = (int) $_GET['id'];
		if ($auftragsId > 0) {
			$auftrag = new Auftrag($auftragsId);
			$fahrzeugTable = $auftrag->getFahrzeuge();
			$farbTable = $auftrag->getFarben();
			$kunde = new Kunde($auftrag->getKundennummer());

			/* Parameter werden nur gebraucht, falls der Auftrag existiert */
			if ($auftrag != null) {
				$auftragstyp = $auftrag->getAuftragstyp();
				if ($auftragstyp == 0) {
					$fahrzeuge = Fahrzeug::getSelection($auftrag->getKundennummer());
					$fahrzeugeAuftrag = $auftrag != null ? $auftrag->getLinkedVehicles() : null;
				}
				
				$leistungen = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");
				$showFiles = Upload::getFilesAuftrag($auftragsId);
				$auftragsverlauf = (new Auftragsverlauf($auftragsId))->representHistoryAsHTML();
				$showLists = Liste::chooseList();
				$showAttachedLists = $auftrag->showAttachedLists();
				$ansprechpartner = $auftrag->bekommeAnsprechpartner();
			}
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
	<p>Auftrag <?=$auftrag->getAuftragsnummer()?> wurde abgeschlossen. Rechnungsnummer: <span id="rechnungsnummer"><?=$auftrag->getRechnungsnummer()?></span></p>
	<button onclick="print('rechnungsnummer', 'Rechnung');">Rechnungsblatt anzeigen</button>
	<button onclick="showAuftrag()">Auftrag anzeigen</button>
	<?php
		$invoiceLink = $auftrag->getKundennummer() . "_" . $auftrag->getRechnungsnummer() . ".pdf";
		$invoiceLink = Link::getResourcesShortLink($invoiceLink, "pdf");
	?>
	<a href="<?=$invoiceLink?>">Zur Rechnung</a>
<?php else: ?>
	<aside class="defCont">
		<p><?=$kunde->getVorname()?> <?=$kunde->getNachname()?></p>
		<p><?=$kunde->getFirmenname()?></p>
		<u>Adresse:</u>
		<p><?=$kunde->getStrasse()?> <?=$kunde->getHausnummer()?></p>
		<p><?=$kunde->getPostleitzahl()?> <?=$kunde->getOrt()?></p>
		<p><?=$kunde->getTelefonFestnetz()?></p>
		<p><?=$kunde->getTelefonMobil()?></p>
		<p><a href="mailto:<?=$kunde->getEmail()?>"><?=$kunde->getEmail()?></a></p>
		<p id="showAnspr"><?php if ($ansprechpartner != -1): ?>Ansprechpartner: <?=$ansprechpartner['Vorname']?> <?=$ansprechpartner['Nachname']?><?php endif;?></p>
		<span>Ansprechpartner ändern<button class="actionButton" onclick="changeContact()">✎</button></span>
		<br>
		<a href="<?=Link::getPageLink("kunde")?>?id=<?=$auftrag->getKundennummer()?>">Kunde <span id="kundennummer"><?=$auftrag->getKundennummer()?></span> zeigen</a>
	</aside>
	<div class="defCont auftragsinfo">
		<p><u>Auftrag:</u> <span id="auftragsnummer"><?=$auftrag->getAuftragsnummer()?></span></p>
		<div class="inputCont">
			<label for="orderTitle">Auftragsbezeichnung:</label>
			<input class="data-input" id="orderTitle" value="<?=$auftrag->getAuftragsbezeichnung()?>" autocomplete="none" onchange="editTitle()">
		</div>
		<div class="inputCont">
			<label for="orderDescription">Auftragsbeschreibung:</label>
			<textarea class="data-input" id="orderDescription" autocomplete="none" onchange="editDescription();" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"><?=$auftrag->getAuftragsbeschreibung()?></textarea>
		</div>
		<br>
		<span><button onclick="showPreview();">Auftragsblatt anzeigen</button></span>
		<span><button onclick="location.href= '<?=Link::getPageLink('rechnung')?>?target=create&id=<?=$auftragsId?>'">Rechnung generieren</button></span>
		<?php if ($auftrag->getIsArchiviert() == false) :?><span><button onclick="archvieren();">Auftrag archivieren</button></span><br><?php endif; ?>
		Auftragsstellung: <span id="changeDate-1"><?=$auftrag->datum?></span><button class="actionButton" onclick="changeDate(1, event)">✎</button><br>
		Termin: <span id="changeDate-2"><?=$auftrag->termin?></span><button class="actionButton" onclick="changeDate(2, event)">✎</button>
		<br>
	</div>
	<div class="defCont schritte">
		<div><u>Schritte und Notizen:</u><br>
			<form name="showSteps">
				<input onchange="radio('hide')" type="radio" name="showDone" value="hide" checked> Zu erledigende Schritte anzeigen<br>
				<input onchange="radio('show')" type="radio" name="showDone" value="show"> Alle Schritte anzeigen<br>
			</form>
			<div id="stepTable">
				<?=$auftrag->getOpenBearbeitungsschritteTable()?>
			</div>
		</div>
		<div id="noteContainer">
			<?=$auftrag->getNotes()?>
		</div>
	</div>
	<div class="defCont schritteAdd">
		<button onclick="addBearbeitungsschritte()">Neuen Bearbeitungsschritt hinzufügen</button>
		<div class="innerDefCont" id="bearbeitungsschritte" style="display: none">
			<span>Bezeichnung: <br><textarea class="bearbeitungsschrittInput" type="text" max="128" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea></span><br>
			<span>Datum: <br><input class="bearbeitungsschrittInput" type="date" max="32"></span><br>
			<span>Priorität: <br><input class="bearbeitungsschrittInput" type="range" min="0" max="100" step="1.0"></span><br>
			<form name="isAlreadyDone">
				<input type="radio" name="isDone" value="show" checked>Noch zu erledigen<br>
				<input type="radio" name="isDone" value="hide">Schon erledigt<br>
			</form>
			<hr>
			<input type="checkbox" name="assignTo" onclick="document.getElementById('selectMitarbeiter').disabled = false;">Zuweisen an:</input>
			<br>
			<select id="selectMitarbeiter" disabled>
				<?php foreach ($mitarbeiter as $m): ?>
					<option value="<?=$m['id']?>"><?=$m['Vorname']?> <?=$m['Nachname']?></option>
				<?php endforeach; ?>
			</select>
			<br>
		</div>
		<button onclick="document.getElementById('addNotes').style.display='block';">Neue Notiz hinzufügen</button>
		<div class="innerDefCont" id="addNotes" style="display: none">
			<span>Notiz: <br><input class="noteInput" type="text" max="128"></span><br>
			<button onclick="addNote();">Hinzufügen</button>
		</div>
		<button onclick="setOrderFinished()">Auftrag ist fertig</button>
	</div>
	<div class="defCont posten">
		<u>Zeiten, Produkte und Kosten (netto):</u>
		<br>
		<br>
		<span id="auftragsPostenTable">
			<?=$auftrag->getAuftragspostenAsTable()?>
		</span>
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
		<u>Rechnungsposten (netto):</u>
		<br><br>
		<div id="invoicePostenTable"><?=$auftrag->getInvoicePostenTable()?></div>
	</div>
	<div class="defCont preis">
		<u>Gesamtpreis (netto):</u>
		<br>
		<span id="gesamtpreis">
			<?=number_format($auftrag->preisBerechnen(), 2, ',', '') . "€"?>
		</span>
		<span>
			<?=number_format($auftrag->gewinnBerechnen(), 2, ',', '') . "€"?> (Gewinn netto)
		</span>
	</div>
	<div class="defCont fahrzeuge">
		<?php if ($auftragstyp == 0): ?>
		<span><u>Fahrzeuge:</u><button class="infoButton" data-info="1">i</button><br>
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
			<button onclick="addFahrzeug(true)">Für diesen Auftrag übernehmen</button>
		</div>
		<div class="innerDefCont" id="addVehicle" style="display: none;">
			<span>Kfz-Kennzeichen:<br><input id="kfz"></span><br>
			<span>Fahrzeug:<br><input id="fahrzeug"></span><br>
			<button onclick="addFahrzeug()">Neues Fahrzeug hinzufügen</button>
		</div>
		<div><?=$auftrag->getFahrzeuge();?></div>
		<br>
		<form class="fileUploader" data-target="vehicle" name="vehicle" method="post" enctype="multipart/form-data" id="fileVehicle" style="display: none">
			<input type="file" name="uploadedFile" multiple>
			<input name="orderid" value="<?=$auftragsId?>" hidden>
		</form>
		<?php endif; ?>
	</div>
	<div class="defCont farben">
		<span><u>Farben:</u><br> <span id="showColors"><?=$farbTable?></span></span>
		<button onclick="addColor()">Neuen Farbe hinzufügen</button>
		<div class="defCont" id="farbe" style="display: none">
			<div class="innerDefCont">
				<span>Farbbezeichnung: <input class="colorInput" type="text" max="32" placeholder="619 verkehrsgrün"></span>
				<span>Farbtyp: <input class="colorInput" type="text" max="32" placeholder="751C"></span>
				<span>Hersteller: <input class="colorInput" tyep="text" max="32" placeholder="Oracal"></span>
				<span id="hexinputspan">Farbe (Hex): <input class="colorInput jscolor" type="text" max="32" onchange="checkHexCode(this);"></span>
				<button onclick="sendColor();">Hinuzufügen</button>
				<button onclick="toggleCS();">Vorhandene Farbe auswählen</button>
				<button onclick="toggleCP();">Farbe über Colorpicker auswählen</button>
			</div>
			<div class="innerDefCont" id="cpContainer" style="display: none"></div>
			<div class="innerDefCont" id="csContainer" style="display: none">
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
		</div>
		<script>var cp = new Colorpicker(document.getElementById("cpContainer"));</script>
	</div>
	<div class="defCont postenadd" id="newPosten">
	</div>
	<div class="defCont produkt">
		<div id="selectProdukt">
			<span>Produkt suchen: <input type="text"><button onclick="performSearch(event)">&#x1F50E;</button></span>
		</div>
	</div>
	<?php if ($show == false): ?>
	<div class="defCont upload">
		<div>
			<form class="fileUploader" method="post" enctype="multipart/form-data" data-target="order" name="auftragUpload">
				Dateien zum Auftrag hinzufügen:
				<input type="file" name="uploadedFile" multiple>
				<input name="auftrag" value="<?=$auftragsId?>" hidden>
			</form>
			<div class="filesList defCont"></div>
		</div>
		<br>
		<div id="showFilePrev">
			<?=$showFiles?>
		</div>
	</div>
	<div class="defCont verlauf">
		<p onclick="showAuftragsverlauf();">Auftragsverlauf anzeigen</p>
		<?=$auftragsverlauf?>
		<br>
		<button onclick="addList();">Liste hinzufügen</button><button class="infoButton" data-info="2">i</button>
		<div class="defCont" id="listauswahl" style="display: none;">
			<?=$showLists?>
		</div>
	</div>
	<div class="liste">
		<?=$showAttachedLists?>
	</div>
	<?php endif; ?>
<?php endif; ?>