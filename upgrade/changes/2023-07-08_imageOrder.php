<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "DROP TABLE module_sticker_image_order;",
        "ALTER TABLE `module_sticker_image` ADD `image_order` INT NULL DEFAULT NULL AFTER `description`;",
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