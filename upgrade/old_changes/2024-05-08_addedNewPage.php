<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "INSERT INTO articles (articleUrl, pageName, src) VALUES ('test.php', 'Test', 'testpage');",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::deleteQuery("DELETE FROM `articles` WHERE `articleUrl` = 'test.php'");

        return "downgraded database";
    }

}

?>