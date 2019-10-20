<?php
	require_once('classes/project/Auftrag.php');
	require_once('classes/project/Rechnung.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Kunde.php');
	require_once('classes/DBAccess.php');

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
		} catch (Exception $e) {
			echo $e->getMessage();
			$auftragsId = -1;
		}
	}

	if (isset($_GET['create'])) {
		$nextId = Rechnung::getNextNumber();
		$auftragsid = $_GET['create'];
		echo "Rechnung $nextId wird erstellt";
		DBAccess::updateQuery("UPDATE auftrag SET Rechnungsnummer = $nextId WHERE Auftragsnummer = $auftragsid");
	}

	/* Paremter wird gebraucht, falls Rechnung gestellt wurde, aber der Auftrag trotzdem gezeigt werden soll */
	if (isset($_GET['show'])) {
		$show = true;
	}

	$leistungen = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");

if ($auftragsId == -1) : ?>
	<input type="number" min="1" oninput="document.getElementById('auftragsLink').href = '<?=$auftragAnzeigen?>?id=' + this.value;">
	<a href="#" id="auftragsLink">Auftrag anzeigen</a>
<?php elseif ($auftrag->istRechnungGestellt() && $show == false) : ?>
	<p>Auftrag <?=$auftrag->getAuftragsnummer()?> wurde abgeschlossen. Rechnungsnummer: <span id="rechnungsnummer"><?=$auftrag->getRechnungsnummer()?></span></p>
	<button onclick="print('rechnungsnummer', 'Rechnung');">Rechnungsblatt anzeigen</button>
	<button onclick="showAuftrag()">Auftrag anzeigen</button>
<?php else: ?>
	<aside class="border">
		<?=$kunde->getVorname()?> <?=$kunde->getNachname()?><br><?=$kunde->getFirmenname()?><br>Adresse: <br><?=$kunde->getStrasse()?> <?=$kunde->getHausnummer()?><br>
		<?=$kunde->getPostleitzahl()?> <?=$kunde->getOrt()?><br><a href="mailto:<?=$kunde->getEmail()?>"><?=$kunde->getEmail()?></a><br>
		<a href="<?=Link::getPageLink("kunde")?>?id=<?=$auftrag->getKundennummer()?>">Kunde <span id="kundennummer"><?=$auftrag->getKundennummer()?></span> zeigen</a>
	</aside>
	<div class="border auftragsinfo">
		<span><u>Auftragsnummer:</u> <span id="auftragsnummer"><?=$auftrag->getAuftragsnummer()?></span></span><br>
		<span><button onclick="print('auftragsnummer', 'Auftrag');">Auftragsblatt anzeigen</button></span>
		<span><button onclick="rechnungErstellen();">Rechnung generieren</button></span><br>
		<span><u>Beschreibung:</u><br><?=$auftrag->getAuftragsbeschreibung()?></span><br>
	</div>
	<div class="border schritte">
		<span><u>Schritte:</u><br>
			<form name="showSteps">
				<input onchange="radio('hide')" type="radio" name="showDone" value="hide" checked> Zu erledigende Schritte anzeigen<br>
				<input onchange="radio('show')" type="radio" name="showDone" value="show"> Alle Schritte anzeigen<br>
			</form>
			<span id="stepTable"><?=$auftrag->getOpenBearbeitungsschritteAsTable()?></span>
		</span>
	</div>
	<div class="border posten">
		<span><u>Posten:</u> <?=$auftrag->getAuftragspostenAsTable()?></span>
	</div>
	<div class="border preis">
		<span><u>Gesamtpreis:</u> <?=$auftrag->preisBerechnen()?>€</span>
	</div>
	<div class="border fahrzeuge">
		<span><u>Fahrzeuge:</u> <?=$fahrzeugTable?></span>
	</div>
	<div class="border farben">
		<span><u>Farben:</u> <?=$farbTable?></span>
	</div>
	<div class="border postenadd" id="newPosten">
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
				<select id="selectLeistung" onchange="selectLeistung(event);">
					<?php foreach ($leistungen as $leistung): ?>
						<option value="<?=$leistung['Nummer']?>" data-aufschlag="<?=$leistung['Aufschlag']?>"><?=$leistung['Bezeichnung']?></option>
					<?php endforeach; ?>
				</select>
				<br>
				<span><input id="bes"> Beschreibung</span><br>
				<span><input id="ekp" value="0"> Einkaufspreis</span><br>
				<span><input id="pre" value="0"> Speziefischer Preis</span><br>
				<button onclick="addLeistung()">Hinzufügen</button>
				<br>
				<div id="addKfz" style="display: none;">
					<span><input id="kfz"> Kfz-Kennzeichen</span><br>
					<span><input id="fahrzeug"> Fahrzeug</span><br>
					<button onclick="addFahrzeug()">Fahrzeug hinzufügen</button>
				</div>
			</div>
		</div>
		<div id="generalPosten"></div>
	</div>
	<div class="border produkt">
		<div id="selectProdukt">
			<span>Produkt suchen: <input type="text"><button onclick="performSearch()">&#x1F50E;</button></span>
			<div id="searchResults"></div>
		</div>
	</div>
	<?php if ($show == false): ?>
	<div class="border step">
		<button onclick="addBearbeitungsschritte()">Neuen Bearbeitungsschritt hinzufügen</button>
		<div id="bearbeitungsschritte"></div>
		<button onclick="addColor()">Neuen Farbe hinzufügen</button>
		<div id="farbe" style="display: none">
			<br>
			<span>Farbname: <input class="colorInput" type="text" max="32"></span><br>
			<span>Farbe (Hex): <input class="colorInput jscolor" type="text" max="32"></span><br>
			<span>Bezeichnung: <input class="colorInput" type="text" max="32"></span><br>
			<span>Hersteller: <input class="colorInput" tyep="text" max="32"></span><br>
		</div>
	</div>
	<?php endif; ?>
<?php endif; ?>