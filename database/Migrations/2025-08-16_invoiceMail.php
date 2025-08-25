<?php

return new class {

    private $queries = [
        "ALTER TABLE `kunde` ADD `invoice_email` VARCHAR(128) NULL AFTER `Email`;",
        "ALTER TABLE `kunde` ADD `auto_send_mail` BOOLEAN NOT NULL DEFAULT FALSE AFTER `id_address_primary`;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
