<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `notizen` CHANGE `Nummer` `id` INT(11) NOT NULL AUTO_INCREMENT;",
        "ALTER TABLE `notizen` CHANGE `Auftragsnummer` `orderId` INT(11) NOT NULL;",
        "ALTER TABLE `notizen` CHANGE `Notiz` `note` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL;",
        "ALTER TABLE `notizen` ADD `title` VARCHAR(128) NOT NULL AFTER `orderId`;",
        "ALTER TABLE notizen RENAME TO notes;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::executeQuery("ALTER TABLE notes RENAME TO notizen;");
        DBAccess::executeQuery("ALTER TABLE `notizen` DROP `title`;");
        DBAccess::executeQuery("ALTER TABLE `notizen` CHANGE `note` `Notiz` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL;");
        DBAccess::executeQuery("ALTER TABLE `notizen` CHANGE `orderId` `Auftragsnummer` INT(11) NOT NULL;");
        DBAccess::executeQuery("ALTER TABLE `notizen` CHANGE `id` `Nummer` INT(11) NOT NULL AUTO_INCREMENT;");

        return "downgraded database";
    }

}

?>