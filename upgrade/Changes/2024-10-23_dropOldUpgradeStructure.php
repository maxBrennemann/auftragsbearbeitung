<?php

return new class {

    private $queries = [
        "DROP TABLE IF EXISTS `upgrade_tracker`",
    ];

    public function getQueries() {
        return $this->queries;
    }

};
