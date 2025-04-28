<?php

return new class {

    private $queries = [
        "ALTER TABLE `dateien` CHANGE `dateiname` `dateiname` VARCHAR(80) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;",
        "ALTER TABLE `dateien` CHANGE `originalname` `originalname` VARCHAR(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;",
        "ALTER TABLE `dateien` CHANGE `date` `date` DATETIME NOT NULL;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
