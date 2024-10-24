<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $query = "ALTER TABLE `module_sticker_images` ADD `id_image_shop` INT NULL DEFAULT NULL AFTER `is_textil`, ADD `order_aufkleber` INT NULL DEFAULT NULL AFTER `id_image_shop`, ADD `order_wandtattoo` INT NULL DEFAULT NULL AFTER `order_aufkleber`, ADD `order_textil` INT NULL DEFAULT NULL AFTER `order_wandtattoo`;";

    public function upgrade() {
        DBAccess::insertQuery($this->query);
        DBAccess::insertQuery("ALTER TABLE `module_sticker_sticker_data` ADD `price_class` INT NOT NULL DEFAULT '0' AFTER `price_type`;");

        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DELETE FROM info_texte WHERE id IN (8, 9, 10");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>