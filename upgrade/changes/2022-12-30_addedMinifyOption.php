<?php

require "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "INSERT INTO settings (title, content) VALUES ('minifyStatus', 'on');",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DELETE FROM settings WHERE title = 'minifyStatus'");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>