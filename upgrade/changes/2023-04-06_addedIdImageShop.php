<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `module_sticker_image` ADD `id_image_shop` INT NULL AFTER `id_product`;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        /* TODO: downgrade schreiben */
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>