<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE produkt_attribute_to_attribute RENAME TO product_attribute_combination;",
        "ALTER TABLE produkt_attribute RENAME TO product_combination;",
        "ALTER TABLE `module_sticker_sticker_data` DROP `price_type`;",
        "ALTER TABLE `produkt` ADD `id_category` INT NULL AFTER `einkaufs_id`;",
        "CREATE TABLE `module_sticker_textiles` (`id` INT NOT NULL AUTO_INCREMENT , `id_module_textile` INT NOT NULL , `id_product` INT NOT NULL , `activated` BOOLEAN NOT NULL DEFAULT FALSE , `price` INT NOT NULL DEFAULT '0' , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
        "DELETE FROM category;",
        "INSERT INTO `category` (`id`, `name`, `parent`) VALUES ('1', 'Start', '0'), ('2', 'Textilien', '1');",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::insertQuery("ALTER TABLE product_attribute_combination RENAME TO produkt_attribute_to_attribute;");
        DBAccess::insertQuery("ALTER TABLE product_combination RENAME TO produkt_attribute;");
        DBAccess::insertQuery("ALTER TABLE `module_sticker_sticker_data` ADD `price_type` INT(1) NOT NULL DEFAULT '0';");
        DBAccess::insertQuery("ALTER TABLE `produkt` DROP `id_category`;");
        DBAccess::insertQuery("DROP TABLE `module_sticker_textiles`;");

        return "downgraded database";
    }

}

?>