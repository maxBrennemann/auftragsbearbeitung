<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Auftrag.php');
require_once('Auftragsverlauf.php');

class Schritt {
    
	private $istAllgemein = null;
	private $bezeichnung = null;
	private $datum = null;
	private $priority = null;
	private $istErledigt = null;
	private $auftragsnummer = null;
	private $schrittnummer = null;

	function __construct($auftragsnummer, $schrittnummer, $bezeichnung, $datum, $priority, $istErledigt) {
		$this->auftragsnummer = $auftragsnummer;
		$this->bezeichnung = $bezeichnung;
		$this->schrittnummer = $schrittnummer;
		$this->datum = $datum;
		$this->priority = $priority;
		$this->istErledigt = $istErledigt;
	}

    public function bearbeiten() {
        
    }
 
    public function erledigen() {
        
    }

	public function getHTMLCode() {
		$htmlCode = "<div><span>{$this->bezeichnung}</span><br><span>Datum: {$this->datum}</span><br><span>{$this->priority}</span><br><span>{$this->istErledigt}</span></div>";
		return $htmlCode;
	}

	public static function insertStep($data) {
		$bez = $data['Bezeichnung'];
		$dat = $data['Datum'];
		$pri = $data['Priority'];
		$auf = $data['Auftragsnummer'];

		$auftragsverlauf = new Auftragsverlauf($auf);
		$postennummer = DBAccess::insertQuery("INSERT INTO `schritte` (`Auftragsnummer`, `istAllgemein`, `Bezeichnung`, `Datum`, `Priority`, `istErledigt`) VALUES ($auf, 1, '$bez', '$dat', $pri, 1)");
		$auftragsverlauf->addToHistory($postennummer, 2, "added");
	}

}

?>