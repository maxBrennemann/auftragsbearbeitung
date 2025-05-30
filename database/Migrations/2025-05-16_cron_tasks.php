<?php

return new class {

    private $queries = [
        "DROP TABLE IF EXISTS crons;",
        "DROP TABLE IF EXISTS scheduler;",
        "CREATE TABLE `task_executions` (`id` INT NOT NULL AUTO_INCREMENT , `job_name` VARCHAR(100) NOT NULL , `status` VARCHAR(32) NOT NULL , `result` TEXT NULL , `started_at` DATETIME NOT NULL , `finished_at` DATETIME NULL , `metadata` JSON NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
