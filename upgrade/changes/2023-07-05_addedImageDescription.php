<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `module_sticker_image` ADD `description` VARCHAR(125) NOT NULL AFTER `id_image_shop`;",
        "ALTER TABLE `module_sticker_image` CHANGE `description` `description` VARCHAR(125) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;",
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