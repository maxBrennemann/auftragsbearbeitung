<?php 
	require_once('classes/project/Kunde.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Search.php');

	$kundenid = -1;
	$isSearch = false;
	$showList = false;

	if (isset($_GET['showDetails'])) {
		$showDetails = $_GET['showDetails'];
		if ($showDetails == "auftrag") {
			if (isset($_GET['id'])) {
				$id = $_GET['id'];
				header("Location: " . Link::getPageLink('auftrag') . "?id={$id}");
			}
		} else if ($showDetails == "list") {
			$showList = true;
			$showListHTML = "";
			$ids = DBAccess::selectQuery("SELECT Kundennummer FROM kunde ORDER BY CONCAT(Firmenname, Nachname)");
			foreach ($ids as $id) {
				$showListHTML .= (new Kunde($id['Kundennummer']))->getHTMLShortSummary();
			}
		}
	}

	if (isset($_GET['mode']) && $_GET['mode'] == "search" && $_GET['query']) {
		$query = $_GET['query'];
		$searchTable = Search::getSearchTable($query, "kunde", Link::getPageLink("kunde"), true);
		if ($searchTable == "") {
			$link =  Link::getPageLink('neuer-kunde');
			$searchTable = "Es wurden keine Ergebnisse gefunden. <a href=\"$link\">Diesen Kunden erstellen</a>";
		}
		$isSearch = true;
	}

	if (isset($_GET['id'])) {
		$kundenid = $_GET['id'];
		try {
			$kunde = new Kunde($kundenid);
			$table = new FormGenerator("ansprechpartner", "", "");
			$data = DBAccess::selectQuery("SELECT * FROM ansprechpartner WHERE Kundennummer = $kundenid");
			$column_names = array(0 => array("COLUMN_NAME" => "Vorname"), 1 => array("COLUMN_NAME" => "Nachname"), 
							2 => array("COLUMN_NAME" => "Email"), 3 => array("COLUMN_NAME" => "Durchwahl"), 4 => array("COLUMN_NAME" => "Mobiltelefonnummer"));
			$ansprechpartner = $table->createTableByData($data, $column_names);
			if (empty($data)) {
				$ansprechpartner = "";
			}
		} catch (Exception $e){
			echo $e->getMessage();
			$kundenid = -1;
		}
	}
	
	$nextNumber = Kunde::getNextAssignedKdnr($kundenid, 1);
	$linkForward = Link::getPageLink("kunde") . "?id=" . ($nextNumber);
	if ($nextNumber == -1) $linkForward = "#";
	$nextNumber = Kunde::getNextAssignedKdnr($kundenid, -1);
	$linkBackward = Link::getPageLink("kunde") . "?id=" . ($nextNumber);
	if ($nextNumber == -1) $linkBackward = "#";

?>
<p><a href="<?=$linkBackward?>">&#129092;</a><a href="<?=$linkForward?>" id="forwards">&#129094;</a></p>
<?php if ($kundenid == -1 && !$isSearch && !$showList) : ?>
	<p>Kunde kann nicht angezeigt werden.</p>
<?php elseif ($kundenid == -1 && $isSearch) : ?>
	<div class="search">
		<p>Suche: <input value="<?=$_GET['query']?>" id="performSearch" data-url="<?=Link::getPageLink('kunde')?>">
		<span id="lupeSpan"><span id="lupe">&#9906;</span></span></p>
	</div>
	<?=$searchTable?>
<?php elseif ($kundenid == -1 && $showList) : ?>
	<div class="search">
		<p>Suche: <input value="" placeholder="Daten suchen" id="performSearch" data-url="<?=Link::getPageLink('kunde')?>">
		<span id="lupeSpan"><span id="lupe">&#9906;</span></span></p>
	</div>
	<div id="gridShowCustomerList"><?=$showListHTML?></div>
<?php else: ?>
	<h3>Kundendaten</h3>
	<div class="gridCont">
		<div id="showKundendaten">
			<table>
				<tr>
					<th>Kundennummer:</th>
					<td id="kundennummer"><?=$kunde->getKundennummer()?></td>
				</tr>
				<tr>
					<th>Vorname:</th>
					<td class="editable" contenteditable data-col="Vorname"><?=$kunde->getVorname()?></td>
				</tr>
				<tr>
					<th>Nachname:</th>
					<td class="editable" contenteditable data-col="Nachname"><?=$kunde->getNachname()?></td>
				</tr>
				<tr>
					<th>Firmenname:</th>
					<td class="editable" contenteditable data-col="Firmenname"><?=$kunde->getFirmenname()?></td>
				</tr>
				<tr>
					<th>Straße:</th>
					<td class="editable" contenteditable data-col="Straße"><?=$kunde->getStrasse()?></td>
				</tr>
				<tr>
					<th>Hausnummer:</th>
					<td class="editable" contenteditable data-col="Hausnummer"><?=$kunde->getHausnummer()?></td>
				</tr>
				<tr>
					<th>Postleitzahl:</th>
					<td class="editable" contenteditable data-col="Postleitzahl"><?=$kunde->getPostleitzahl()?></td>
				</tr>
				<tr>
					<th>Ort:</th>
					<td class="editable" contenteditable data-col="Ort"><?=$kunde->getOrt()?></td>
				</tr>
				<tr>
					<th>Email:</th>
					<td class="editable" contenteditable data-col="Email"><?=$kunde->getEmail()?></td>
				</tr>
				<tr>
					<th>Telefon Festnetz:</th>
					<td class="editable" contenteditable data-col="TelefonFestnetz"><?=$kunde->getTelefonFestnetz()?></td>
				</tr>
				<tr>
					<th>Telefon Mobil:</th>
					<td class="editable" contenteditable data-col="TelefonMobil"><?=$kunde->getTelefonMobil()?></td>
				</tr>	
			</table>
			<button id="sendKundendaten" disabled onclick="kundendatenAbsenden()">Absenden</button>
		</div>
		<div id="ansprechpartner">
			<h3>Ansprechpartner</h3>
			<div id="ansprechpartnerTable">
				<span id="resetAnsprechpartnerTable">
					<?=$ansprechpartner?>
				</span>
				<table id="addAnsprechpartner" style="display: none;">
					<tr>
						<th>Vorname</th>
						<th>Nachname</th>
						<th>Email</th>
						<th>Durchwahl</th>
						<th>Mobilnummer</th>
					</tr>
					<tr>
						<td class="ansprTableCont" contenteditable="true" data-col="vorname"></td>
						<td class="ansprTableCont" contenteditable="true" data-col="nachname"></td>
						<td class="ansprTableCont" contenteditable="true" data-col="email"></td>
						<td class="ansprTableCont" contenteditable="true" data-col="durchwahl"></td>
						<td class="ansprTableCont" contenteditable="true" data-col="handynummer"></td>
					</tr>
				</table>
			</div>
			<button id="showAddAnsprechpartner" onclick="showAddAnsprechpartner()">Ausklappen </button>
			<button id="addAnsprechpartnerBtn" onclick="addDataToDB()" style="display: none;">Hinzufügen</button>
		</div>
		<div id="farben">
			<h3>Farben</h3>
			<div id="showFarben"><?=$kunde->getFarben()?></div>
		</div>
		<div id="auftraege">
			<h3>Aufträge</h3>
			<?=$kunde->getAuftraege()?>
			<a href="<?=Link::getPageLink("neuer-auftrag")?>?kdnr=<?=$kundenid?>">Neuen Auftrag erstellen</a>
		</div>
		<div id="notizen">
			<h3>Notizen</h3>
			<div id="editNotes"><?=$kunde->getNotizen()?></div>
			<button onclick="editText(event);">Bearbeiten</button>
		</div>
		<div id="fahrzeuge">
			<h3>Fahrzeuge</h3>
			<?=$kunde->getFahrzeuge()?>
		</div>
	</div>
<?php endif; ?>