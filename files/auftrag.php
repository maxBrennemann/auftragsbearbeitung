<?php
	require_once('classes/project/Auftrag.php');
	require_once('classes/project/Rechnung.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Auftragsverlauf.php');
	require_once('classes/project/Fahrzeug.php');
	require_once('classes/project/Kunde.php');
	require_once('classes/DBAccess.php');
	require_once('classes/Upload.php');
	require_once('classes/project/Liste.php');

	$auftragsId = -1;
	$auftragAnzeigen = Link::getPageLink("auftrag");
	$show = false;
	if (isset($_GET['id'])) {
		$auftragsId = $_GET['id'];
		try {
			$auftrag = new Auftrag($auftragsId);
			$fahrzeugTable = $auftrag->getFahrzeuge();
			$farbTable = $auftrag->getFarben();
			$kunde = new Kunde($auftrag->getKundennummer());

			/* Parameter werden nur gebraucht, falls der Auftrag existiert */
			$auftragstyp = $auftrag->getAuftragstyp();
			if ($auftragstyp == 0) {
				$fahrzeuge = Fahrzeug::getSelection($auftrag->getKundennummer());
				$fahrzeugeAuftrag = $auftrag != null ? $auftrag->getLinkedVehicles() : null;
			}
			
			$leistungen = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");
			$showFiles = Upload::getFilesAuftrag($auftragsId);
			$auftragsverlauf = (new Auftragsverlauf($auftragsId))->representHistoryAsHTML();
			$showLists = Liste::chooseList();
		} catch (Exception $e) {
			echo $e->getMessage();
			$auftragsId = -1;
		}
	}

	if (isset($_POST['filesubmitbtn'])) {
		$upload = new Upload();
		$upload->uploadFilesAuftrag($auftragsId);
	}

	if (isset($_POST['filesubmitbtnV'])) {
		$vehicleId = $_POST['vehicleImageId'];
		echo $vehicleId;
		$upload = new Upload();
		$upload->uploadFilesVehicle($vehicleId, $auftragsId);
	}

	if (isset($_GET['create'])) {
		$nextId = Rechnung::getNextNumber();
		$auftragsid = $_GET['create'];
		echo "Rechnung $nextId wird erstellt";
		DBAccess::updateQuery("UPDATE auftrag SET Rechnungsnummer = $nextId WHERE Auftragsnummer = $auftragsid");
		$tempAuftrag = new Auftrag($auftragsid);
		$tempAuftrag->recalculate();
	}

	/* Paremter wird gebraucht, falls Rechnung gestellt wurde, aber der Auftrag trotzdem gezeigt werden soll */
	if (isset($_GET['show'])) {
		$show = true;
	}

if ($auftragsId == -1) : ?>
	<input type="number" min="1" oninput="document.getElementById('auftragsLink').href = '<?=$auftragAnzeigen?>?id=' + this.value;">
	<a href="#" id="auftragsLink">Auftrag anzeigen</a>
<?php elseif ($auftrag->istRechnungGestellt() && $show == false) : ?>
	<p>Auftrag <?=$auftrag->getAuftragsnummer()?> wurde abgeschlossen. Rechnungsnummer: <span id="rechnungsnummer"><?=$auftrag->getRechnungsnummer()?></span></p>
	<button onclick="print('rechnungsnummer', 'Rechnung');">Rechnungsblatt anzeigen</button>
	<button onclick="showAuftrag()">Auftrag anzeigen</button>
<?php else: ?>
	<aside class="defCont">
		<?=$kunde->getVorname()?> <?=$kunde->getNachname()?><br><?=$kunde->getFirmenname()?><br>Adresse: <br><?=$kunde->getStrasse()?> <?=$kunde->getHausnummer()?><br>
		<?=$kunde->getPostleitzahl()?> <?=$kunde->getOrt()?><br><a href="mailto:<?=$kunde->getEmail()?>"><?=$kunde->getEmail()?></a><br>
		<a href="<?=Link::getPageLink("kunde")?>?id=<?=$auftrag->getKundennummer()?>">Kunde <span id="kundennummer"><?=$auftrag->getKundennummer()?></span> zeigen</a>
	</aside>
	<div class="defCont auftragsinfo">
		<span><u>Auftragsnummer:</u> <span id="auftragsnummer"><?=$auftrag->getAuftragsnummer()?></span></span><br>
		<span><button onclick="print('auftragsnummer', 'Auftrag');">Auftragsblatt anzeigen</button></span>
		<span><button onclick="rechnungErstellen();">Rechnung generieren</button></span>
		<span><button onclick="archvieren();">Auftrag archivieren</button></span><br>
		<span><u>Beschreibung:</u><br><?=$auftrag->getAuftragsbeschreibung()?></span><br>
	</div>
	<div class="defCont schritte">
		<span><u>Schritte:</u><br>
			<form name="showSteps">
				<input onchange="radio('hide')" type="radio" name="showDone" value="hide" checked> Zu erledigende Schritte anzeigen<br>
				<input onchange="radio('show')" type="radio" name="showDone" value="show"> Alle Schritte anzeigen<br>
			</form>
			<span id="stepTable"><?=$auftrag->getOpenBearbeitungsschritteAsTable()?></span>
		</span>
	</div>
	<div class="defCont schritteAdd">
		<button onclick="addBearbeitungsschritte()">Neuen Bearbeitungsschritt hinzufügen</button>
		<div class="innerDefCont" id="bearbeitungsschritte" style="display: none">
			<span>Bezeichnung: <br><input class="bearbeitungsschrittInput" type="text" max="128"></span><br>
			<span>Datum: <br><input class="bearbeitungsschrittInput" type="date" max="32"></span><br>
			<form name="isAlreadyDone">
				<input onchange="radio('hide')" type="radio" name="showDone" value="hide" checked>Noch zu erledigen<br>
				<input onchange="radio('show')" type="radio" name="showDone" value="show">Schon erledigt<br>
			</form>
		</div>
	</div>
	<div class="defCont posten">
		<span><u>Posten:</u><br><span id="auftragsPostenTable"><?=$auftrag->getAuftragspostenAsTable()?></span></span>
	</div>
	<div class="defCont preis">
		<span><u>Gesamtpreis:</u><br><span id="gesamtpreis"><?=$auftrag->preisBerechnen()?>€</span></span>
	</div>
	<div class="defCont fahrzeuge">
		<?php if ($auftragstyp == 0): ?>
		<span><u>Fahrzeuge:</u> <span id="fahrzeugTable"><?=$fahrzeugTable?></span></span><br>
		<div>
			<p>
				<form method="post" enctype="multipart/form-data">
					<label>Fahrzeug:
						<select name="vehicleImageId">
							<?php foreach ($fahrzeugeAuftrag as $f): ?>
								<option value="<?=$f['Nummer']?>"><?=$f['Kennzeichen']?> <?=$f['Fahrzeug']?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<br>
					<label>
						<input type="file" name="uploadedFile">
						<input type="submit" value="Datei hochladen" name="filesubmitbtnV">
					</label>
				</form>
			</p>
		</div>
		<?php endif; ?>
	</div>
	<div class="defCont farben">
		<span><u>Farben:</u><br> <span id="showColors"><?=$farbTable?></span></span>
		<button onclick="addColor()">Neuen Farbe hinzufügen</button>
		<div class="defCont" id="farbe" style="display: none">
			<div class="innerDefCont">
				<span>Farbname: <input class="colorInput" type="text" max="32"></span>
				<span>Farbe (Hex): <input class="colorInput jscolor" type="text" max="32"></span>
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
			<div id="addPostenZeit" style="display: none">
				<span><input id="time" type="number" min="0">Zeit in Minuten</span><br>
				<span><input id="wage" type="number" value="44">Stundenlohn in €</span>
				<span><input id="descr" type="text">Beschreibung</span>
				<button onclick="addTime()">Hinzufügen</button>
			</div>
			<div id="addPostenLeistung" style="display: none">
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
					<button onclick="addLeistung()">Hinzufügen</button>
				</div>
				<div class="columnLeistung" id="addKfz" style="display: none;">
					<span>Kfz-Kennzeichen:<br><input id="kfz"></span><br>
					<span>Fahrzeug:<br><input id="fahrzeug"></span><br>
					<button onclick="addFahrzeug()">Neues Fahrzeug hinzufügen</button>
					<hr>
					<select id="selectVehicle" onchange="selectVehicle(event);">
						<option value="0" selected disabled>Bitte auswählen</option>
						<?php foreach ($fahrzeuge as $f): ?>
							<option value="<?=$f['Nummer']?>"><?=$f['Kennzeichen']?> <?=$f['Fahrzeug']?></option>
						<?php endforeach; ?>
					</select>
					<button onclick="addFahrzeug(true)">Für diesen Auftrag übernehmen</button>
				</div>
			</div>
			<span id="showOhneBerechnung" style="display: none;"><input id="ohneBerechnung" type="checkbox">Ohne Berechnung</span>
		</div>
		<div id="generalPosten"></div>
	</div>
	<div class="defCont produkt">
		<div id="selectProdukt">
			<span>Produkt suchen: <input type="text"><button onclick="performSearch(event)">&#x1F50E;</button></span>
		</div>
	</div>
	<?php if ($show == false): ?>
	<div class="defCont step"></div>
	<div class="defCont upload">
		<form method="post" enctype="multipart/form-data">
			Dateien zum Auftrag hinzufügen:
			<input type="file" name="uploadedFile">
			<input type="submit" value="Datei hochladen" name="filesubmitbtn">
		</form>
		<br>
		<div>
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
	<div class="liste"></div>
	<?php endif; ?>
<?php endif; ?>