<?php

require_once('Kunde.php');
require_once('Schritt.php');
require_once('Posten.php');
require_once('Priority.php');
require_once('FormGenerator.php');
require_once('InteractiveFormGenerator.php');
require_once('StatisticsInterface.php');
require_once('Statistics.php');
require_once('GlobalSettings.php');
require_once("classes/project/Table.php");
require_once("classes/project/NotificationManager.php");
require_once('classes/project/ClientSettings.php');

/**
 * Klasse generiert im Zusammenhang mit der Template Datei auftrag.php die Übersicht für einen bestimmten Auftrag.
 * Dabei werden alle Auftragsposten und alle Bearbeitungsschritte aus der Datenbank geladen und als Objekte erstellt.
 * Diese können bearbeitet, ergänzt und abgearbeitet werden.
 */
class Auftrag implements StatisticsInterface {

    protected $Auftragsnummer = null;
	protected $Auftragsbezeichnung = null;
	protected $Auftragsbeschreibung = null;
	protected $Auftragsposten = array();
	protected $Bearbeitungsschritte = array();
	protected $auftragstyp = null;
	protected $rechnungsnummer = 0;

	/* dates */
	public $datum;
	public $termin;
	public $fertigstellung;

	private $isArchiviert = false;
	private $isRechnung = false;

	function __construct($auftragsnummer) {
		$auftragsnummer = (int) $auftragsnummer;
		if ($auftragsnummer > 0) {
			$this->Auftragsnummer = $auftragsnummer;
			$data = DBAccess::selectAllByCondition("auftrag", "Auftragsnummer", $auftragsnummer);

			if (!empty($data)) {
				$this->Auftragsbeschreibung = $data[0]['Auftragsbeschreibung'];
				$this->Auftragsbezeichnung = $data[0]['Auftragsbezeichnung'];
				$this->auftragstyp = (int) $data[0]['Auftragstyp'];
				$this->rechnungsnummer = $data[0]['Rechnungsnummer'];

				$this->datum = $data[0]['Datum'];
				$this->termin = $data[0]['Termin'];
				$this->fertigstellung = $data[0]['Fertigstellung'];

				if ($data[0]['archiviert'] == 0 || $data[0]['archiviert'] == "0") {
					$this->isArchiviert = true;
				}

				if ($data[0]['Rechnungsnummer'] != 0) {
					$this->isArchiviert = true;
				}
				
				$data = DBAccess::selectQuery("SELECT * FROM schritte WHERE Auftragsnummer = {$auftragsnummer}");
				foreach ($data as $step) {
					$element = new Schritt($step['Auftragsnummer'], $step['Schrittnummer'], $step['Bezeichnung'], $step['Datum'], $step['Priority'], $step['istErledigt']);
					array_push($this->Bearbeitungsschritte, $element);
				}

				$this->Auftragsposten = Posten::bekommeAllePosten($auftragsnummer);
			} else {
				throw new Exception("Auftragsnummer " . $auftragsnummer . " existiert nicht oder kann nicht gefunden werden<br>");
			}
		}
	}

	public function bekommeAnsprechpartner() {
		$data = DBAccess::selectQuery("SELECT Vorname, Nachname, Email, Durchwahl, Mobiltelefonnummer FROM ansprechpartner, auftrag WHERE auftrag.Ansprechpartner = ansprechpartner.Nummer AND auftrag.Auftragsnummer = {$this->Auftragsnummer}");
		if (empty($data)) {
			return -1;
		}
		return $data[0];
	}

	public function getBearbeitungsschritte() {
		$htmlData = "";
		foreach ($this->Bearbeitungsschritte as $schritt) {
			$htmlData .= $schritt->getHTMLData();
		}
		return $htmlData;
	}

	public function getBearbeitungsschritteAsTable() {
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
	public function getOpenBearbeitungsschritteTable() {
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

	public function getAuftragsbeschreibung() {
		return $this->Auftragsbeschreibung;
	}

	public function getAuftragsnummer() {
		return $this->Auftragsnummer;
	}

	public function getAuftragsposten() {
		$htmlData = "";
		foreach ($this->Auftragsposten as $posten) {
			$htmlData .= $posten->getHTMLData();
		}
		return $htmlData;
	}

	public function getAuftragspostenData() {
		return $this->Auftragsposten;
	}

	public function getAuftragstyp() {
		return $this->auftragstyp;
	}

	public function getAuftragstypBezeichnung() {
		$query = "SELECT `Auftragstyp` FROM `auftragstyp` WHERE `id` = :idAuftragstyp LIMIT 1;";
		$bez = DBAccess::selectQuery($query, ["idAuftragstyp" => $this->auftragstyp]);

		if ($bez != null) {
			return $bez[0]["Auftragstyp"];
		} else {
			return "";
		}
	}

	public static function getAllOrderTypes() {
		$query = "SELECT * FROM `auftragstyp`;";
		$result = DBAccess::selectQuery($query);
		return $result;
	}

	public function getAuftragsbezeichnung() {
		return $this->Auftragsbezeichnung;
	}

	public function getDate() {
		return $this->datum;
	}

	public function getDeadline() {
		return $this->termin;
	}

	/**
	 * calculates the sum of all items in the order
	 */
	public function calcOrderSum () {
		$price = 0;
		foreach ($this->Auftragsposten as $posten) {
			if ($posten->isInvoice() == 1) {
				$price += $posten->bekommePreis();
			}
		}
		return $price;
	}

	public function preisBerechnen() {
		$price = 0;
		foreach ($this->Auftragsposten as $posten) {
			$price += $posten->bekommePreis();
		}
		return $price;
	}

	public function gewinnBerechnen() {
		$price = 0;
		foreach ($this->Auftragsposten as $posten) {
			$price += $posten->bekommeDifferenz();
		}
		return $price;
	}

	public function getKundennummer() {
		return DBAccess::selectQuery("SELECT Kundennummer FROM auftrag WHERE auftragsnummer = {$this->Auftragsnummer}")[0]['Kundennummer'];
	}

	/*
	 * helper function for creating the AuftragsPosten table
	 * function returns the data for the table
	 */
	private function getAuftragsPostenHelper($isInvoice = false) {
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

	public function getAuftragspostenAsTable() {
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
		$t->addActionButton("delete", $identifier = "Postennummer");
		$t->addAction(null, Icon::$iconAdd, "Rechnung/ Zahlung hinzufügen");
		$t->addActionButton("move");
		$t->addDataset("type", "type");
		$_SESSION["posten_table"] = serialize($t);
		$_SESSION[$t->getTableKey()] = serialize($t);

		return $t->getTable();
	}

	public function getAuftragsPostenCheckTable() {
		$column_names = array(
			0 => array("COLUMN_NAME" => "Bezeichnung"), 
			1 => array("COLUMN_NAME" => "Beschreibung"), 
			2 => array("COLUMN_NAME" => "Stundenlohn"), 
			3 => array("COLUMN_NAME" => "Anzahl"), 
			4 => array("COLUMN_NAME" => "MEH"), 
			5 => array("COLUMN_NAME" => "Preis"), 
			6 => array("COLUMN_NAME" => "Einkaufspreis")
		);

		/* checks if postenarray is empty */
		$data = $this->getAuftragsPostenHelper();
		if (sizeof($this->Auftragsposten) == 0) {
			$data = [];
		}

		/* addes edit and delete to table */
		$t = new Table();
		$t->createByData($data, $column_names);
		$t->addSelector("check");
		$t->setType("posten");
		$_SESSION[$t->getTableKey()] = serialize($t);

		return $t->getTable();
	}

	/*
	 * returns all invoice columns from invoice_posten table
	 */
	public function getInvoicePostenTable() {
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

	public function getIsArchiviert() {
		return $this->isArchiviert;
	}

    public function bearbeitungsschrittHinzufuegen() {
        
    }

    public function bearbeitungsschrittEntfernen() {
        
    }

    public function bearbeitunsschrittBearbeiten() {
		
    }

    public function postenHinzufuegen() {
        
    }

    public function postenEntfernen() {
        
    }

    public function schritteNachTypGenerieren() {
        
    }

	/* 
	 * this function returns all orders which are marked as ready to finish;
	 * an order is ready when its "archived" column is set to -1
	 */
	public static function getReadyOrders() {
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

	public static function getAuftragsliste() {
		$column_names = array(
			0 => array("COLUMN_NAME" => "Auftragsnummer", "ALT" => "Nr.", "NOWRAP"), 
			1 => array("COLUMN_NAME" => "Datum", "NOWRAP" => true),
			2 => array("COLUMN_NAME" => "Termin", "NOWRAP" => true), 
			3 => array("COLUMN_NAME" => "Kunde"), 
			4 => array("COLUMN_NAME" => "Auftragsbezeichnung")
		);

		$query = "SELECT Auftragsnummer, Datum, IF(kunde.Firmenname = '', 
				CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) as Kunde, 
				Auftragsbezeichnung, IF(auftrag.Termin IS NULL OR auftrag.Termin = '0000-00-00', 'kein Termin', auftrag.Termin) AS Termin 
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

	public function istRechnungGestellt() {
		return $this->rechnungsnummer == 0 ? false : true;
	}

	public function getRechnungsnummer() {
		return $this->rechnungsnummer;
	}

	public function getLinkedVehicles() {
		return DBAccess::selectQuery("SELECT Nummer, Kennzeichen, Fahrzeug FROM fahrzeuge LEFT JOIN fahrzeuge_auftraege ON fahrzeuge_auftraege.id_fahrzeug = Nummer WHERE fahrzeuge_auftraege.id_auftrag = {$this->getAuftragsnummer()}");
	}

	public function getFahrzeuge() {
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

	public function getFarben() {
		$farben = DBAccess::selectQuery("SELECT Farbe, Farbwert, id AS Nummer, Hersteller, Bezeichnung FROM color, color_auftrag WHERE id_color = id AND id_auftrag = :orderId", ["orderId" => $this->getAuftragsnummer()]);

		ob_start();
		insertTemplate('files/res/views/colorView.php', [
			"farben" => $farben,
		]);
		$content = ob_get_clean();

		return $content;
	}

	/*
	 * creates a div card with the order details
	*/
	public function getOrderCardData() {
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

	/* this function fetches the associated notes from the db */
	public function getNotes() {
		$notes = DBAccess::selectQuery("SELECT Notiz, Nummer FROM notizen WHERE Auftragsnummer = :id ORDER BY creation_date DESC", [
			"id" => $this->Auftragsnummer
		]);

		ob_start();
		insertTemplate('files/res/views/noteView.php', [
			"notes" => $notes,
			"icon" => Icon::$iconNotebook,
		]);
		$content = ob_get_clean();
		return $content;
	}

	public function recalculate() {
		//Statistics::auftragEroeffnen();
		/*
		* Theoretisch sollte auftragAbschliessen() aufgerufen werden, jedoch müssen
		* die Methoden in Statistics noch angepasst werden
		*/
	}

	public function archiveOrder() {
		$query = "UPDATE auftrag SET archiviert = 0 WHERE Auftragsnummer = {$this->Auftragsnummer}";
		DBAccess::updateQuery($query);
	}

	/*
	 * adds a list to the order
	 * lists can be edited
	*/
	public function addList($listId) {
		DBAccess::insertQuery("INSERT INTO auftrag_liste (auftrags_id, listen_id) VALUES ({$this->Auftragsnummer}, $listId)");
	}

	/*
	 * removes a list, later the saved list data has to be removed as well
	 * TODO: implement data deletion
	*/
	public function removeList($listId) {
		DBAccess::deleteQuery("DELETE FROM auftrag_liste WHERE auftrags_id = {$this->Auftragsnummer} AND listen_id = $listId");
	}

	public function getListIds() {
		return DBAccess::selectQuery("SELECT listen_id FROM auftrag_liste WHERE auftrags_id = {$this->Auftragsnummer}");
	}

	public function showAttachedLists() {
		$listenIds = self::getListIds();
		$html = "";
		foreach ($listenIds as $id) {
			$html .= (Liste::readList($id['listen_id']))->toHTML($this->Auftragsnummer);
		}
		return $html;
	}

	public function getDefaultWage() {
		$defaultWage = GlobalSettings::getSetting("defaultWage");
		return $defaultWage;
	}

	/**
	 * adds a new order to the database by using the data from the form,
	 * which is sent by the client;
	 * the function echos a json object with the response link and the order id
	 */
	public static function add() {
		$bezeichnung = $_POST['bezeichnung'];
		$beschreibung = $_POST['beschreibung'];
		$typ = $_POST['typ'];
		$termin = getParameter("termin", "POST", null);
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
	private static function addToDB($kdnr, $bezeichnung, $beschreibung, $typ, $termin, $angenommenVon, $angenommenPer, $ansprechpartner) {
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

	public static function getFiles($auftragsnummer) {
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

}
