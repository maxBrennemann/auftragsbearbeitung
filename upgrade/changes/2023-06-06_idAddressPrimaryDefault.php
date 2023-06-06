<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `kunde` CHANGE `id_address_primary` `id_address_primary` INT(11) NULL DEFAULT NULL;
        ",
        "ALTER TABLE `kunde` CHANGE `Website` `Website` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '';
        ",
        "ALTER TABLE `address` CHANGE `art` `art` INT(11) NOT NULL DEFAULT '1';
        ",
        "ALTER TABLE `kunde_extended` CHANGE `Faxnummer` `Faxnummer` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
        ",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>