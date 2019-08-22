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

    public $Auftragsnummer = null;
	public $Auftragsbezeichnung = null;
	public $Auftragsbeschreibung = null;
	public $Auftragsposten = null;
	public $Bearbeitungsschritte = array();
	public $Auftragstyp = null;

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

    public function bearbeitungsschrittHinzufuegen() {
        
    }

    public function bearbeitungsschrittEntfernen() {
        
    }

    /**
     * Short description of method bearbeitunsschrittBearbeiten
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function bearbeitunsschrittBearbeiten()
    {
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E0 begin
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E0 end
    }

    /**
     * Short description of method postenHinzufuegen
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function postenHinzufuegen()
    {
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E2 begin
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E2 end
    }

    /**
     * Short description of method postenEntfernen
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function postenEntfernen()
    {
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E4 begin
        // section -64--88--78-22--dbaf7cb:16c8686fa2d:-8000:00000000000009E4 end
    }

    /**
     * Short description of method schritteNachTypGenerieren
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function schritteNachTypGenerieren()
    {
        // section -64--88--78-22-6f584299:16ca497f3f8:-8000:0000000000000A0A begin
        // section -64--88--78-22-6f584299:16ca497f3f8:-8000:0000000000000A0A end
    }

}

?>