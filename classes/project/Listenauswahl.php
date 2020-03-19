<?php

require_once('classes/DBAccess.php');

class Listenauswahl {
    
    private $bezeichnung;
    private $isChecked;
    private $text;
    private $ordnung;

    function __construct($bezeichnung, $ordnung) {
        $this->bezeichnung = $bezeichnung;
        $this->ordnung = $ordnung;
    }

    public function saveList($listenpunktid) {
        DBAccess::insertQuery("INSERT INTO listenauswahl (listenpunktid, `bezeichnung`, ordnung) VALUES ($listenpunktid, '{$this->bezeichnung}', {$this->ordnung})");
    }
}

?>