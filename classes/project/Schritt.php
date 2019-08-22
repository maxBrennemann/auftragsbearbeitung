<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Auftrag.php');

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

}

?>