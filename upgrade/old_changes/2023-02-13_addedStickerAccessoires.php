<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "CREATE TABLE `module_sticker_accessoires` ( `id_sticker` INT NOT NULL , `id_product` INT NOT NULL , `id_product_reference` INT NOT NULL ) ENGINE = InnoDB;",
        "ALTER TABLE `module_sticker_accessoires` ADD PRIMARY KEY(`id_product`, `id_product_reference`);"
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DROP TABLE `module_sticker_accessoires`");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>