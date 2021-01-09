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

  public function getName() {
    return $this->name;
  }

  public function toHTML() {
    ?>
      <div class="defCont">
        <h3><u><?=$this->getName();?></u></h3>
        <?php foreach ($this->listenpunkte as $lp): ?>
          <h4><?=$lp->getTitle()?></h4>
          <div class="innerDefCont">
          <?php foreach ($lp->getListenauswahl() as $la):
            $insType = "";
            $label = "<label for=\"{$la->getBezeichnung()}\">{$la->getBezeichnung()}</label>";
            $type3 = "";
            $typenot3 = "";
            switch ($lp->getType()) {
              case 1:
                $insType = "radio";
                $typenot3 = $label;
              break;
              case 2:
                $insType = "checkbox";
                $typenot3 = $label;
              break;
              case 3:
                $insType = "text";
                $type3 = $label;
              break;
            }
          ?><?=$type3?>
            <input name="<?=$lp->getOrdnung()?>" value="<?=$la->getBezeichnung()?>" type="<?=$insType?>">
            <?=$typenot3?>
          <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php
  }

  public static function getAllListPrevs() {
    $lists = array();
    $listIds = DBAccess::selectQuery("SELECT id, name FROM liste");

    $data = "";
    $pageLink = Link::getPageLink("listmaker");
    foreach ($listIds as $lid) {
      $data .= "<div class=\"defCont\"><a href=\"{$pageLink}?lid={$lid['id']}\">{$lid['name']}</a></div>";
    }

    return $data;
  }

  public static function chooseList() {
    $lists = array();
    $listIds = DBAccess::selectQuery("SELECT id, name FROM liste");

    $data = "";
    $pageLink = Link::getPageLink("listmaker");
    foreach ($listIds as $lid) {
      $data .= "<div class=\"innerDefCont\">{$lid['name']} <button onclick=\"chooseList({$lid['id']});\">Auswählen</button></div>";
    }

    return $data;
  }

  /*
  * readList reads all listenauswahl and listenpoint elements from database which are connected to the list id;
  * then every line of the result is read; when its id occures already in an array, only the listenauswahl will be created;
  * otherwise a listenpunkt element will also be created
  */
  public static function readList($listid) {
    $data = array();
    $query = "SELECT liste.id as listenid, liste.name, listenpunkt.id as listenpunktid, listenpunkt.text, listenauswahl.id as listenauswahlid, listenauswahl.bezeichnung, listenpunkt.art FROM `liste`, listenpunkt, listenauswahl where liste.id = listenpunkt.listenid and listenpunkt.id = listenauswahl.listenpunktid and liste.id = $listid";
    $query = DBAccess::selectQuery($query);

    $list = new Liste($query[0]['name'], "");
    $listenpunkte = array();
    foreach ($query as $entry) {
      if (!in_array((int) $entry['listenpunktid'], $listenpunkte)) {
        $text = $entry['text'];
        $art = $entry['art'];
        $ordnung = $entry['listenpunktid'];
        $lp = new Listenpunkt($text, $art, $ordnung);

        $bezeichnung = $entry['bezeichnung'];
        $ordnung = $entry['listenauswahlid'];
        $la = new Listenauswahl($bezeichnung, $ordnung);

        $lp->addListenAuswahl($la);
        $list->addListenPunkt($lp);

        array_push($listenpunkte, (int) $entry['listenpunktid']);
      } else {
        $id = (int) $entry['listenpunktid'];

        $bezeichnung = $entry['bezeichnung'];
        $ordnung = $entry['listenauswahlid'];
        $la = new Listenauswahl($bezeichnung, $ordnung);

        foreach ($list->listenpunkte as $lp) {
          if ((int) $lp->getOrdnung() == $id) {
            $lp->addListenAuswahl($la);
          }
        }
      }
    }

    return $list;
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

  /* function for saving a list with filled in data */
  public function storeListData() {

  }

  /* function for loading data from db */
  public function loadListData() {

  }

}

/* Listendaten speichern:

listenauswahl.id speichern für listenpunkt typ radio
listenauswahl.id mit notitz speichern in tabelle für text
listenauswahl.id true false für checked

*/

?>