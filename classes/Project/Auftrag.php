<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

use Classes\Link;
use Classes\Notification\NotifiableEntity;
use Classes\Notification\NotificationManager;

class Auftrag implements StatisticsInterface, NotifiableEntity
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

	private int $customerId = 0;

	public const IS_ACTIVE = 0;
	public const IS_ARCHIVED = 1;
	public const IS_FINISHED = -1;

	public function __construct(int $orderId)
	{
		if ($orderId <= 0) {
			return;
		}

		$this->Auftragsnummer = $orderId;
		$data = DBAccess::selectAllByCondition("auftrag", "Auftragsnummer", $orderId);
		$data = $data[0] ?? [];

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

			$data = DBAccess::selectQuery("SELECT * FROM schritte WHERE Auftragsnummer = {$orderId}");
			foreach ($data as $step) {
				$element = new Step($step['Auftragsnummer'], $step['Schrittnummer'], $step['Bezeichnung'], $step['Datum'], $step['Priority'], $step['istErledigt']);
				array_push($this->Bearbeitungsschritte, $element);
			}

			$this->Auftragsposten = Posten::getOrderItems($orderId);
			$this->customerId = (int) DBAccess::selectQuery("SELECT Kundennummer FROM auftrag WHERE auftragsnummer = :orderId", [
				"orderId" => $orderId,
			])[0]['Kundennummer'];
		} else {
			throw new \Exception("Auftragsnummer $orderId existiert nicht oder kann nicht gefunden werden.");
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
		if ($this->termin == "0000-00-00" || $this->termin == null) {
			return "";
		}
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

	public function getKundennummer(): int
	{
		return $this->customerId;
	}

	public static function getOrderItems()
	{
		$id = (int) Tools::get("id");
		$type = ClientSettings::getFilterOrderPosten() == true ? "invoice" : "";
		$data = Posten::getOrderItems($id, $type);

		$parsedData = [];
		foreach ($data as $key => $value) {
			$item = [];
			$item["type"] = "posten";

			if ($value instanceof Zeit) {
				$item["type"] = "time";
			} else if ($value instanceof Leistung) {
				$item["type"] = "service";
			}

			$item["position"] = $value->getPosition();
			$item["price"] = $value->bekommeEinzelPreis();
			$item["totalPrice"] = $value->bekommePreis();

			$value = $value->fillToArray([]);
			$item["id"] = $value["Postennummer"];
			$item["name"] = $value["Bezeichnung"];
			$item["description"] = $value["Beschreibung"];
			$item["quantity"] = $value["Anzahl"];
			$item["price"] = $value["Preis"];
			$item["unit"] = $value["MEH"];
			$item["totalPrice"] = $value["Gesamtpreis"];
			$item["purchasePrice"] = $value["Einkaufspreis"];

			$parsedData[] = $item;
		}

		JSONResponseHandler::sendResponse($parsedData);
	}

	public static function getOrderItem(int $id) {}

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

	public static function getInvoicePostenTableAjax()
	{
		$orderId = Tools::get("id");
		$order = new Auftrag($orderId);

		JSONResponseHandler::sendResponse([
			"invoicePostenTable" => $order->getInvoicePostenTable(),
		]);
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

	public function getOrderCardData(): array
	{
		$query = "SELECT DATE_FORMAT(Datum, '%d.%m.%Y') AS Datum,
				DATE_FORMAT(Termin, '%d.%m.%Y') AS Termin, 
				DATE_FORMAT(Fertigstellung , '%d.%m.%Y') AS Fertigstellung 
			FROM auftrag 
			WHERE Auftragsnummer = :orderId";
		$data = DBAccess::selectQuery($query, [
			"orderId" => $this->getAuftragsnummer(),
		]);

		$date = $data[0]["Datum"] == "00.00.0000" ? "-" : $data[0]["Datum"];
		$deadline = $data[0]["Termin"] == "00.00.0000" ? "-" : $data[0]["Termin"];
		$finished = $data[0]["Fertigstellung"] == "00.00.0000" ? "-" : $data[0]["Fertigstellung"];

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

		JSONResponseHandler::sendResponse($notes);
	}

	public function recalculate() {}

	public static function archive()
	{
		$orderId = (int) Tools::get("id");
		$archive = Tools::get("archive") == "true" ? 0 : 1;

		$query = "UPDATE auftrag SET archiviert = :archiveStatus WHERE Auftragsnummer = :orderId";
		DBAccess::updateQuery($query, [
			"orderId" => $orderId,
			"archiveStatus" => $archive,
		]);

		JSONResponseHandler::sendResponse([
			"status" => "success",
		]);
	}

	/**
	 * adds a new order to the database by using the data from the form,
	 * which is sent by the client;
	 * the function echos a json object with the response link and the order id
	 */
	public static function add()
	{
		$bezeichnung = Tools::get("bezeichnung");
		$beschreibung = Tools::get("beschreibung");
		$typ = Tools::get("typ");
		$termin = Tools::get("termin");
		$angenommenVon = Tools::get("angenommenVon");
		$kdnr = Tools::get("customerId");
		$angenommenPer = Tools::get("angenommenPer");
		$ansprechpartner = (int) Tools::get("ansprechpartner");

		$orderId = self::addToDB($kdnr, $bezeichnung, $beschreibung, $typ, $termin, $angenommenVon, $angenommenPer, $ansprechpartner);

		$data = [
			"success" => true,
			"responseLink" => Link::getPageLink("auftrag") . "?id=$orderId",
			"orderId" => $orderId
		];

		NotificationManager::addNotification(User::getCurrentUserId(), 4, "Auftrag <a href=" . $data["responseLink"] . ">$orderId</a> wurde angelegt", $orderId);

		$auftragsverlauf = new Auftragsverlauf($orderId);
		$auftragsverlauf->addToHistory($orderId, 5, "added", "Neuer Auftrag");

		JSONResponseHandler::sendResponse($data);
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
			"id" => $historyId,
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

	public static function resetAnsprechpartner($data)
	{
		$customerId = Tools::get("customerId");
		$query = "UPDATE auftrag SET Ansprechpartner = 0 WHERE Kundennummer = :customerId AND Ansprechpartner = :contactPerson;";

		DBAccess::updateQuery($query, [
			"customerId" => $customerId,
			"contactPerson" => $data["Nummer"],
		]);
	}

	public static function getOverview()
	{
		$query = "SELECT Auftragsnummer FROM auftrag WHERE archiviert != 0 AND Rechnungsnummer = 0;";
		$data = DBAccess::selectQuery($query);

		foreach ($data as $row) {
			$order = new Auftrag($row["Auftragsnummer"]);
			$orders[] = $order->getOrderCardData();
		}

		ob_start();
		insertTemplate('files/res/views/orderCardView.php', [
			"orders" => $orders,
		]);
		$content = ob_get_clean();

		return $content;
	}

	public static function addFiles()
	{
		$uploadHandler = new UploadHandler();
		$files = $uploadHandler->uploadMultiple();
		$orderId = (int) Tools::get("id");

		$query = "INSERT INTO dateien_auftraege (id_datei, id_auftrag) VALUES ";
		$values = [];
		foreach ($files as $file) {
			$values[] = [(int) $file["id"], $orderId];

			$auftragsverlauf = new Auftragsverlauf($orderId);
            $auftragsverlauf->addToHistory($file["id"], 4, "added");
  		}

		DBAccess::insertMultiple($query, $values);
	}

	public function getNotificationContent(): string
	{
		return "";
	}

	public function getNotificationType(): int
	{
		return 0;
	}

	public function getNotificationLink(): string
	{
		return "";
	}
	
	public function getNotificationSpecificId(): int
	{
		return 0;
	}
}
