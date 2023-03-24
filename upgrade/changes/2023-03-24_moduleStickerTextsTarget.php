<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `module_sticker_texts` ADD `target_temp` ENUM('aufkleber', 'wandtattoo', 'textil') NOT NULL DEFAULT 'aufkleber' AFTER `type`;",
        "UPDATE `module_sticker_texts` SET `target_temp` = CASE `target` 
            WHEN 1 THEN 'aufkleber'
            WHEN 2 THEN 'wandtattoo'
            WHEN 3 THEN 'textil'
        END;",
        "ALTER TABLE `module_sticker_texts` DROP INDEX `id_sticker`;",
        "ALTER TABLE `module_sticker_texts` DROP `target`;",
        "ALTER TABLE `module_sticker_texts` CHANGE `target_temp` `target` ENUM('aufkleber', 'wandtattoo', 'textil') NOT NULL;",
        "ALTER TABLE `module_sticker_texts` ADD UNIQUE(`id_sticker`, `type`, `target`);"
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