<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "INSERT INTO `color` (`Hersteller`, `Bezeichnung`, `Farbe`, `Farbwert`)
        VALUES
          ('klebefux', '70', 'schwarz', '000000'),
          ('klebefux', '60', 'gelb', 'FFFF00'),
          ('klebefux', '67', 'rot', 'FF0000'),
          ('klebefux', '79', 'dunkelblau', '00008B'),
          ('klebefux', '91', 'grün', '008000'),
          ('klebefux', '107', 'grau', '808080'),
          ('klebefux', '111', 'weiß', 'FFFFFF'),
          ('klebefux', '225', 'silber', 'C0C0C0'),
          ('klebefux', '226', 'gold', 'FFD700'),
          ('klebefux', '74', 'pink', 'FFC0CB'),
          ('klebefux', '68', 'hellrot', 'FF6347'),
          ('klebefux', '294', 'lichtblau', 'ADD8E6');",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DELETE FROM `color` WHERE `Hersteller` = 'klebefux'");

        return "downgraded database";
    }

}

?>