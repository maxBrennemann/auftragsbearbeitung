<?php

namespace Classes\Project;

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Posten.php');

class EmptyPosten extends Posten {
    
	protected $postenTyp = "empty";
    public $beschreibung = "";
    public $id = 0;

	function __construct($id, $beschreibung) {
        $this->beschreibung = $beschreibung;
        $this->id = (int) $id;
	}

	public function getHTMLData() {
		return "";
	}

	public function fillToArray($arr) {
		return $arr;
	}
	
	public function bekommeEinzelPreis() {
		return 0;
	}

    public function bekommePreis() {
		return 0;
    }

	public function bekommePreis_formatted() {
		return "";
	}

	public function bekommeEinzelPreis_formatted() {
		return "";
	}

	public function bekommeDifferenz() {
		return 0;
	}
	
	public function getDescription() {
		return $this->beschreibung;
	}

	public function getEinheit() {
		return "";
	}

	public function getWage() {
		return "";
	}

	public function getQuantity() {
		return "";
	}

	public function getOhneBerechnung() {
		return "";
	}

	public function isInvoice() {
		return "";
	}

	public function calculateDiscount() {

	}

	public function storeToDB($auftragsNr) {
	}

}

?>