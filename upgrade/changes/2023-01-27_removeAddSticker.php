<?php

require "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "DELETE FROM `articles` WHERE `articleURL` = 'addSticker.php';"
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