<?php

return new class {

    private $queries = [
        "UPDATE schritte SET istAllgemein = 0;",
        "ALTER TABLE `schritte` CHANGE `istAllgemein` `assignedTo` INT(11) NOT NULL;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
