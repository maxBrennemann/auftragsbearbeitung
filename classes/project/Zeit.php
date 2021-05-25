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
	protected $ohneBerechnung = false;

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
		$arr['Preis'] = number_format($this->bekommePreis(), 2, ',', '') . "€";
		$arr['Stundenlohn'] = number_format($this->Stundenlohn, 2, ',', '') . "€";
		$arr['Zeit in Minuten'] = $this->ZeitInMinuten . "min";
		$arr['Beschreibung'] = $this->beschreibung;
		$arr['Einkaufspreis'] = "-";

		return $arr;
	}
	
	public function bekommeEinzelPreis() {
		return $this->bekommePreis();
	}

    public function bekommePreis() {
		if ($this->ohneBerechnung == true) {
			return 0;
		}
        return $this->kalkulierePreis();
    }

	public function bekommeDifferenz() {
		return $this->bekommePreis();
	}

    private function kalkulierePreis() {
		$this->Kosten = $this->Stundenlohn * ($this->ZeitInMinuten / 60);
        return round((float) $this->Kosten, 2);
	}
	
	public function getDescription() {
		return $this->beschreibung;
	}

	public function getEinheit() {
		return "min";
	}

	public function getWage() {
		return $this->Stundenlohn;
	}

	public function getQuantity() {
		return $this->ZeitInMinuten;
	}

	public function getOhneBerechnung() {
		return $this->ohneBerechnung;
	}

	public function storeToDB($auftragsNr) {
		$data = $this->fillToArray(array());
		$data['ohneBerechnung'] = 1;
		$data['Auftragsnummer'] = $auftragsNr;
		Posten::insertPosten("zeit", $data);
	}

}

?>