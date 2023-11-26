<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "CREATE TABLE `auftragsbearbeitung`.`module_sticker_search_data` (`id` INT NOT NULL , `site` VARCHAR(128) NOT NULL , `date` DATE NOT NULL , `clicks` INT NOT NULL , `impressions` INT NOT NULL , `ctr` FLOAT NOT NULL , `position` INT NOT NULL ) ENGINE = InnoDB;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        return "downgraded database";
    }

}

?>