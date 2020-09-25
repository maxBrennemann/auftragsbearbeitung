<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Kunde.php');
require_once('Schritt.php');
require_once('Posten.php');
require_once('FormGenerator.php');
require_once('InteractiveFormGenerator.php');
require_once('StatisticsInterface.php');
require_once('classes/DBAccess.php');
require_once('classes/Link.php');
require_once('Statistics.php');

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
		$data = DBAccess::selectQuery("SELECT Schrittnummer, Bezeichnung, Datum, `Priority` FROM schritte WHERE Auftragsnummer = {$this->Auftragsnummer} AND istErledigt = 1");
		$column_names = array(0 => array("COLUMN_NAME" => "Bezeichnung"), 1 => array("COLUMN_NAME" => "Datum"), 
				2 => array("COLUMN_NAME" => "Priority"));

		$form = new InteractiveFormGenerator("schritte");
		$form->setRowDone(true);
		$_SESSION['storedTable'] = serialize($form);
		return $form->create($data, $column_names);
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

	public function getAuftragstyp() {
		return $this->Auftragstyp;
	}

	public function preisBerechnen() {
		$price = 0;
		foreach ($this->Auftragsposten as $posten) {
			$price += $posten->bekommePreis();
		}
		return $price;
	}

	public function getKundennummer() {
		return DBAccess::selectQuery("SELECT Kundennummer FROM auftrag WHERE auftragsnummer = {$this->Auftragsnummer}")[0]['Kundennummer'];
	}

	public function getAuftragspostenAsTable() {
		$column_names = array(0 => array("COLUMN_NAME" => "Bezeichnung"), 1 => array("COLUMN_NAME" => "Beschreibung"), 
				2 => array("COLUMN_NAME" => "Stundenlohn"), 3 => array("COLUMN_NAME" => "ZeitInMinuten"), 4 => array("COLUMN_NAME" => "Preis"), 
				5 => array("COLUMN_NAME" => "Anzahl"), 6 => array("COLUMN_NAME" => "Einkaufspreis"));

		$subArr = array("Bezeichnung" => "", "Beschreibung" => "", "Stundenlohn" => "", "ZeitInMinuten" => "", "Preis" => "", "Anzahl" => "", "Einkaufspreis" => "");
		$data = array(sizeof($this->Auftragsposten));

		if (sizeof($this->Auftragsposten) == 0) {
			return "";
		}

		for ($i = 0; $i < sizeof($this->Auftragsposten); $i++) {
			$data[$i] = $this->Auftragsposten[$i]->fillToArray($subArr);
		}

		$form = new InteractiveFormGenerator("");
		$form->setRowDeletable(true);
		return $form->create($data, $column_names);
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
		$column_names = array(0 => array("COLUMN_NAME" => "Auftragsnummer"), 1 => array("COLUMN_NAME" => "Name"),
				2 => array("COLUMN_NAME" => "Auftragsbezeichnung"), 3 => array("COLUMN_NAME" => "Auftragsbeschreibung"), 4 => array("COLUMN_NAME" => "Datum"), 
				5 => array("COLUMN_NAME" => "Termin"), 6 => array("COLUMN_NAME" => "Angenommen durch"));
		
		$query = "SELECT Auftragsnummer, IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ',";
		$query .= " kunde.Nachname), kunde.Firmenname) as Name, Auftragsbezeichnung,";
		$query .= " Auftragsbeschreibung, Datum, IF(auftrag.Termin = '0000-00-00', 'kein Termin', ";
		$query .= "auftrag.Termin) AS Termin, CONCAT(mitarbeiter.Vorname, ' ', mitarbeiter.Nachname)";
		$query .= " AS 'Angenommen durch', kunde.Kundennummer FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer =";
		$query .= " kunde.Kundennummer LEFT JOIN mitarbeiter ON mitarbeiter.id = ";
		$query .= "auftrag.AngenommenDurch WHERE Rechnungsnummer = 0";

		$column_names = array(0 => array("COLUMN_NAME" => "Nr."), 1 => array("COLUMN_NAME" => "Datum"),
		2 => array("COLUMN_NAME" => "Termin"), 3 => array("COLUMN_NAME" => "Kunde"), 4 => array("COLUMN_NAME" => "Auftragsbezeichnung"));

		$query = "SELECT Auftragsnummer AS 'Nr.', Datum, IF(kunde.Firmenname = '', CONCAT(kunde.Vorname, ' ', kunde.Nachname), kunde.Firmenname) as Kunde, Auftragsbezeichnung, IF(auftrag.Termin = '0000-00-00', 'kein Termin', auftrag.Termin) AS Termin FROM auftrag LEFT JOIN kunde ON auftrag.Kundennummer = kunde.Kundennummer WHERE Rechnungsnummer = 0 AND archiviert = 1";

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
		$fahrzeugTable = new FormGenerator("fahrzeug", "", "");
		return $fahrzeugTable->createTableByDataRowLink($fahrzeuge, $column_names, "fahrzeug", "fahrzeug");
	}

	public function getFarben() {
		$farben = DBAccess::selectQuery("SELECT Farbe, Farbwert, Nummer FROM farben, farben_auftrag WHERE Auftragsnummer = id_auftrag AND id_auftrag = {$this->getAuftragsnummer()}");
		$farbTable = "";
		foreach ($farben as $farbe) {
			$farbTable .= "<div class=\"singleColorContainer\"><p class=\"singleColorName\">{$farbe['Farbe']}</p><div class=\"farbe\" style=\"background-color: #{$farbe['Farbwert']}\"></div><button onclick=\"removeColor({$farbe['Nummer']});\">×</button></div><br>";
		}

		return $farbTable;
	}

	public function getAddColors() {
		/*return <<<XML
			<span>Farbname: <input class="colorInput" type="text" max="32"></span>
			<span>Farbe (Hex): <input class="colorInput jscolor" type="text" max="32"></span>
			<span>Bezeichnung: <input class="colorInput" type="text" max="32"></span>
			<span>Hersteller: <input class="colorInput" tyep="text" max="32"></span>
			<button onclick="sendColor();">Hinuzufügen</button>
		XML;*/
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

}

?>