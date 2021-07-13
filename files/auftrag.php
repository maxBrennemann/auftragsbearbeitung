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
		<?=$kunde->getVorname()?> <?=$kunde->getNachname()?><br>
		<?=$kunde->getFirmenname()?><br>
		Adresse: <br>
		<?=$kunde->getStrasse()?> <?=$kunde->getHausnummer()?><br>
		<?=$kunde->getPostleitzahl()?> <?=$kunde->getOrt()?><br>
		<a href="mailto:<?=$kunde->getEmail()?>"><?=$kunde->getEmail()?></a><br>
		<?php if ($ansprechpartner != -1): ?>Ansprechpartner: <?=$ansprechpartner['Vorname']?> <?=$ansprechpartner['Nachname']?><button class="actionButton" onclick="changeContact()">✎</button><?php endif;?><br>
		<a href="<?=Link::getPageLink("kunde")?>?id=<?=$auftrag->getKundennummer()?>">Kunde <span id="kundennummer"><?=$auftrag->getKundennummer()?></span> zeigen</a>
	</aside>
	<div class="defCont auftragsinfo">
		<span><u>Auftragsnummer:</u> <span id="auftragsnummer"><?=$auftrag->getAuftragsnummer()?></span></span>
		<br>
		<span><button onclick="showPreview();">Auftragsblatt anzeigen</button></span>
		<span><button onclick="location.href= '<?=Link::getPageLink('rechnung')?>?create=<?=$auftragsId?>'">Rechnung generieren</button></span>
		<?php if ($auftrag->getIsArchiviert() == false) :?><span><button onclick="archvieren();">Auftrag archivieren</button></span><br><?php endif; ?>
		<br>
		<span>
			<u>Beschreibung:</u>
			<br>
			<p id="orderDescription">
				<?=$auftrag->getAuftragsbeschreibung()?>
			</p>
			<button onclick="editDescription(event);">Bearbeiten</button>
		</span>
		<br>
		Auftragsstellung: <?=$auftrag->datum?><br>
		Termin: <?=$auftrag->termin?>
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
			<span>Bezeichnung: <br><input class="bearbeitungsschrittInput" type="text" max="128"></span><br>
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
	</div>
	<div class="defCont posten">
		<u>Posten:</u><br><span id="auftragsPostenTable"><?=$auftrag->getAuftragspostenAsTable()?></span>
	</div>
	<div class="defCont preis">
		<u>Gesamtpreis:</u>
		<br>
		<span id="gesamtpreis">
			<?=number_format($auftrag->preisBerechnen(), 2, ',', '') . "€"?>
		</span>
		<span>
			<?=number_format($auftrag->gewinnBerechnen(), 2, ',', '') . "€"?> (Gewinn)
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
		<form class="fileUploader" data-target="vehicle" name="vehicle">
			<input type="file">
		</form>
		<?php endif; ?>
	</div>
	<div class="defCont farben">
		<span><u>Farben:</u><br> <span id="showColors"><?=$farbTable?></span></span>
		<button onclick="addColor()">Neuen Farbe hinzufügen</button>
		<div class="defCont" id="farbe" style="display: none">
			<div class="innerDefCont">
				<span>Farbname: <input class="colorInput" type="text" max="32"></span>
				<span id="hexinputspan">Farbe (Hex): <input class="colorInput jscolor" type="text" max="32" onchange="checkHexCode(this);"></span>
				<span>Bezeichnung: <input class="colorInput" type="text" max="32"></span>
				<span>Hersteller: <input class="colorInput" tyep="text" max="32"></span>
				<button onclick="sendColor();">Hinuzufügen</button>
			</div>
		</div>
		<script>var cp = new Colorpicker(document.getElementById("farbe"));</script>
	</div>
	<div class="defCont postenadd" id="newPosten">
		<select id="selectPosten">
			<option value="zeit">Zeit</option>
			<option value="leistung">Leistung</option>
			<option value="produkt">Produkt</option>
		</select>
		<button onclick="getSelections()">Posten hinzufügen</button>
		<div id="addPosten">
			<div class="innerDefCont" id="addPostenZeit" style="display: none">
				<span>Zeit in Minuten<br><input id="time" type="number" min="0"></span><br>
				<span>Stundenlohn in €<br><input id="wage" type="number" value="44"></span><br>
				<span>Beschreibung<br><input id="descr" type="text"></span><br>
				<button onclick="addTime()">Hinzufügen</button>
			</div>
			<div class="innerDefCont" id="addPostenLeistung" style="display: none">
				<div class="columnLeistung">
					<select id="selectLeistung" onchange="selectLeistung(event);">
						<?php foreach ($leistungen as $leistung): ?>
							<option value="<?=$leistung['Nummer']?>" data-aufschlag="<?=$leistung['Aufschlag']?>"><?=$leistung['Bezeichnung']?></option>
						<?php endforeach; ?>
					</select>
					<br>
					<span>Beschreibung:<br><input id="bes"></span><br>
					<span>Einkaufspreis:<br><input id="ekp" value="0"></span><br>
					<span>Speziefischer Preis:<br><input id="pre" value="0"></span><br>
					<span>Anzahl:<br><input id="anz" value="1"></span><br>
					<span>Mengeneinheit:<br><input id="meh"></span><br>
					<button onclick="addLeistung()">Hinzufügen</button>
				</div>
			</div>
			<div class="innerDefCont" id="addPostenProdukt" style="display: none">
				<span>Menge: <input id="posten_produkt_menge" type="number"></span>
				<span>Marke: <input id="posten_produkt_marke" type="text"></span>
				<span>EK-Preis: <input id="posten_produkt_ek" type="text"></span>
				<span>VK-Preis: <input id="posten_produkt_vk" type="text"></span>
				<span>Name: <input id="posten_produkt_name" type="text"></span>
				<span>Beschreibung: <input id="posten_produkt_besch" type="text"></span>
				<button onclick="addProductCompact()">Hinzufügen</button>
			</div>
			<span id="showOhneBerechnung" style="display: none;"><input id="ohneBerechnung" type="checkbox">Ohne Berechnung</span>
			<br>
			<span id="showDiscount" style="display: none;"><input type="range" min="0" max="100" value="0" oninput="event.target.nextSibling.innerText = this.value + '%';"><span id="showDiscoundValue">0%</span> Rabatt</span>
		</div>
		<div id="generalPosten"></div>
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
		<button onclick="addList();">Liste hinzufügen</button>
		<div class="defCont" id="listauswahl" style="display: none;">
			<?=$showLists?>
		</div>
	</div>
	<div class="liste">
		<?=$showAttachedLists?>
	</div>
	<?php endif; ?>
<?php endif; ?>