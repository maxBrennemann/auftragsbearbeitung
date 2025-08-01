<?php

return new class {

    private $queries = [
        "ALTER TABLE `auftrag` ADD `status` VARCHAR(32) NOT NULL DEFAULT 'default' AFTER `archiviert`;",
        "UPDATE auftrag
        SET `status` = CASE
            WHEN Rechnungsnummer != 0 THEN 'invoiced'
            WHEN archiviert = 1 THEN 'default'
            WHEN archiviert = -1 THEN 'finished'
            ELSE 'archived'
        END;",
        "ALTER TABLE `auftrag` DROP `archiviert`;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
