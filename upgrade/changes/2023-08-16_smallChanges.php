<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "INSERT INTO articles (articleUrl, pageName, src) VALUES ('stickerImages.php', 'Bildübersicht der Motive', 'sticker-images');",
        "ALTER TABLE invoice DROP COLUMN payment_type;",
        "ALTER TABLE `invoice` ADD COLUMN `payment_type` ENUM('unbezahlt','ueberweisung','paypal','kreditkarte','amazonpay','weiteres','bar') DEFAULT 'unbezahlt' NOT NULL;",
        "CREATE TABLE `module_sticker_categories` (`stickerId` INT NOT NULL , `categoryId` INT NOT NULL ) ENGINE = InnoDB;",
        "ALTER TABLE `module_sticker_categories` ADD PRIMARY KEY(`stickerId`, `categoryId`);",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        
    }

}

?>