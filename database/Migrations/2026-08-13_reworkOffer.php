<?php

return new class () {
    private $queries = [
        "ALTER TABLE `posten` ADD `offer_id` INT NULL AFTER `Auftragsnummer`;",
        "UPDATE `offer` SET `state` = 'open' WHERE `state` NOT IN ('accepted', 'rejected', 'expired');",
    ];

    public function getQueries()
    {
        return $this->queries;
    }

};
