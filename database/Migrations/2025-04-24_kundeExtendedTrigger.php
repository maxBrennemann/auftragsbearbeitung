<?php

return new class {

    private $queries = [
        "DROP TRIGGER IF EXISTS createRowForNewCustomer;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
