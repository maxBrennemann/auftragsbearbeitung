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
	protected $postenTyp = "zeit";

	function __construct($Stundenlohn, $ZeitInMinuten) {
		$this->Stundenlohn = (int) $Stundenlohn;
		$this->ZeitInMinuten = (int) $ZeitInMinuten;
	}

	public function getHTMLData() {
		$html = "<div><span>Typ: {$this->postenTyp} </span><span>Stundenlohn: {$this->Stundenlohn}€ </span>";
		$html .= "<span>Zeit in Minuten: {$this->ZeitInMinuten} </span><span>Preis: {$this->bekommePreis()}€ </span></div>";
		return $html;
	}

    public function bekommePreis() {
        return $this->kalkulierePreis();
    }

    private function kalkulierePreis() {
		$this->Kosten = $this->Stundenlohn * ($this->ZeitInMinuten / 60);
        return (float) $this->Kosten;
    }

}

?>