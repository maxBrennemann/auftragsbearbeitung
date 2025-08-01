<?php

return new class {

    private $queries = [
        "ALTER TABLE `invoice` CHANGE `amount` `amount` DOUBLE NOT NULL;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
