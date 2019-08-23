<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Auftrag.php');

class Rechnung extends Auftrag {

    private $rechnungsnummer = 0;
	private $summeMwSt = 0;
	private $summe = 0;

	function __construct($auftragsnummer) {
		parent::__construct($auftragsnummer);
	}

    public function PDFgenerieren() {
        
    }

	public function getRechnungsnummer() {
		return $this->rechnungsnummer;
	}

}

?>