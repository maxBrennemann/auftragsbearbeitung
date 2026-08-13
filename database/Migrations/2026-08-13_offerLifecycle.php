<?php

return new class () {
    private $queries = [
        "ALTER TABLE `offer` ADD `offer_number` INT NOT NULL DEFAULT 0 AFTER `state`;",
        "ALTER TABLE `offer` ADD `valid_until` DATE NULL AFTER `creation_date`;",
        "CREATE TABLE IF NOT EXISTS offer_number_tracker (
            id INT PRIMARY KEY,
            last_used_number INT
        );",
    ];

    public function getQueries()
    {
        return $this->queries;
    }

};
