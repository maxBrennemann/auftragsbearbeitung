<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Posten.php');

class Leistung extends Posten {
    
    private $preis = 0;
    private $bezeichnung = null;
	private $beschreibung = null;
	protected $postenTyp = "leistung";

	function __construct($Bezeichnung, $Beschreibung) {
		$this->bezeichnung = $Bezeichnung;
		$this->beschreibung = $Beschreibung;
	}

	public function getHTMLData() {
		return "<div><span>{$this->bezeichnung}</span><br><span>Beschreibung: {$this->$beschreibung}</span><br><span>Preis: {$this->preis}</span></div>";
	}

    public function bekommePreis() {
        return $this->Preis();
    }

}

?>