<?php

require "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `module_sticker_sticker_data` ADD `additional_data` JSON NULL DEFAULT NULL AFTER `in_shop_textil`;",
        "ALTER TABLE `module_sticker_sticker_data` CHANGE `additional_data` `additional_data` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;",
        "ALTER TABLE module_sticker_sticker_data DROP COLUMN in_shop_aufkleber;",
        "ALTER TABLE module_sticker_sticker_data DROP COLUMN in_shop_wandtattoo;",
        "ALTER TABLE module_sticker_sticker_data DROP COLUMN in_shop_texil;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("ALTER TABLE module_sticker_sticker_data DROP COLUMN additional_data;");

        DBAccess::deleteQuery("ALTER TABLE `module_sticker_sticker_data` ADD `in_shop_aufkleber` INT NOT NULL DEFAULT '0' AFTER `additional_info`;");
        DBAccess::deleteQuery("ALTER TABLE `module_sticker_sticker_data` ADD `in_shop_wandtattoo` INT NOT NULL DEFAULT '0' AFTER `in_shop_aufkleber`;");
        DBAccess::deleteQuery("ALTER TABLE `module_sticker_sticker_data` ADD `in_shop_texil` INT NOT NULL DEFAULT '0' AFTER `in_shop_wandtattoo`;");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>