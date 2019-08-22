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

	function __construct($Stundenlohn, $ZeitInMinuten) {
		$this->Stundenlohn = $Stundenlohn;
		$this->ZeitInMinuten = $ZeitInMinuten;
	}

    public function bekommePreis() {
        return $this->kalkulierePreis();
    }

    private function kalkulierePreis() {
		$this->Kosten = $this->Stundenlohn * ($this->ZeitInMinuten / 60);
        return $this->Kosten;
    }

}

?>