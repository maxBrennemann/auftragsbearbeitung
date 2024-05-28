<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE `attribute_group` ADD `position` INT NOT NULL AFTER `id`;",
        "ALTER TABLE `attribute` ADD `position` INT NOT NULL AFTER `id`;",
        "SET @a = 0; UPDATE attribute SET position = @a:=@a+1;",
        "SET @a = 0; UPDATE attribute_group SET position = @a:=@a+1;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::insertQuery("ALTER TABLE `attribute_group` DROP `position`;");
        DBAccess::insertQuery("ALTER TABLE `attribute` DROP `position`;");

        return "downgraded database";
    }

}

?>