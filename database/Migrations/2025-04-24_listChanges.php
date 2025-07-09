<?php

return new class () {
    private $queries = [
        "DROP TABLE IF EXISTS auftrag_liste;",
        "DROP TABLE IF EXISTS liste;",
        "DROP TABLE IF EXISTS listenauswahl;",
        "DROP TABLE IF EXISTS listendata;",
        "DROP TABLE IF EXISTS listenpunkt;",
        // list instance, list template, list item instance, list item template
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
