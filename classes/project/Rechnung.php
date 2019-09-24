<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Auftrag.php');
require_once('classes/DBAccess.php');

class Rechnung extends Auftrag {

    private $rechnungsnummer = 0;
	private $summeMwSt = 0;
	private $summe = 0;

	function __construct($rechnungsnummer) {
		$auftragsnummer = DBAccess::selectQuery("SELECT Auftragsnummer FROM auftrag WHERE Rechnungsnummer = {$rechnungsnummer}");
		if (!empty($auftragsnummer)) {
			$auftragsnummer = $auftragsnummer[0]['Auftragsnummer'];
		} else {
			trigger_error("Rechnungsnummer does not match to Auftragsnummer");
		}
		parent::__construct($auftragsnummer);
	}

    public function PDFgenerieren() {
        
    }

	public function getRechnungsnummer() {
		return $this->rechnungsnummer;
	}

	public static function getNextNumber() {
		$number = DBAccess::selectQuery("SELECT MAX(Rechnungsnummer) FROM auftrag")[0]['MAX(Rechnungsnummer)'];
		$number = (int) $number;
		$number++;
		return $number;
	}

}

?>