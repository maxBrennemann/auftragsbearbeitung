<?php

error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Posten.php');

class Zeit extends Posten {
    
    private $Stundenlohn = null;
    private $ZeitInMinuten = null;
	private $Kosten = null;
	private $beschreibung = null;
	protected $postenTyp = "zeit";

	function __construct($Stundenlohn, $ZeitInMinuten, $beschreibung) {
		$this->Stundenlohn = (int) $Stundenlohn;
		$this->ZeitInMinuten = (int) $ZeitInMinuten;
		$this->beschreibung = $beschreibung;
	}

	public function getHTMLData() {
		$html = "<div><span>Typ: {$this->postenTyp} </span><span>Stundenlohn: {$this->Stundenlohn}€ </span>";
		$html .= "<span>Zeit in Minuten: {$this->ZeitInMinuten} </span><span>Preis: {$this->bekommePreis()}€ </span></div>";
		return $html;
	}

	public function fillToArray($arr) {
		$arr['Preis'] = $this->bekommePreis();
		$arr['Stundenlohn'] = $this->Stundenlohn;
		$arr['ZeitInMinuten'] = $this->ZeitInMinuten;
		$arr['Beschreibung'] = $this->beschreibung;

		return $arr;
	}

    public function bekommePreis() {
        return $this->kalkulierePreis();
    }

    private function kalkulierePreis() {
		$this->Kosten = $this->Stundenlohn * ($this->ZeitInMinuten / 60);
        return round((float) $this->Kosten, 2);
    }

}

?>