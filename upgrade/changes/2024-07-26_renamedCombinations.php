<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "ALTER TABLE produkt_attribute_to_attribute RENAME TO product_attribute_combination;",
        "ALTER TABLE produkt_attribute RENAME TO product_combination;",
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