<?php

return new class {

    private $queries = [
        "ALTER TABLE `invoice` CHANGE `invoice_id` `invoice_number` INT(11) NOT NULL;",
        "ALTER TABLE `invoice_text` ADD `active` BOOLEAN NOT NULL DEFAULT TRUE AFTER `text`;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
