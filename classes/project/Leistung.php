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

	function __construct($bezeichnung, $beschreibung, $preis) {
		$this->bezeichnung = $bezeichnung;
		$this->beschreibung = $beschreibung;
		$this->preis = (int) $preis;
	}

	public function getHTMLData() {
		return "<div><span>{$this->bezeichnung}</span><br><span>Beschreibung: {$this->$beschreibung}</span><br><span>Preis: {$this->preis}</span></div>";
	}

    public function bekommePreis() {
        return (int) $this->preis;
    }

}

?>