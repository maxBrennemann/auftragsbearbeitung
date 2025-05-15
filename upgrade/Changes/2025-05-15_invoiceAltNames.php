<?php

return new class {

    private $queries = [
        "CREATE TABLE `invoice_alt_names` (`id` INT NOT NULL AUTO_INCREMENT , `id_invoice` INT NOT NULL , `text` VARCHAR(128) NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;"
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
