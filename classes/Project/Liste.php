<?php

namespace Classes\Project;

class Liste {

  private $listenpunkte = array();
  private $name = "";
  private $zugehoerigkeit = "";
  private $listid = 0;

  function __construct($name, $zugehoerigkeit, $listid) {
    $this->name = $name;
    $this->listid = $listid;
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

  /**
   * https://stackoverflow.com/questions/528445/is-there-any-way-to-return-html-in-a-php-function-without-building-the-return
   */
  public function toHTML($loaddata = null) {
    /* checks if it has to use the data or if the form can be empty */
    if ($loaddata != null) {
      $data = $this->loadData($loaddata);
    }

    ob_start();
    ?>
      <div class="defCont">
        <form class="listen" id="liste-<?=$this->listid?>">
        <h3><u><?=$this->getName();?></u></h3>
        <?php foreach ($this->listenpunkte as $lp): ?>
          <h4><?=$lp->getTitle()?></h4>
          <div class="innerDefCont">
          <?php foreach ($lp->getListenauswahl() as $la):
            $insType = "";
            $label = "<label for=\"{$la->getBezeichnung()}\">{$la->getBezeichnung()}</label>";
            $type3 = "";
            $typenot3 = "";
            $checked = "";
            switch ($lp->getType()) {
              case 1:
                $insType = "radio";
                $typenot3 = $label;
                /*
                 * rework that later,
                 * gets the correct data row and then sets the radio button to checked,
                 * must be done later by an extra function or by using the la and lp data types
                 */
                if ($loaddata != null) {
                  if ($this->getDataByKey($data, $la->getOrdnung())) {
                    $checked = "checked";
                  } else {
                    $checked = "";
                  }
                }
              break;
              case 2:
                $insType = "checkbox";
                $typenot3 = $label;
                /*
                 * rework that later,
                 * gets the correct data row and then sets the checkbox to checked,
                 * must be done later by an extra function or by using the la and lp data types
                 */
                if ($loaddata != null) {
                  if ($this->getDataByKey($data, $la->getOrdnung())) {
                    $checked = "checked";
                  } else {
                    $checked = "";
                  }
                }
              break;
              case 3:
                $insType = "text";
                $type3 = $label;
                /*
                 * rework that later,
                 * gets the correct data row and then sets the la bezeichnung to the value from the db,
                 * must be done later by an extra function or by using the la and lp data types
                 */
                if ($loaddata != null) {
                  if ($d = $this->getDataByKey($data, $la->getOrdnung())) {
                    $la->setBezeichnung($d["info"]);
                  }
                }
              break;
            }
          ?><?=$type3?>
            <input name="<?=$la->getOrdnung()?>" value="<?=$la->getBezeichnung()?>" type="<?=$insType?>" <?=$checked?>>
            <?=$typenot3?>
          <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
        </form>
      </div>
    <?php
    return ob_get_clean();
  }

  /*
   * the function loads all the stored data from the table by using the order id as the key
   */
  public function loadData($id) {
    $query = "SELECT lid, art, info FROM listendata WHERE orderid = $id";
    return DBAccess::selectQuery($query);
  }

  /*
   * the function iterates over all selected rows and returns the one with the right key,
   * if no row matches the search, the function returns null
   */
  public function getDataByKey($data, $key) {
    foreach ($data as $d) {
      if ((int) $d['lid'] == $key) {
        return $d;
      }
    }
    return null;
  }

  public static function getAllListPrevs() {
    $listIds = DBAccess::selectQuery("SELECT id, `name` FROM liste");
    $data = "";
    $pageLink = Link::getPageLink("listmaker");

    foreach ($listIds as $lid) {
      $id = $lid['id'];
      $name = $lid['name'];
      $data .= "<div class=\"defCont\"><a href=\"$pageLink?lid=$id\">$name</a></div>";
    }

    return $data;
  }

  public static function chooseList() {
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

    $list = new Liste($query[0]['name'], "", $query[0]['listenid']);
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

    $liste = new Liste($arr["name"], '', 0);

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

  /* 
   * function for saving a list with filled in data,
   * checks if data already existed and creates the new rows or updates it accordingly
   */
  public static function storeListData($lnr, $lid, $art, $info, $orderId) {
    $checkExists = DBAccess::selectQuery("SELECT lnr, art FROM listendata WHERE lid = $lid");
    if (empty($checkExists)) {
      DBAccess::insertQuery("INSERT INTO listendata (lnr, lid, art, info, orderid) VALUES ($lnr, $lid, $art, '$info', $orderId)");
    } else {
      if ((int) $checkExists[0]["art"] == 3) {
        DBAccess::updateQuery("UPDATE listendata SET info = '$info' WHERE lid = $lid");
      } else {
        DBAccess::updateQuery("DELETE FROM listendata WHERE lid = $lid");
      }
    }
  }

  /* function for loading data from db */
  public function loadListData() {

  }

}

/* Listendaten speichern:

listenauswahl.id speichern für listenpunkt typ radio
listenauswahl.id mit notitz speichern in tabelle für text
listenauswahl.id true false für checked

 <!-- input texfeld options: email, number, tel, url -->
        <!-- input field ideas: color, date, datetime-local, file, range, search, time -->
        <!-- other interaction types: textarea, link (button), download (file), table -->

*/
