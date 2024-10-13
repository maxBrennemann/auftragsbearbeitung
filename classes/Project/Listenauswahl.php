<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class Listenauswahl
{

    private $bezeichnung;
    private $isChecked;
    private $text;
    private $ordnung;

    function __construct($bezeichnung, $ordnung)
    {
        $this->bezeichnung = $bezeichnung;
        $this->ordnung = $ordnung;
    }

    public function getBezeichnung()
    {
        return $this->bezeichnung;
    }

    /*
     * temporar function to be used for setting the data when lists are read,
     * must be implemented better later
     */
    public function setBezeichnung($bez)
    {
        $this->bezeichnung = $bez;
    }

    public function getOrdnung()
    {
        return $this->ordnung;
    }

    public function saveList($listenpunktid)
    {
        DBAccess::insertQuery("INSERT INTO listenauswahl (listenpunktid, `bezeichnung`, ordnung) VALUES ($listenpunktid, '{$this->bezeichnung}', {$this->ordnung})");
    }
}
