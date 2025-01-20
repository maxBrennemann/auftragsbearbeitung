<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use Classes\Link;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;
use Classes\Login;

/**
 * Klasse generiert im Zusammenhang mit der Template Datei auftrag.php die Übersicht für einen bestimmten Auftrag.
 * Dabei werden alle Auftragsposten und alle Bearbeitungsschritte aus der Datenbank geladen und als Objekte erstellt.
 * Diese können bearbeitet, ergänzt und abgearbeitet werden.
 */
class Auftrag implements StatisticsInterface
{

	protected $Auftragsnummer = null;
	protected $Auftragsbezeichnung = null;
	protected $Auftragsbeschreibung = null;
	protected $Auftragsposten = [];
	protected $Bearbeitungsschritte = [];
	protected $auftragstyp = null;
	protected $rechnungsnummer = 0;

	protected $isPayed = false;

	/* dates */
	public $datum;
	public $termin;
	public $fertigstellung;

	private $isArchiviert = false;
	private $isRechnung = false;

	function __construct($auftragsnummer)
	{
		$auftragsnummer = (int) $auftragsnummer;
		if ($auftragsnummer > 0) {
			$this->Auftragsnummer = $auftragsnummer;
			$data = DBAccess::selectAllByCondition("auftrag", "Auftragsnummer", $auftragsnummer);
			$data = $data[0];

			if (!empty($data)) {
				$this->Auftragsbeschreibung = $data['Auftragsbeschreibung'];
				$this->Auftragsbezeichnung = $data['Auftragsbezeichnung'];
				$this->auftragstyp = (int) $data['Auftragstyp'];
				$this->rechnungsnummer = $data['Rechnungsnummer'];

				$this->datum = $data['Datum'];
				$this->termin = $data['Termin'];
				$this->fertigstellung = $data['Fertigstellung'];

				$this->isPayed = $data['Bezahlt'] == 1 ? true : false;

				if ($data['archiviert'] == 0 || $data['archiviert'] == "0") {
					$this->isArchiviert = true;
				}

				$data = DBAccess::selectQuery("SELECT * FROM schritte WHERE Auftragsnummer = {$auftragsnummer}");
				foreach ($data as $step) {
					$element = new Step($step['Auftragsnummer'], $step['Schrittnummer'], $step['Bezeichnung'], $step['Datum'], $step['Priority'], $step['istErledigt']);
					array_push($this->Bearbeitungsschritte, $element);
				}

				$this->Auftragsposten = Posten::bekommeAllePosten($auftragsnummer);
			} else {
				throw new \Exception("Auftragsnummer " . $auftragsnummer . " existiert nicht oder kann nicht gefunden werden<br>");
			}
		}
	}

	public function getContactPersons(): array
	{
		$query = "SELECT ap.Nummer AS id, ap.Vorname AS firstName, ap.Nachname AS lastName, ap.Email AS email, 
				CASE
					WHEN a.Ansprechpartner = ap.Nummer THEN 1
					ELSE 0
				END AS isSelected
			FROM ansprechpartner ap 
				JOIN kunde k ON ap.Kundennummer = k.Kundennummer
				JOIN auftrag a ON a.Kundennummer = k.Kundennummer
			WHERE a.Auftragsnummer = :id;";
		$data = DBAccess::selectQuery($query, [
			"id" => $this->Auftragsnummer,
		]);

		return $data;
	}

	public function getBearbeitungsschritte()
	{
		$htmlData = "";
		foreach ($this->Bearbeitungsschritte as $schritt) {
			$htmlData .= $schritt->getHTMLData();
		}
		return $htmlData;
	}

	public function getBearbeitungsschritteAsTable()
	{
		$query = "SELECT Schrittnummer, Bezeichnung, Datum, `Priority`, finishingDate FROM schritte WHERE Auftragsnummer = :id ORDER BY `Priority` DESC";
		$data = DBAccess::selectQuery($query, ["id" => $this->Auftragsnummer]);

		$column_names = array(
			0 => array("COLUMN_NAME" => "Bezeichnung"),
			1 => array("COLUMN_NAME" => "Datum"),
			2 => array("COLUMN_NAME" => "Priority", "ALT" => "Priorotät"),
			3 => array("COLUMN_NAME" => "finishingDate", "ALT" => "erledigt am"),
		);

		for ($i = 0; $i < sizeof($data); $i++) {
			$data[$i]["Priority"] = Priority::getPriorityLevel($data[$i]["Priority"]);
		}

		/* addes three buttons to table */
		$t = new Table();
		$t->createByData($data, $column_names);
		$t->addActionButton("edit");
		$t->setType("schritte");
		$t->addActionButton("delete", "Schrittnummer");

		$_SESSION["schritte_table"] = serialize($t);

		return $t->getTable();
	}

	/* getBearbeitungsschritte with new Table class */
	public function getOpenBearbeitungsschritteTable(): string
	{
		$query = "SELECT Schrittnummer, Bezeichnung, Datum, `Priority` FROM schritte WHERE Auftragsnummer = :id AND istErledigt = 1 ORDER BY `Priority` DESC";
		$data = DBAccess::selectQuery($query, ["id" => $this->Auftragsnummer]);

		$column_names = array(
			0 => array("COLUMN_NAME" => "Bezeichnung"),
			1 => array("COLUMN_NAME" => "Datum"),
			2 => array("COLUMN_NAME" => "Priority", "ALT" => "Priorotät")
		);

		for ($i = 0; $i < sizeof($data); $i++) {
			$data[$i]["Priority"] = Priority::getPriorityLevel($data[$i]["Priority"]);
		}

		/* addes three buttons to table */
		$t = new Table();
		$t->createByData($data, $column_names);
		$t->addActionButton("update", "Schrittnummer", $update = "istErledigt = 0");
		$t->addActionButton("edit");
		$t->setType("schritte");
		$t->addActionButton("delete", "Schrittnummer");

		$_SESSION["schritte_table"] = serialize($t);

		return $t->getTable();
	}

	public function getAuftragsbeschreibung()
	{
		return $this->Auftragsbeschreibung;
	}

	public function getAuftragsnummer(): int
	{
		return $this->Auftragsnummer;
	}

	public function getIsPayed()
	{
		return $this->isPayed;
	}

	public function getPaymentDate()
	{
		if (!$this->getIsPayed()) {
			return "";
		}

		$query = "SELECT payment_date FROM invoice WHERE order_id = :orderId";
		$data = DBAccess::selectQuery($query, ["orderId" => $this->Auftragsnummer]);

		if (empty($data)) {
			return "";
		}

		return $data[0]["payment_date"];
	}

	public function getPaymentType()
	{
		if (!$this->getIsPayed()) {
			return "";
		}

		$query = "SELECT payment_type FROM invoice WHERE order_id = :orderId";
		$data = DBAccess::selectQuery($query, ["orderId" => $this->Auftragsnummer]);

		if (empty($data)) {
			return "";
		}

		return $data[0]["payment_type"];
	}

	public function getAuftragsposten()
	{
		$htmlData = "";
		foreach ($this->Auftragsposten as $posten) {
			$htmlData .= $posten->getHTMLData();
		}
		return $htmlData;
	}

	public function getAuftragspostenData()
	{
		return $this->Auftragsposten;
	}

	public function getAuftragstyp(): int
	{
		return $this->auftragstyp;
	}

	public function getAuftragstypBezeichnung()
	{
		$query = "SELECT `Auftragstyp` FROM `auftragstyp` WHERE `id` = :idAuftragstyp LIMIT 1;";
		$bez = DBAccess::selectQuery($query, ["idAuftragstyp" => $this->auftragstyp]);

		if ($bez != null) {
			return $bez[0]["Auftragstyp"];
		} else {
			return "";
		}
	}

	public static function getAllOrderTypes()
	{
		$query = "SELECT * FROM `auftragstyp`;";
		$result = DBAccess::selectQuery($query);
		return $result;
	}

	public function getAuftragsbezeichnung()
	{
		return $this->Auftragsbezeichnung;
	}

	public function getDate()
	{
		return $this->datum;
	}

	public function getDeadline()
	{
		return $this->termin;
	}

	/**
	 * calculates the sum of all items in the order
	 */
	public function calcOrderSum()
	{
		$price = 0;
		foreach ($this->Auftragsposten as $posten) {
			if ($posten->isInvoice() == 1) {
				$price += $posten->bekommePreis();
			}
		}
		return $price;
	}

	public function preisBerechnen()
	{
		$price = 0;
		foreach ($this->Auftragsposten as $posten) {
			$price += $posten->bekommePreis();
		}
		return $price;
	}

	public function gewinnBerechnen()
	{
		$price = 0;
		foreach ($this->Auftragsposten as $posten) {
			$price += $posten->bekommeDifferenz();
		}
		return $price;
	}

	public function getKundennummer()
	{
		return DBAccess::selectQuery("SELECT Kundennummer FROM auftrag WHERE auftragsnummer = {$this->Auftragsnummer}")[0]['Kundennummer'];
	}

	/*
	 * helper function for creating the AuftragsPosten table
	 * function returns the data for the table
	 */
	private function getAuftragsPostenHelper($isInvoice = false): array|string
	{
		$subArr = array(
			"Postennummer" => "",
			"Bezeichnung" => "",
			"Beschreibung" => "",
			"Stundenlohn" => "",
			"MEH" => "",
			"Preis" => "",
			"Gesamtpreis" => "",
			"Anzahl" => "",
			"Einkaufspreis" => "",
			"type" => ""
		);

		$data = array();
		if (sizeof($this->Auftragsposten) == 0) {
			return "";
		}

		/* only collect the items where isInvoice is true */
		if ($isInvoice) {
			for ($i = 0; $i < sizeof($this->Auftragsposten); $i++) {
				if ($this->Auftragsposten[$i]->isInvoice() == true) {
					array_push($data, $this->Auftragsposten[$i]->fillToArray($subArr));
				}
			}

			return $data;
		}

		/* check if ClientSettings::getFilterOrderPosten is set */
		if (ClientSettings::getFilterOrderPosten()) {
			for ($i = 0; $i < sizeof($this->Auftragsposten); $i++) {
				if ($this->Auftragsposten[$i]->isInvoice() == false) {
					array_push($data, $this->Auftragsposten[$i]->fillToArray($subArr));
				}
			}

			return $data;
		}

		for ($i = 0; $i < sizeof($this->Auftragsposten); $i++) {
			array_push($data, $this->Auftragsposten[$i]->fillToArray($subArr));
		}

		return $data;
	}

	public function getAuftragspostenAsTable()
	{
		$column_names = array(
			0 => array("COLUMN_NAME" => "Bezeichnung"),
			1 => array("COLUMN_NAME" => "Beschreibung"),
			2 => array("COLUMN_NAME" => "Stundenlohn"),
			3 => array("COLUMN_NAME" => "Anzahl"),
			4 => array("COLUMN_NAME" => "MEH"),
			5 => array("COLUMN_NAME" => "Preis"),
			6 => array("COLUMN_NAME" => "Gesamtpreis"),
			7 => array("COLUMN_NAME" => "Einkaufspreis")
		);

		$data = $this->getAuftragsPostenHelper();

		/* addes edit and delete to table */
		$t = new Table();
		$t->createByData($data, $column_names);
		$t->addActionButton("edit");
		$t->setType("posten");
		$t->addActionButton("delete", "Postennummer");
		$t->addAction(null, Icon::getDefault("iconAdd"), "Rechnung/ Zahlung hinzufügen");
		$t->addActionButton("move");
		$t->addDataset("type", "type");
		$_SESSION["posten_table"] = serialize($t);
		$_SESSION[$t->getTableKey()] = serialize($t);

		return $t->getTable();
	}

	public static function getOrderItems() {
		$id = Tools::get("id");
		$order = new Auftrag($id);
		$data = $order->getAuftragsPostenHelper();

		JSONResponseHandler::sendResponse($data);
	}

	/*
	 * returns all invoice columns from invoice_posten table
	 */
	public function getInvoicePostenTable()
	{
		$column_names = array(
			0 => array("COLUMN_NAME" => "Menge"),
			1 => array("COLUMN_NAME" => "MEH"),
			2 => array("COLUMN_NAME" => "Bezeichnung"),
			3 => array("COLUMN_NAME" => "E-Preis"),
			4 => array("COLUMN_NAME" => "G-Preis"),
		);

		$data = array();

		/* only collect the items where isInvoice is true */
		for ($i = 0; $i < sizeof($this->Auftragsposten); $i++) {
			if ($this->Auftragsposten[$i]->isInvoice() == true) {
				$p = $this->Auftragsposten[$i];
				$subArr = array(
					"Menge" => $p->getQuantity(),
					"MEH" => $p->getEinheit(),
					"Bezeichnung" => $p->getDescription(),
					"E-Preis" => $p->bekommeEinzelPreis_formatted(),
					"G-Preis" => $p->bekommePreis_formatted()
				);
				array_push($data, $subArr);
			}
		}

		/* addes edit and delete to table */
		$t = new Table();
		$t->createByData($data, $column_names);

		return $t->getTable();
	}

	public function getIsArchiviert()
	{
		return $this->isArchiviert;
	}

	/* 
	 * this function returns all orders which are marked as ready to finish;
	 * an order is ready when its "archived" column is set to -1
	 */
	public static function getReadyOrders()
	{
		$query = "SELECT Auftragsnummer, IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) as Kunde, Auftragsbezeichnung FROM auftrag, kunde WHERE archiviert = -1 AND kunde.Kundennummer = auftrag.Kundennummer AND Rechnungsnummer = 0";
		$data = DBAccess::selectQuery($query);

		$column_names = array(
			0 => array("COLUMN_NAME" => "Auftragsnummer"),
			1 => array("COLUMN_NAME" => "Kunde"),
			2 => array("COLUMN_NAME" => "Auftragsbezeichnung")
		);

		$link = new Link();
		$link->addBaseLink("auftrag");
		$link->setIterator("id", $data, "Auftragsnummer");

		$t = new Table();
		$t->createByData($data, $column_names);
		$t->addLink($link);
		return $t->getTable();
	}

	public static function getAuftragsliste()
	{
		$column_names = array(
			0 => array("COLUMN_NAME" => "Auftragsnummer", "ALT" => "Nr.", "NOWRAP"),
			1 => array("COLUMN_NAME" => "Datum", "NOWRAP" => true),
			2 => array("COLUMN_NAME" => "Termin", "NOWRAP" => true),
			3 => array("COLUMN_NAME" => "Kunde"),
			4 => array("COLUMN_NAME" => "Auftragsbezeichnung")
		);

		$query = "SELECT Auftragsnummer, DATE_FORMAT(Datum, '%d.%m.%Y') as Datum, IF(kunde.Firmenname = '', 
				CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) as Kunde, 
				Auftragsbezeichnung, IF(auftrag.Termin IS NULL OR auftrag.Termin = '0000-00-00', 'kein Termin', DATE_FORMAT(auftrag.Termin, '%d.%m.%Y')) AS Termin 
			FROM auftrag 
			LEFT JOIN kunde 
				ON auftrag.Kundennummer = kunde.Kundennummer 
			WHERE Rechnungsnummer = 0 AND archiviert != 0";

		$data = DBAccess::selectQuery($query);

		$link = new Link();
		$link->addBaseLink("auftrag");
		$link->setIterator("id", $data, "Auftragsnummer");

		$t = new Table();
		$t->createByData($data, $column_names);
		$t->addLink($link);
		return $t->getTable();
	}

	public function istRechnungGestellt()
	{
		return $this->rechnungsnummer == 0 ? false : true;
	}

	public function getRechnungsnummer()
	{
		return $this->rechnungsnummer;
	}

	public function getLinkedVehicles()
	{
		return DBAccess::selectQuery("SELECT Nummer, Kennzeichen, Fahrzeug FROM fahrzeuge LEFT JOIN fahrzeuge_auftraege ON fahrzeuge_auftraege.id_fahrzeug = Nummer WHERE fahrzeuge_auftraege.id_auftrag = {$this->getAuftragsnummer()}");
	}

	public function getFahrzeuge()
	{
		$fahrzeuge = $this->getLinkedVehicles();
		$column_names = array(0 => array("COLUMN_NAME" => "Nummer"), 1 => array("COLUMN_NAME" => "Kennzeichen"), 2 => array("COLUMN_NAME" => "Fahrzeug"));

		$link = new Link();
		$link->addBaseLink("fahrzeug");
		$link->setIterator("id", $fahrzeuge, "Nummer");

		$t = new Table();
		$t->createByData($fahrzeuge, $column_names);
		$t->addLink($link);
		$t->addAction("addFileVehicle", "+", "Datei hinzufügen");
		$_SESSION[$t->getTableKey()] = serialize($t);
		return $t->getTable();
	}

	public function getColors()
	{
		$query = "SELECT color_name, hex_value, id, producer, short_name 
			FROM color, color_auftrag 
			WHERE id_color = id 
				AND id_auftrag = :orderId";

		$colors = DBAccess::selectQuery($query, [
			"orderId" => $this->getAuftragsnummer()
		]);

		ob_start();
		insertTemplate('files/res/views/colorView.php', [
			"colors" => $colors,
		]);

		$content = ob_get_clean();
		return $content;
	}

	/*
	 * creates a div card with the order details
	*/
	public function getOrderCardData()
	{
		$data = DBAccess::selectQuery("SELECT Datum, Termin, Fertigstellung FROM auftrag WHERE Auftragsnummer = :orderId", [
			"orderId" => $this->getAuftragsnummer(),
		]);
		$data = $data[0];

		$date = $data['Datum'];
		$deadline = $data['Termin'];
		$finished = $data['Fertigstellung'];

		return [
			"id" => $this->Auftragsnummer,
			"archived" => $this->isArchiviert,
			"orderTitle" => $this->Auftragsbezeichnung,
			"orderDescription" => $this->Auftragsbeschreibung,
			"date" => $date,
			"deadline" => $deadline,
			"finished" => $finished,
			"invoice" => $this->rechnungsnummer,
			"summe" => $this->rechnungsnummer != 0 ? $this->preisBerechnen() : 0,
		];
	}

	public static function getNotes()
	{
		$orderId = (int) Tools::get("orderId");

		$notes = DBAccess::selectQuery("SELECT id, note, title, creation_date as `date` FROM notes WHERE orderId = :id ORDER BY creation_date DESC", [
			"id" => $orderId,
		]);

		foreach ($notes as $key => $note) {
			if ($notes[$key]["date"] == date("Y-m-d")) {
				$notes[$key]["date"] = "Heute";
			} else if ($notes[$key]["date"] == date("Y-m-d", strtotime("-1 day"))) {
				$notes[$key]["date"] = "Gestern";
			} else {
				$notes[$key]["date"] = date("d.m.Y", strtotime($note["date"]));
			}
		}

		return JSONResponseHandler::sendResponse([
			"data" => $notes,
		]);
	}

	public function recalculate() {}

	public function archiveOrder()
	{
		$query = "UPDATE auftrag SET archiviert = 0 WHERE Auftragsnummer = {$this->Auftragsnummer}";
		DBAccess::updateQuery($query);
	}

	public function rearchiveOrder()
	{
		$query = "UPDATE auftrag SET archiviert = 1 WHERE Auftragsnummer = :orderId";
		DBAccess::updateQuery($query, [
			"orderId" => $this->Auftragsnummer
		]);
	}

	/*
	 * adds a list to the order
	 * lists can be edited
	*/
	public function addList($listId)
	{
		DBAccess::insertQuery("INSERT INTO auftrag_liste (auftrags_id, listen_id) VALUES ({$this->Auftragsnummer}, $listId)");
	}

	/*
	 * removes a list, later the saved list data has to be removed as well
	 * TODO: implement data deletion
	*/
	public function removeList($listId)
	{
		DBAccess::deleteQuery("DELETE FROM auftrag_liste WHERE auftrags_id = {$this->Auftragsnummer} AND listen_id = $listId");
	}

	public function getListIds()
	{
		return DBAccess::selectQuery("SELECT listen_id FROM auftrag_liste WHERE auftrags_id = {$this->Auftragsnummer}");
	}

	public function showAttachedLists()
	{
		$listenIds = self::getListIds();
		$html = "";
		foreach ($listenIds as $id) {
			$html .= (Liste::readList($id['listen_id']))->toHTML($this->Auftragsnummer);
		}
		return $html;
	}

	public function getDefaultWage()
	{
		$defaultWage = GlobalSettings::getSetting("defaultWage");
		return $defaultWage;
	}

	/**
	 * adds a new order to the database by using the data from the form,
	 * which is sent by the client;
	 * the function echos a json object with the response link and the order id
	 */
	public static function add()
	{
		$bezeichnung = $_POST['bezeichnung'];
		$beschreibung = $_POST['beschreibung'];
		$typ = $_POST['typ'];
		$termin = Tools::get("termin");
		$angenommenVon = $_POST['angenommenVon'];
		$kdnr = $_POST['customerId'];
		$angenommenPer = $_POST['angenommenPer'];
		$ansprechpartner = (int) $_POST['ansprechpartner'];

		$orderId = self::addToDB($kdnr, $bezeichnung, $beschreibung, $typ, $termin, $angenommenVon, $angenommenPer, $ansprechpartner);

		$isLoadPosten = false;
		if (isset($_SESSION['offer_is_order']) && $_SESSION['offer_is_order'] == true) {
			$isLoadPosten = true;
		}

		$data = array(
			"success" => true,
			"responseLink" => Link::getPageLink("auftrag") . "?id=$orderId",
			"loadFromOffer" => $isLoadPosten,
			"orderId" => $orderId
		);

		NotificationManager::addNotification(Login::getUserId(), 4, "Auftrag <a href=" . $data["responseLink"] . ">$orderId</a> wurde angelegt", $orderId);
		$auftragsverlauf = new Auftragsverlauf($orderId);
		$auftragsverlauf->addToHistory($orderId, 5, "added", "Neuer Auftrag");
		echo json_encode($data, JSON_FORCE_OBJECT);
	}

	/**
	 * adds a new order to the database
	 */
	private static function addToDB($kdnr, $bezeichnung, $beschreibung, $typ, $termin, $angenommenVon, $angenommenPer, $ansprechpartner)
	{
		$date = date("Y-m-d");
		$query = "INSERT INTO auftrag (Kundennummer, Auftragsbezeichnung, Auftragsbeschreibung, Auftragstyp, Datum, Termin, AngenommenDurch, AngenommenPer, Ansprechpartner) VALUES (:kdnr, :bezeichnung, :beschreibung, :typ, :datum, :termin, :angenommenVon, :angenommenPer, :ansprechpartner);";
		$parameters = [
			":kdnr" => $kdnr,
			":bezeichnung" => $bezeichnung,
			":beschreibung" => $beschreibung,
			":typ" => $typ,
			":datum" => $date,
			":termin" => $termin,
			":angenommenVon" => $angenommenVon,
			":angenommenPer" => $angenommenPer,
			":ansprechpartner" => $ansprechpartner
		];
		$orderId = DBAccess::insertQuery($query, $parameters);
		return $orderId;
	}

	public static function getFiles($auftragsnummer)
	{
		$files = DBAccess::selectQuery("SELECT DISTINCT dateiname AS Datei, originalname, `date` AS Datum, typ as Typ FROM dateien LEFT JOIN dateien_auftraege ON dateien_auftraege.id_datei = dateien.id WHERE dateien_auftraege.id_auftrag = $auftragsnummer");

		for ($i = 0; $i < sizeof($files); $i++) {
			$link = Link::getResourcesShortLink($files[$i]['Datei'], "upload");

			$filePath = "upload/" . $files[$i]['Datei'];
			/*
             * checks at first if the image exists
             * then checks if it is an image with exif_imagetype function,
             * suppresses with @ the notice and then checks if getimagesize
             * returns a value
             */
			if (file_exists($filePath) && (@exif_imagetype($filePath) != false) && getimagesize($filePath) != false) {
				$html = "<a target=\"_blank\" rel=\"noopener noreferrer\" href=\"$link\"><img class=\"img_prev_i\" src=\"$link\" width=\"40px\"><p class=\"img_prev\">{$files[$i]['originalname']}</p></a>";
			} else {
				$html = "<span><a target=\"_blank\" rel=\"noopener noreferrer\" href=\"$link\">{$files[$i]['originalname']}</a></span>";
			}

			$files[$i]['Datei'] = $html;
		}

		$column_names = array(
			0 => array("COLUMN_NAME" => "Datei"),
			1 => array("COLUMN_NAME" => "Typ"),
			2 => array("COLUMN_NAME" => "Datum")
		);

		$t = new Table();
		$t->createByData($files, $column_names);
		$t->setType("dateien");
		$t->addActionButton("delete", $identifier = "id");

		return $t->getTable();
	}

	public static function deleteOrder()
	{
		$id = (int) Tools::get("id");
		$query = "DELETE FROM auftrag WHERE Auftragsnummer = :id;";
		DBAccess::deleteQuery($query, ["id" => $id]);

		if (DBAccess::getAffectedRows() == 0) {
			JSONResponseHandler::throwError(404, "Auftrag existiert nicht");
		}

		JSONResponseHandler::sendResponse([
			"success" => true,
			"home" => Link::getPageLink(""),
		]);
	}

	public static function setOrderArchived()
	{
		$id = (int) Tools::get("id");
		$auftrag = new Auftrag($id);
		$auftrag->archiveOrder();

		JSONResponseHandler::sendResponse([
			"success" => true,
			"home" => Link::getPageLink(""),
		]);
	}

	public function addNewNote()
	{
		$title = (string) Tools::get("title");
		$note = (string) Tools::get("note");

		$historyId = DBAccess::insertQuery("INSERT INTO notes (orderId, title, note) VALUES (:orderId, :title, :note)", [
			"orderId" => $this->Auftragsnummer,
			"title" => $title,
			"note" => $note,
		]);

		$auftragsverlauf = new Auftragsverlauf($this->Auftragsnummer);
		$auftragsverlauf->addToHistory($historyId, 7, "added", $note);

		JSONResponseHandler::sendResponse([
			"success" => true,
			"date" => date("d.m.Y"),
		]);
	}

	public static function addNote()
	{
		$orderId = (int) Tools::get("orderId");
		$order = new Auftrag($orderId);
		$order->addNewNote();
	}

	public static function updateNote()
	{
		$id = (int) Tools::get("id");
		$type = (string) Tools::get("type");
		$data = (string) Tools::get("data");

		DBAccess::updateQuery("UPDATE notes SET $type = :data WHERE id = :id", [
			"id" => $id,
			"data" => $data,
		]);

		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}

	public static function deleteNote()
	{
		$note = (int) Tools::get("id");
		DBAccess::deleteQuery("DELETE FROM notes WHERE id = :note", [
			"note" => $note,
		]);

		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}

	public static function updateOrderType()
	{
		$idOrderType = (int) Tools::get("type");
		$idOrder = (int) Tools::get("id");

		$query = "UPDATE `auftrag` SET `Auftragstyp` = :idOrderType WHERE `Auftragsnummer` = :idOrder";
		DBAccess::updateQuery($query, [
			"idOrder" => $idOrder,
			"idOrderType" => $idOrderType,
		]);

		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}

	public static function updateOrderTitle()
	{
		$orderTitle = (string) Tools::get("title");
		$idOrder = (int) Tools::get("id");

		$query = "UPDATE auftrag SET Auftragsbezeichnung = :title WHERE Auftragsnummer = :idOrder";
		DBAccess::updateQuery($query, [
			"idOrder" => $idOrder,
			"title" => $orderTitle,
		]);

		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}

	public static function updateContactPerson()
	{
		$idContact = (string) Tools::get("idContact");
		$idOrder = (int) Tools::get("id");

		$query = "UPDATE auftrag SET Ansprechpartner = :idContact WHERE Auftragsnummer = :idOrder";
		DBAccess::updateQuery($query, [
			"idOrder" => $idOrder,
			"idContact" => $idContact,
		]);
		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}

	public static function updateDate()
	{
		$order = (int) Tools::get("id");
		$date =  Tools::get("date");
		$type = (int)  Tools::get("type");

		$types = [
			1 => "Datum",
			2 => "Termin",
			3 => "Fertigstellung"
		];

		if (!isset($types[$type])) {
			JSONResponseHandler::throwError(400, "Type does not exist");
		}

		$type = $types[$type];

		if ($date == "unset") {
			DBAccess::updateQuery("UPDATE auftrag SET $type = NULL WHERE Auftragsnummer = :order;", [
				"order" => $order,
			]);
		} else {
			DBAccess::updateQuery("UPDATE auftrag SET $type = :setDate WHERE Auftragsnummer = :order;", [
				"setDate" => $date,
				"order" => $order,
			]);
		}

		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}

	public static function addColors()
	{
		$id = (int) Tools::get("id");
		$colors = Tools::get("colors");
		$colors = json_decode($colors, false);

		$query = "INSERT INTO color_auftrag (id_auftrag, id_color) VALUES ";
		$data = [];

		foreach ($colors as $colorId) {
			$data[] = [$id, (int) $colorId];
		}

		DBAccess::insertMultiple($query, $data);

		$order = new Auftrag($id);
		$response = $order->getColors();
		JSONResponseHandler::sendResponse([
			"colors" => $response,
		]);
	}

	public static function addColor()
	{
		$orderId = (int) Tools::get("id");
		$colorName = (string) Tools::get("colorName");
		$hexValue = (string) Tools::get("hexValue");
		$shortName = (string) Tools::get("shortName");
		$producer = (string) Tools::get("producer");

		$color = new Color($colorName, $hexValue, $shortName, $producer);
		$colorId = $color->save();

		DBAccess::insertQuery("INSERT INTO color_auftrag (id_color, id_auftrag) VALUES (:colorId, :orderId)", [
			"colorId" => $colorId,
			"orderId" => $orderId,
		]);

		$order = new Auftrag($orderId);
		$response = $order->getColors();
		JSONResponseHandler::sendResponse([
			"colors" => $response,
		]);
	}

	public static function deleteColor()
	{
		$orderId = (int) Tools::get("id");
		$colorId = (int) Tools::get("colorId");

		DBAccess::deleteQuery("DELETE FROM color_auftrag WHERE id_color = :colorId AND id_auftrag = :orderId", [
			"colorId" => $colorId,
			"orderId" => $orderId,
		]);

		$order = new Auftrag($orderId);
		$response = $order->getColors();
		JSONResponseHandler::sendResponse([
			"colors" => $response,
		]);
	}

	public static function itemsOverview()
	{
		$auftragsId = Tools::get("id");
		$auftrag = new Auftrag($auftragsId);

		$data = [
			0 => $auftrag->getAuftragspostenAsTable(),
			1 => $auftrag->getInvoicePostenTable()
		];

		JSONResponseHandler::sendResponse([
			"data" => $data,
		]);
	}

	public static function resetAnsprechpartner($data)
	{
		$customerId = Tools::get("customerId");
		$query = "UPDATE auftrag SET Ansprechpartner = 0 WHERE Kundennummer = :customerId AND Ansprechpartner = :contactPerson;";

		DBAccess::updateQuery($query, [
			"customerId" => $customerId,
			"contactPerson" => $data["Nummer"],
		]);
	}

}
