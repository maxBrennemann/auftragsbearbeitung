<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Kunde.php');
require_once('Schritt.php');
require_once('Posten.php');
require_once('Priority.php');
require_once('FormGenerator.php');
require_once('InteractiveFormGenerator.php');
require_once('StatisticsInterface.php');
require_once('classes/DBAccess.php');
require_once('classes/Link.php');
require_once('Statistics.php');
require_once("classes/project/Table.php");

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
	protected $Auftragstyp = null;
	protected $rechnungsnummer = 0;

	/* dates */
	public $datum;
	public $termin;
	public $fertigstellung;

	private $isArchiviert = false;
	private $isRechnung = false;

	static $offeneAuftraege = array();

	function __construct($auftragsnummer) {
		$auftragsnummer = (int) $auftragsnummer;
		if ($auftragsnummer > 0) {
			$this->Auftragsnummer = $auftragsnummer;
			$data = DBAccess::selectAllByCondition("auftrag", "Auftragsnummer", $auftragsnummer);

			if (!empty($data)) {
				$this->Auftragsbeschreibung = $data[0]['Auftragsbeschreibung'];
				$this->Auftragsbezeichnung = $data[0]['Auftragsbezeichnung'];
				$this->Auftragstyp = (int) $data[0]['Auftragstyp'];
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
		$data = DBAccess::selectQuery("SELECT Bezeichnung, Datum, `Priority`, finishingDate as 'erledigt am' FROM schritte WHERE Auftragsnummer = {$this->Auftragsnummer}");
		$column_names = array(0 => array("COLUMN_NAME" => "Bezeichnung"), 1 => array("COLUMN_NAME" => "Datum"), 
				2 => array("COLUMN_NAME" => "Priority"), 3 => array("COLUMN_NAME" => "erledigt am"));

		$form = new InteractiveFormGenerator("");
		return $form->create($data, $column_names);
	}

	public function getOpenBearbeitungsschritteAsTable() {
		/* 
		 * istErledigt = 1 -> ist noch zu erledigen
		 * istErledigt = 0 -> ist schon erledigt
		*/
		$data = DBAccess::selectQuery("SELECT Schrittnummer, Bezeichnung, Datum, `Priority` FROM schritte WHERE Auftragsnummer = {$this->Auftragsnummer} AND istErledigt = 1 ORDER BY `Priority` DESC");
		$column_names = array(0 => array("COLUMN_NAME" => "Bezeichnung"), 1 => array("COLUMN_NAME" => "Datum"), 
				2 => array("COLUMN_NAME" => "Priority"));

		$form = new InteractiveFormGenerator("schritte");
		$form->setRowDone(true);
		$_SESSION['storedTable'] = serialize($form);
		return $form->create($data, $column_names);
	}

	/* getBearbeitungsschritte with new Table class */
	public function getOpenBearbeitungsschritteTable() {
		$data = DBAccess::selectQuery("SELECT Schrittnummer, Bezeichnung, Datum, `Priority` AS Priorotät FROM schritte WHERE Auftragsnummer = {$this->Auftragsnummer} AND istErledigt = 1 ORDER BY `Priority` DESC");
		$column_names = array(0 => array("COLUMN_NAME" => "Bezeichnung"), 1 => array("COLUMN_NAME" => "Datum"), 2 => array("COLUMN_NAME" => "Priorotät"));

		for ($i = 0; $i < sizeof($data); $i++) {
			$data[$i]["Priorotät"] = Priority::getPriorityLevel($data[$i]["Priorotät"]);
		}

		/* addes three buttons to table */
		$t = new Table();
		$t->createByData($data, $column_names);
		$t->addActionButton("update", $identifier = "Schrittnummer", $update = "istErledigt = 0");
		$t->addActionButton("edit");
		$t->setType("schritte");
		$t->addActionButton("delete", $identifier = "Schrittnummer");

		$_SESSION["schritte_table"] = serialize($t);

		return $t->getTable();
	}

	public function getAuftragsbeschreibung() {
		return nl2br($this->Auftragsbeschreibung);
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
		return $this->Auftragstyp;
	}

	public function getAuftragsbezeichnung() {
		return $this->Auftragsbezeichnung;
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
	private function getAuftragsPostenHelper() {
		$subArr = array(
			"Postennummer" => "", 
			"Bezeichnung" => "", 
			"Beschreibung" => "", 
			"Stundenlohn" => "", 
			"MEH" => "", 
			"Preis" => "", 
			"Anzahl" => "", 
			"Einkaufspreis" => "",
			"type" => ""
		);

		$data = array(sizeof($this->Auftragsposten));

		if (sizeof($this->Auftragsposten) == 0) {
			return "";
		}

		for ($i = 0; $i < sizeof($this->Auftragsposten); $i++) {
			$data[$i] = $this->Auftragsposten[$i]->fillToArray($subArr);
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
			6 => array("COLUMN_NAME" => "Einkaufspreis")
		);

		$data = $this->getAuftragsPostenHelper();

		/* addes edit and delete to table */
		$t = new Table();
		$t->createByData($data, $column_names);
		$t->addActionButton("edit");
		$t->setType("posten");
		$t->addActionButton("delete", $identifier = "Postennummer");
		$t->addAction(null, "+", "Rechnung/ Zahlung hinzufügen");
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
		$_SESSION["posten_table"] = serialize($t);

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

	public static function getOffeneAuftraege() {
		$query = "SELECT Auftragsnummer AS id FROM auftrag WHERE Rechnungsnummer = 0 AND archiviert = 1";
		$data = DBAccess::selectQuery($query);
		return self::getAuftragsListe($data, "id");
	}

	public static function getAuftragsListe($ids, $arrKey) {
		$column_names = array(
			0 => array("COLUMN_NAME" => "Auftragsnummer"), 
			1 => array("COLUMN_NAME" => "Name"),	
			2 => array("COLUMN_NAME" => "Auftragsbezeichnung"), 
			3 => array("COLUMN_NAME" => "Auftragsbeschreibung"), 
			4 => array("COLUMN_NAME" => "Datum"), 
			5 => array("COLUMN_NAME" => "Termin"), 
			6 => array("COLUMN_NAME" => "Angenommen durch")
		);
		
		$query = "SELECT Auftragsnummer, IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ',";
		$query .= " kunde.Nachname), kunde.Firmenname) as Name, Auftragsbezeichnung,";
		$query .= " Auftragsbeschreibung, Datum, IF(auftrag.Termin = '0000-00-00', 'kein Termin', ";
		$query .= "auftrag.Termin) AS Termin, CONCAT(mitarbeiter.Vorname, ' ', mitarbeiter.Nachname)";
		$query .= " AS 'Angenommen durch', kunde.Kundennummer FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer =";
		$query .= " kunde.Kundennummer LEFT JOIN mitarbeiter ON mitarbeiter.id = ";
		$query .= "auftrag.AngenommenDurch WHERE Rechnungsnummer = 0";

		$column_names = array(
			0 => array("COLUMN_NAME" => "Nr."), 
			1 => array("COLUMN_NAME" => "Datum"),
			2 => array("COLUMN_NAME" => "Termin"), 
			3 => array("COLUMN_NAME" => "Kunde"), 
			4 => array("COLUMN_NAME" => "Auftragsbezeichnung")
		);

		$query = "SELECT Auftragsnummer AS 'Nr.', Datum, IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) as Kunde, Auftragsbezeichnung, IF(auftrag.Termin = '0000-00-00', 'kein Termin', auftrag.Termin) AS Termin FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer = kunde.Kundennummer WHERE ";

		foreach($ids as $id) {
			$nr = $id[$arrKey]; /* must be fixed later */
			$query .= "Auftragsnummer = $nr OR ";
		}
		$query = substr($query, 0, -4);

		$data = DBAccess::selectQuery($query);
		self::$offeneAuftraege = $data;

		$form = new FormGenerator("auftrag", "Datum", "Rechnungsnummer = 0");
		$table = $form->createTableByDataRowLink($data, $column_names, "auftrag", null);
		return $table;
	}

	public function istRechnungGestellt() {
		return $this->rechnungsnummer == 0 ? false : true;
	}

	public function getRechnungsnummer() {
		return $this->rechnungsnummer;
	}

	public function getLinkedVehicles() {
		return DBAccess::selectQuery("SELECT Kennzeichen, Fahrzeug, Nummer FROM fahrzeuge LEFT JOIN fahrzeuge_auftraege ON fahrzeuge_auftraege.id_fahrzeug = Nummer WHERE fahrzeuge_auftraege.id_auftrag = {$this->getAuftragsnummer()}");
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
		return $t->getTable();
	}

	public function getFarben() {
		$farben = DBAccess::selectQuery("SELECT Farbe, Farbwert, Nummer FROM farben, farben_auftrag WHERE Auftragsnummer = id_auftrag AND id_auftrag = {$this->getAuftragsnummer()}");
		$farbTable = "";
		foreach ($farben as $farbe) {
			$farbTable .= "<div class=\"singleColorContainer\"><p class=\"singleColorName\">{$farbe['Farbe']}</p><div class=\"farbe\" style=\"background-color: #{$farbe['Farbwert']}\"></div><button onclick=\"removeColor({$farbe['Nummer']});\">×</button></div><br>";
		}

		return $farbTable;
	}

	/*
	 * creates a div card with the order details
	*/
	public function getOrderCard() {
		$archievedBtn = $this->isArchiviert ? "<button>archiviert</button>" : "<!-- Auftrag archiviert -->";
		$orderTitle = $this->Auftragsbezeichnung;
		$orderDescription = $this->Auftragsbeschreibung;

		$data = DBAccess::selectQuery("SELECT Datum, Termin, Fertigstellung FROM auftrag WHERE Auftragsnummer = $this->Auftragsnummer")[0];
		$date = $data['Datum'];
		$deadline = $data['Termin'];
		$finished = $data['Fertigstellung'];

		$invoice =  $this->rechnungsnummer == 0 ? "" : "Rechnung Nr. $this->rechnungsnummer";
		$summe = $this->rechnungsnummer != 0 ? "<button>" . $this->preisBerechnen() . "€</button>" : "-€";

		$html = "
		<div class=\"innerDefCont orderCard\">
			<h3>$orderTitle</h3>
			<a href=\"" . Link::getPageLink("auftrag") . "?id=$this->Auftragsnummer" . "\">Zum Auftrag $this->Auftragsnummer</a>
			<p>$orderDescription</p>
			<table>
				<tr>
					<th>Datum</th>
					<td>$date</td>
				</tr>
				<tr>
					<th>Termin</th>
					<td>$deadline</td>
				</tr>
				<tr>
					<th>Fertigstellung</th>
					<td>$finished</td>
				</tr>
			</table>
			<br>
			$archievedBtn
			$invoice
			<br>
			<p>Auftragssumme: $summe </p>
		</div>";

		return $html;
	}

	/* this function fetches the associated notes from the db */
	public function getNotes() {
		$html = "";

		$notes = DBAccess::selectQuery("SELECT Notiz FROM notizen WHERE Auftragsnummer = $this->Auftragsnummer");
		foreach($notes as $note) {
			$content = $note['Notiz'];
			$html .= "
				<div class=\"notes\">
					<div class=\"noteheader\">Notiz 📓</div>
					<div class=\"notecontent\">$content</div>
					<div class=\"notebutton\" onclick=\"removeNote(event)\">×</div>
				</div>
			";
		}

		return $html;
	}

	public function getAddColors() {
		$string = '<span>Farbname: <input class="colorInput" type="text" max="32"></span><span>Farbe (Hex): <input class="colorInput jscolor" type="text" max="32"></span><span>Bezeichnung: <input class="colorInput" type="text" max="32"></span><span>Hersteller: <input class="colorInput" tyep="text" max="32"></span><button onclick="sendColor();">Hinuzufügen</button>';
		return $string;
	}

	public function recalculate() {
		Statistics::auftragEroeffnen();
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
	 * TODO implement data deletion
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

}

?>