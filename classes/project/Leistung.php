<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Posten.php');
require_once('classes/DBAccess.php');

class Leistung extends Posten {
    
    private $preis = 0;
    private $einkaufspreis = 0;
    private $bezeichnung = null;
	private $beschreibung = null;
	private $leistungsnummer = 0;
	protected $postenTyp = "leistung";
	protected $ohneBerechnung = false;

	function __construct($leistungsnummer, $beschreibung, $speziefischerPreis, $einkaufspreis) {
		$this->beschreibung = $beschreibung;
		$this->preis = (int) $speziefischerPreis;
		$this->einkaufspreis = (int) $einkaufspreis;
		$this->leistungsnummer = $leistungsnummer;

		$data =  DBAccess::selectQuery("SELECT Bezeichnung FROM leistung WHERE Nummer = $leistungsnummer");
		if ($data == null) {
			$this->bezeichnung = "";
		} else {
			$this->bezeichnung =  $data[0]["Bezeichnung"];
		}
	}

	public function getHTMLData() {
		return "<div><span>{$this->bezeichnung}</span><br><span>Beschreibung: {$this->beschreibung}</span><br><span>Preis: {$this->preis}</span></div>";
	}

	public function fillToArray($arr) {
		$arr['Preis'] = $this->preis;
		$arr['Bezeichnung'] = $this->bezeichnung;
		$arr['Beschreibung'] = $this->beschreibung;
		$arr['Einkaufspreis'] = $this->einkaufspreis;
		$arr['Leistungsnummer'] = $this->leistungsnummer;

		return $arr;
	}

	public function storeToDB($auftragsNr) {
		$data = $this->fillToArray(array());
		$data['ohneBerechnung'] = 1;
		$data['Auftragsnummer'] = $auftragsNr;
		Posten::insertPosten("leistung", $data);
	}

    public function bekommePreis() {
		if ($this->ohneBerechnung == true) {
			return 0;
		}
        return (float) $this->preis;
	}

	public function bekommeEinzelPreis() {
		return $this->preis;
	}

	public function getOhneBerechnung() {
		return $this->ohneBerechnung;
	}

	public function bekommeEKPreis() {
		return $this->einkaufspreis;
	}
	
	public function getDescription() {
		return $this->beschreibung;
	}

	public function getEinheit() {
		return "/";
	}

	public static function bearbeitungsschritteHinzufuegen($leistungsnummer, $auftragsnummer) {
		$schritte = DBAccess::selectQuery("SELECT * FROM schritte_vordefiniert WHERE Leistungsnummer = $leistungsnummer");

		foreach ($schritte as $schritt) {
			$data = array();
			$data['Bezeichnung'] = $schritt['bez'];
			$data['Datum'] = date("Y-m-d");
			$data['Priority'] = 1;
			$data['Auftragsnummer'] = $auftragsnummer;
			require_once("classes/project/Schritt.php");
			Schritt::insertStep($data);
		}
	}

	public function getQuantity() {
		return 1;
	}

}

?>