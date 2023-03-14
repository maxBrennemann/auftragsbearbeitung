<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "CREATE TABLE `module_sticker_sticker_tag_group` ( `id` INT NOT NULL AUTO_INCREMENT , `title` VARCHAR(64) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
        "CREATE TABLE `module_sticker_sticker_tag_group_match` ( `idGroup` INT NOT NULL , `idTag` INT NOT NULL ) ENGINE = InnoDB;",
        "ALTER TABLE `module_sticker_sticker_tag_group_match` ADD PRIMARY KEY(`idGroup`, `idTag`);"
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DROP TABLE `module_sticker_sticker_tag_group`;");
        DBAccess::deleteQuery("DROP TABLE `module_sticker_sticker_tag_group_match`;");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>