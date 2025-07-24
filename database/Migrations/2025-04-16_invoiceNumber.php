<?php

return new class () {
    private $queries = [
        "ALTER TABLE invoice DROP INDEX invoice_id_2;",
        /* this was not a clean solution - maybe some entries got finalized even though they are not finished? */
        "UPDATE invoice SET `status` = 'finalized';",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
