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
	private $discount = -1;
    private $bezeichnung = null;
	private $beschreibung = null;
	private $leistungsnummer = 0;
	protected $postenTyp = "leistung";
	protected $ohneBerechnung = false;

	function __construct($leistungsnummer, $beschreibung, $speziefischerPreis, $einkaufspreis, $discount) {
		$this->beschreibung = $beschreibung;
		$this->preis =$speziefischerPreis;
		$this->einkaufspreis = $einkaufspreis;
		$this->leistungsnummer = $leistungsnummer;

		$data =  DBAccess::selectQuery("SELECT Bezeichnung FROM leistung WHERE Nummer = $leistungsnummer");
		if ($data == null) {
			$this->bezeichnung = "";
		} else {
			$this->bezeichnung =  $data[0]["Bezeichnung"];
		}

		if ($discount != 0 && $discount > 0 && $discount <= 100) {
			$this->discount = $discount;
		}
	}

	public function getHTMLData() {
		return "<div><span>{$this->bezeichnung}</span><br><span>Beschreibung: {$this->beschreibung}</span><br><span>Preis: {$this->preis}</span></div>";
	}

	public function fillToArray($arr) {
		$arr['Preis'] = $this->bekommePreisTabelle();
		$arr['Bezeichnung'] = $this->bezeichnung;
		$arr['Beschreibung'] = $this->beschreibung;
		$arr['Einkaufspreis'] = number_format($this->einkaufspreis, 2, ',', '') . "€";
		$arr['Leistungsnummer'] = $this->leistungsnummer;

		return $arr;
	}

	/* returns the price if no discount is applied, else calculates the discount and returns the according table */
	private function bekommePreisTabelle() {
		$price_formatted = number_format($this->preis, 2, ',', '') . "€";

		if ($this->discount != -1) {
			$discountedPrice = number_format($this->preis - $this->preis * ($this->discount / 100), 2, ',', '') . "€";
			$discount_table = "
				<table class=\"innerTable\">
					<tr>
						<td>Preis</td>
						<td>{$price_formatted}</td>
						<td>{$discountedPrice}</td>
					</tr>
					<tr>
						<td>Rabatt</td>
						<td colspan=\"2\">{$this->discount}%</td>
					</tr>
				</table>";
			
			return $discount_table;
		} else {
			return $price_formatted;
		}
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
        return (float) $this->preis * (1 - ($this->discount / 100));
	}

	public function bekommeEinzelPreis() {
		return $this->preis;
	}

	public function bekommeDifferenz() {
		if ($this->ohneBerechnung == true) {
			return 0;
		}
        return (float) ($this->preis - $this->einkaufspreis);
	}

	public function getOhneBerechnung() {
		return $this->ohneBerechnung;
	}

	public function bekommeEKPreis() {
		return $this->einkaufspreis;
	}

	public function calculateDiscount() {
		
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