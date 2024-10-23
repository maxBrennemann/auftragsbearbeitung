<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `settings` ADD `defaultValue` VARCHAR(64) NULL DEFAULT NULL AFTER `content`, ADD `isBool` BOOLEAN NOT NULL DEFAULT FALSE AFTER `defaultValue`, ADD `isNullable` BOOLEAN NOT NULL DEFAULT FALSE AFTER `isBool`;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("ALTER TABLE settings DROP IF EXISTS defaultValue;");
        DBAccess::deleteQuery("ALTER TABLE settings DROP IF EXISTS isBool;");
        DBAccess::deleteQuery("ALTER TABLE settings DROP IF EXISTS isNullable;");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>