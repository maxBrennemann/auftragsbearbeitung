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
require_once('classes/DBAccess.php');
require_once('classes/Link.php');

/**
 * Klasse generiert im Zusammenhang mit der Template Datei auftrag.php die Übersicht für einen bestimmten Auftrag.
 * Dabei werden alle Auftragsposten und alle Bearbeitungsschritte aus der Datenbank geladen und als Objekte erstellt.
 * Diese können bearbeitet, ergänzt und abgearbeitet werden.
 */
class Auftrag {

    protected $Auftragsnummer = null;
	protected $Auftragsbezeichnung = null;
	protected $Auftragsbeschreibung = null;
	protected $Auftragsposten = array();
	protected $Bearbeitungsschritte = array();
	protected $Auftragstyp = null;

	function __construct($auftragsnummer) {
		if ($auftragsnummer > 0) {
			$this->Auftragsnummer = $auftragsnummer;
			$data = DBAccess::selectQuery("SELECT * FROM `auftrag` WHERE Auftragsnummer = {$auftragsnummer}");

			if (!empty($data)) {
				$this->Auftragsbeschreibung = $data[0]['Auftragsbeschreibung'];
				$this->Auftragsbezeichnung = $data[0]['Auftragsbezeichnung'];

				$data = DBAccess::selectQuery("SELECT Schrittnummer, Bezeichnung, Datum, Priority, istErledigt FROM schritte WHERE Auftragsnummer = {$auftragsnummer}");
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
		$data = DBAccess::selectQuery("SELECT * FROM schritte WHERE Auftragsnummer = {$this->Auftragsnummer}");
		$column_names = DBAccess::selectQuery("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'schritte'");
		$form = new FormGenerator("", "", "");
		return $form->createTableByData($data, $column_names);
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

		for ($i = 0; $i < sizeof($this->Auftragsposten); $i++) {
			$data[$i] = $this->Auftragsposten[$i]->fillToArray($subArr);
		}

		$form = new InteractiveFormGenerator("");
		return $form->create($data, $column_names);
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

}

?>