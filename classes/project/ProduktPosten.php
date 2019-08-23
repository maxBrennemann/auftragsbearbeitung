<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Posten.php');

class ProduktPosten extends Posten {
    
    private $Preis = null;
    private $Bezeichnung = null;
	private $Beschreibung = null;
	protected $postenTyp = "produkt";

	function __construct($Preis, $Bezeichnung, $Beschreibung) {
		$this->Preis = $Preis;
		$this->Bezeichnung = $Bezeichnung;
		$this->Beschreibung = $Beschreibung;
	}

	public function getHTMLData() {
		$html = "<div><span>Typ: {$this->postenTyp} </span><span>Preis: {$this->bekommePreis}€ </span>";
		$html .= "<span>Bezeichnung: {$this->Bezeichnung} </span><span>Beschreibung: {$this->Beschreibung} </span></div>";
		return $html;
	}

    public function bekommePreis() {
        return $this->Preis();
    }

}

?>