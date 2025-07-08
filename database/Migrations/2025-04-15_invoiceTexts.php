<?php

return new class () {
    private $queries = [
        "CREATE TABLE `invoice_text` (`id` INT NOT NULL AUTO_INCREMENT , `id_invoice` INT NOT NULL , `text` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
