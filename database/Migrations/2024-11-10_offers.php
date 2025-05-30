<?php

return new class {

    private $queries = [
        "CREATE TABLE `offer` (`id` INT NOT NULL AUTO_INCREMENT , `customer_id` INT NOT NULL , `creation_date` DATETIME NULL , `state` VARCHAR(32) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
        "INSERT INTO offer (customer_id, state)
        SELECT kdnr,
            CASE 
                WHEN `status` = 0 THEN 'created'
                ELSE 'undefined'
            END AS state
        FROM angebot",
    ];

    public function getQueries() {
        return $this->queries;
    }

};
