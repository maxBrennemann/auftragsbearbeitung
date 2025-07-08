<?php

return new class () {
    private $queries = [
        "DROP TABLE IF EXISTS frontpage;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
