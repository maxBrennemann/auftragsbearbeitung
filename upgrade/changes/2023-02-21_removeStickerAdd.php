<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "DELETE FROM articles WHERE src = 'sticker-add'",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("INSERT INTO articles (src) VALUES ('sticker-add')");
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>