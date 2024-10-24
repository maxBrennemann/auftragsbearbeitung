<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `module_sticker_changelog` ADD `rowId` INT NOT NULL AFTER `type`;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("ALTER TABLE module_sticker_changelog DROP COLUMN rowId;");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>