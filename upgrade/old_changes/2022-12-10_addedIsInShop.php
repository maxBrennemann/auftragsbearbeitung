<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $query = "ALTER TABLE `module_sticker_sticker_data` ADD `in_shop_aufkleber` BOOLEAN NOT NULL DEFAULT FALSE AFTER `additional_info`, ADD `in_shop_wandtattoo` BOOLEAN NOT NULL DEFAULT FALSE AFTER `in_shop_aufkleber`, ADD `in_shop_textil` BOOLEAN NOT NULL DEFAULT FALSE AFTER `in_shop_wandtattoo`;";

    public function upgrade() {
        DBAccess::insertQuery($this->query);

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