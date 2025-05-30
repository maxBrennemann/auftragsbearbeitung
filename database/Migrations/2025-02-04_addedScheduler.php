<?php

return new class {

    private $queries = [
        "CREATE TABLE `scheduler` (`id` INT NOT NULL , `taskName` VARCHAR(64) NOT NULL , `taskDescription` TEXT NOT NULL , `dayOfWeek` INT NOT NULL , `hour` INT NOT NULL , `action` VARCHAR(256) NOT NULL , `parameters` JSON NOT NULL ) ENGINE = InnoDB;",
    ];

    public function getQueries() {
        return $this->queries;
    }

};
