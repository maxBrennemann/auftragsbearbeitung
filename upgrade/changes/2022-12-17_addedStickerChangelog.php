<?php

require "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "CREATE TABLE `module_sticker_changelog` (`id` INT NOT NULL AUTO_INCREMENT , `id_sticker` INT NOT NULL , `type` INT NOT NULL , `table` VARCHAR(64) NOT NULL , `column` VARCHAR(32) NOT NULL , `newValue` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;"
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DROP TABLE `module_sticker_changelog`");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>