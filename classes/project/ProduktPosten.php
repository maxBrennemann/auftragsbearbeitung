<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Posten.php');

class ProduktPosten extends Posten {
    
    private $Preis = 0;
	private $Einkaufspreis = 0;
    private $Bezeichnung = null;
	private $Beschreibung = null;
	private $Anzahl = 0;
	protected $postenTyp = "produkt";
	protected $ohneBerechnung = false;

	function __construct($Preis, $Bezeichnung, $Beschreibung, $Anzahl, $Einkaufspreis) {
		$this->Preis = $Preis;
		$this->Einkaufspreis = $Einkaufspreis;
		$this->Bezeichnung = $Bezeichnung;
		$this->Beschreibung = $Beschreibung;
		$this->Anzahl = $Anzahl;
	}

	public function getHTMLData() {
		$html = "<div><span>Typ: {$this->postenTyp} </span><span>Preis: {$this->bekommePreis()}� </span>";
		$html .= "<span>Bezeichnung: {$this->Bezeichnung} </span><span>Beschreibung: {$this->Beschreibung} </span></div>";
		return $html;
	}

	public function fillToArray($arr) {
		$arr['Preis'] = $this->bekommePreis();
		$arr['Bezeichnung'] = $this->Bezeichnung;
		$arr['Beschreibung'] = $this->Beschreibung;
		$arr['Anzahl'] = $this->Anzahl;
		$arr['Einkaufspreis'] = $this->Einkaufspreis;

		return $arr;
	}

    public function bekommePreis() {
		if ($this->ohneBerechnung == true) {
			return 0;
		}
        return (int) $this->Preis;
    }

}

?>