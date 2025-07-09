<?php

return new class () {
    private $queries = [
        "ALTER TABLE invoice DROP INDEX invoice_id_2;",
        "UPDATE invoice SET `status` = 'finalized';",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
