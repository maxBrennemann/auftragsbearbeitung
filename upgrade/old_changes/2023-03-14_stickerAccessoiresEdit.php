<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `module_sticker_accessoires` DROP PRIMARY KEY;",
        "ALTER TABLE `module_sticker_accessoires` CHANGE `id_product` `id_product` INT(11) NULL DEFAULT NULL;",
        "ALTER TABLE `module_sticker_accessoires` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);",
        "ALTER TABLE `module_sticker_accessoires` ADD `type` ENUM('aufkleber','wandtattoo','textil','') NULL DEFAULT NULL AFTER `id_sticker`;",
        "ALTER TABLE `module_sticker_accessoires` ADD `title` VARCHAR(256) NULL DEFAULT NULL AFTER `id_product`;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("ALTER TABLE settings DROP IF EXISTS `type`;");
        DBAccess::deleteQuery("ALTER TABLE settings DROP IF EXISTS id;");
        DBAccess::deleteQuery("ALTER TABLE `module_sticker_accessoires` ADD PRIMARY KEY(`id_product`, `id_product_reference`);");
        DBAccess::deleteQuery("ALTER TABLE `module_sticker_accessoires` CHANGE `id_product` `id_product` INT NOT NULL;");
        DBAccess::deleteQuery("ALTER TABLE settings DROP IF EXISTS title;");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>