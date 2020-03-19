<?php

require_once('classes/DBAccess.php');
require_once('classes/project/Listenpunkt.php');
require_once('classes/project/Listenauswahl.php');

class Liste {

  private $listenpunkte = array();
  private $name = "";
  private $zugehoerigkeit = "";

  function __construct($name, $zugehoerigkeit) {
    $this->name = $name;
  }
  
  public function createList() {

  }

  public function addListenPunkt($lp) {
    array_push($this->listenpunkte, $lp);
  }

  public function removeListenPunkt() {

  }

  public static function saveData($data) {
    $arr = json_decode($data, true);

    $liste = new Liste($arr["name"], '');

    foreach ($arr["listenpunkte"] as $lp) {
      $listenpunkt = new Listenpunkt($lp["text"], $lp["type"], $lp["id"]);

      foreach ($lp["auswahl"] as $aw) {
        $auswahl = new Listenauswahl($aw["text"], $aw["ordnung"]);
        $listenpunkt->addListenAuswahl($auswahl);
      }

      $liste->addListenPunkt($listenpunkt);
    }

    $liste->saveList();
  }

  public function saveList() {
    $id = DBAccess::insertQuery("INSERT INTO liste (`name`, `zugehoerigkeit`) VALUES ('{$this->name}', '{$this->zugehoerigkeit}')");

    foreach($this->listenpunkte as $lp) {
      $lp->saveList($id);
    }
  }

}

?>