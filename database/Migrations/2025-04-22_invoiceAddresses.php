<?php

return new class {

    private $queries = [
        "ALTER TABLE `invoice` ADD `contact_id` INT NULL AFTER `order_id`, ADD `address_id` INT NULL AFTER `contact_id`;",
        "DROP TABLE IF EXISTS keywords;",
        "DROP TABLE IF EXISTS header;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
