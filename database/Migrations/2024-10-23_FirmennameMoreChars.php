<?php

return new class {

    private $queries = [
        "ALTER TABLE `kunde` CHANGE `Firmenname` `Firmenname` VARCHAR(256) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;",
    ];

    public function getQueries() {
        return $this->queries;
    }

};
