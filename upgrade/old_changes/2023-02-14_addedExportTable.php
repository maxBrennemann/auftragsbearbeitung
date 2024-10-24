<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "CREATE TABLE `module_sticker_exports` (`idSticker` INT NOT NULL , `facebook` INT NULL , `google` INT NULL , `amazon` INT NULL , `etsy` INT NULL , `ebay` INT NULL , `pinterest` INT NULL ) ENGINE = InnoDB;",
        "ALTER TABLE `module_sticker_exports` ADD PRIMARY KEY(`idSticker`);",
        "ALTER TABLE `module_sticker_exports` ADD FOREIGN KEY (`idSticker`) REFERENCES `module_sticker_sticker_data`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DROP TABLE `module_sticker_exports`");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>