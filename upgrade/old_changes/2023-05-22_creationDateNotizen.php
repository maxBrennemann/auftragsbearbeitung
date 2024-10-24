<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `notizen` ADD `creation_date` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `Notiz`;",
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