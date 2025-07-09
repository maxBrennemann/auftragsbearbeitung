<?php

return new class () {
    private $queries = [
        "ALTER TABLE `angebot` CHANGE `status` `status` VARCHAR(32) NULL;",
        "ALTER TABLE `angebot` ADD `order_id` INT NULL AFTER `kdnr`;",
        "ALTER TABLE `angebot` CHANGE `kdnr` `id_customer` INT(11) NOT NULL;",
        "ALTER TABLE `posten` CHANGE `Auftragsnummer` `Auftragsnummer` INT(11) NULL;",
        "ALTER TABLE `angebot` ADD `creation_date` DATETIME NOT NULL AFTER `status`, ADD `update_date` DATETIME NULL AFTER `creation_date`;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }

};
