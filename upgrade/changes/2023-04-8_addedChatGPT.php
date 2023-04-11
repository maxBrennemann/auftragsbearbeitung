<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "CREATE TABLE `module_sticker_chatgpt` ( `id` INT NOT NULL AUTO_INCREMENT , `idSticker` INT NOT NULL , `creationDate` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP , `chatgptResponse` JSON NOT NULL , `textType` ENUM('short','long') NOT NULL , `additionalQuery` TEXT NOT NULL , `textStyle` ENUM('lustig','ernst','geschäftlich','traurig','für Privatkunden','motivierend','hilfreich') NOT NULL)",
        "ALTER TABLE `module_sticker_chatgpt` CHANGE `creationDate` `creationDate` DATE NOT NULL;",
        "ALTER TABLE `module_sticker_chatgpt` ADD `jsonResponse` JSON NOT NULL AFTER `chatgptResponse`;",
        "ALTER TABLE `module_sticker_chatgpt` ADD `stickerType` ENUM('aufkleber','wandtattoo','textil') NOT NULL AFTER `jsonResponse`;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DROP TABLE module_sticker_chatgpt;");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>