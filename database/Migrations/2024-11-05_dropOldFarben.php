<?php

return new class {

    private $queries = [
        "DROP TABLE IF EXISTS `farben`",
        "DROP TABLE IF EXISTS `farben_auftrag`",
    ];

    public function getQueries() {
        return $this->queries;
    }

};
