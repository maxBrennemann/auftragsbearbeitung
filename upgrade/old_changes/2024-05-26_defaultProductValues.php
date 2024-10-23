<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `produkt` CHANGE `Bild` `Bild` VARCHAR(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        return "downgraded database";
    }

}

?>