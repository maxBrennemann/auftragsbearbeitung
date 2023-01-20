<?php

require "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "CREATE TABLE `module_sticker_log_id` (`id_changelog` INT NOT NULL , `id_sticker` INT NOT NULL) ENGINE = InnoDB;",
        "ALTER TABLE `module_sticker_log_id` ADD UNIQUE(`id_changelog`, `id_sticker`);"
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DROP TABLE `module_sticker_log_id`");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>