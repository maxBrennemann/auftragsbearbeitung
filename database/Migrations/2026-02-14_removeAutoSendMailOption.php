<?php

return new class {

    private $queries = [
        "ALTER TABLE kunde DROP IF EXISTS auto_send_mail; ",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
