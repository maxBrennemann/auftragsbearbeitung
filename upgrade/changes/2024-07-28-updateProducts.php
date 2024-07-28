<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "CREATE TABLE `product_image` (`id` INT NOT NULL AUTO_INCREMENT , `id_product` INT NOT NULL , `id_file` INT NOT NULL , `position` INT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        DBAccess::insertQuery("DROP TABLE `product_image`;");

        return "downgraded database";
    }

}

?>