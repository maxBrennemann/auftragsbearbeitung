<?php 
	require_once('classes/project/Kunde.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Table.php');
	require_once('classes/project/Search.php');
	require_once('classes/project/Adress.php');

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

			$data = DBAccess::selectQuery("SELECT * FROM ansprechpartner WHERE Kundennummer = $kundenid");
			$column_names = array(0 => array("COLUMN_NAME" => "Vorname"), 1 => array("COLUMN_NAME" => "Nachname"), 2 => array("COLUMN_NAME" => "Email"), 3 => array("COLUMN_NAME" => "Durchwahl"), 4 => array("COLUMN_NAME" => "Mobiltelefonnummer"));

			/* create ansprechpartner table */
			$t = new Table();
			$t->createByData($data, $column_names);
			$t->addActionButton("edit");
			$t->addNewLineButton();
			
			$pattern = [
				"Kundennummer" => [
					"status" => "preset",
					"value" => $kundenid
				],
				"Vorname" => [
					"status" => "unset",
					"value" => 0
				],
				"Nachname" => [
					"status" => "unset",
					"value" => 1
				],
				"Email" => [
					"status" => "unset",
					"value" => 2
				],
				"Durchwahl" => [
					"status" => "unset",
					"value" => 3
				],
				"Mobiltelefonnummer" => [
					"status" => "unset",
					"value" => 4
				]
			];

			$t->defineUpdateSchedule(new UpdateSchedule("ansprechpartner", $pattern));
			$ansprechpartner = $t->getTable(true);
			
			$_SESSION[$t->getTableKey()] = serialize($t);
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
			<div class="row">
				<div class="width12">
					<div class="inputCont">
						<label for="kdnr">Kundennummer:</label>
						<input disabled class="data-input" id="kdnr" value="<?=$kunde->getKundennummer()?>">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="width6">
					<div class="inputCont">
						<label for="vorname">Vorname:</label>
						<input class="data-input" id="vorname" value="<?=$kunde->getVorname()?>">
					</div>
				</div>
				<div class="width6">
					<div>
						<label for="nachname">Nachname:</label>
						<input class="data-input" id="nachname" value="<?=$kunde->getNachname()?>">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="width12">
					<div class="inputCont">
						<label for="firmenname">Firmenname:</label>
						<input class="data-input" id="firmenname" value="<?=$kunde->getFirmenname()?>">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="width6">
					<div class="inputCont">
						<label for="strasse">Straße:</label>
						<input class="data-input" id="strasse" value="<?=$kunde->getStrasse()?>">
					</div>
				</div>
				<div class="width6">
					<div>
						<label for="hausnr">Hausnummer:</label>
						<input class="data-input" id="hausnr" value="<?=$kunde->getHausnummer()?>">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="width6">
					<div class="inputCont">
						<label for="plz">Postleitzahl:</label>
						<input class="data-input" id="plz" value="<?=$kunde->getPostleitzahl()?>">
					</div>
				</div>
				<div class="width6">
					<div>
						<label for="ort">Ort:</label>
						<input class="data-input" id="ort" value="<?=$kunde->getOrt()?>">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="width12">
					<div class="inputCont">
						<label for="email">Email:</label>
						<input class="data-input" id="email" value="<?=$kunde->getEmail()?>">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="width6">
					<div class="inputCont">
						<label for="festnezt">Telefon Festnetz:</label>
						<input class="data-input" id="festnezt" value="<?=$kunde->getTelefonFestnetz()?>">
					</div>
				</div>
				<div class="width6">
					<div>
						<label for="mobil">Telefon Mobil:</label>
						<input class="data-input" id="mobil" value="<?=$kunde->getTelefonMobil()?>">
					</div>
				</div>
			</div>
			<button id="sendKundendaten" disabled onclick="kundendatenAbsenden()">Absenden</button>
			<button onclick="showAdressForm();">Neue Adresse hinzufügen</button>
			<div id="adressForm" style="display: none">
				<?=Adress::getAdressForm();?>
			</div>
		</div>

		<div id="ansprechpartner">
			<h3>Ansprechpartner</h3>
			<div id="ansprechpartnerTable">
				<?=$ansprechpartner?>
			</div>
		</div>
		<div id="farben">
			<h3>Farben</h3>
			<div id="showFarben"><?=$kunde->getFarben()?></div>
		</div>
		<div id="auftraege">
			<h3>Aufträge</h3>
			<?=$kunde->getOrderCards()?>
			<br>
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