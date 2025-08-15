<?php

return new class {

    private $queries = [
        "DROP VIEW `auftragssumme`;",
        "DROP VIEW `auftragssumme_view`;",
        "DROP VIEW `postendata`;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
