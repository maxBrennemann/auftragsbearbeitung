<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Kunde.php');
require_once('Schritt.php');
require_once('Posten.php');
require_once('classes/DBAccess.php');
require_once('classes/Link.php');

/**
 * Klasse generiert im Zusammenhang mit der Template Datei auftrag.php die Übersicht für einen bestimmten Auftrag.
 * Dabei werden alle Auftragsposten und alle Bearbeitungsschritte aus der Datenbank geladen und als Objekte erstellt.
 * Diese können bearbeitet, ergänzt und abgearbeitet werden.
 */
class Auftrag {

    private $Auftragsnummer = null;
	private $Auftragsbezeichnung = null;
	private $Auftragsbeschreibung = null;
	private $Auftragsposten = null;
	private $Bearbeitungsschritte = array();
	private $Auftragstyp = null;

	function __construct($auftragsnummer) {
		if ($auftragsnummer > 0) {
			$this->Auftragsnummer = $auftragsnummer;
			$data = DBAccess::selectQuery("SELECT * FROM `auftrag` WHERE Auftragsnummer = {$auftragsnummer}");

			$this->Auftragsbeschreibung = $data[0]['Auftragsbeschreibung'];
			$this->Auftragsbezeichnung = $data[0]['Auftragsbezeichnung'];

			$data = DBAccess::selectQuery("SELECT Schrittnummer, Bezeichnung, Datum, Priority, istErledigt FROM schritte WHERE Auftragsnummer = {$auftragsnummer}");
			foreach ($data as $step) {
				$element = new Schritt($step['Auftragsnummer'], $step['Schrittnummer'], $step['Bezeichnung'], $step['Datum'], $step['Priority'], $step['istErledigt']);
				array_push($this->Bearbeitungsschritte, $element);
			}

			$this->Auftragsposten = Posten::bekommeAllePosten($auftragsnummer);
		}
	}

	public function getBearbeitungsschritte() {
		$htmlData = "";
		foreach ($this->Bearbeitungsschritte as $schritt) {
			$htmlData .= $schritt->getHTMLData();
		}
		return $htmlData;
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