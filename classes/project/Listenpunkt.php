<?php

require_once('classes/project/Listenauswahl.php');

class Listenpunkt {

    private $listenauswahl = array();
    private $text;
    private $ordnung;
    private $art;

    function __construct($text, $art, $ordnung) {
        $this->text = $text;
        $this->art = $art;
        $this->ordnung = $ordnung;
    }

    public function getType() {
        return (int) $this->art;
    }
    
    public function addListenAuswahl($aw) {
        array_push($this->listenauswahl, $aw);
    }

    public function getListenauswahl() {
        return $this->listenauswahl;
    }

    public function removeListenAuswahl() {
        
    }

    public function getTitle() {
        return $this->text;
    }

    public function getOrdnung() {
        return $this->ordnung;
    }

    public function saveList($listenid) {
        $id = DBAccess::insertQuery("INSERT INTO listenpunkt (`listenid`, `text`, `art`, `ordnung`) VALUES ($listenid, '{$this->text}', {$this->art}, {$this->ordnung})");
    
        foreach($this->listenauswahl as $la) {
          $la->saveList($id);
        }
    }

}
