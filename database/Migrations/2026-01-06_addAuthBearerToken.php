<?php

return new class {

    private $queries = [
        "CREATE TABLE `access_tokens` (
            `id` INT NOT NULL AUTO_INCREMENT , 
            `name` VARCHAR(32) NOT NULL , 
            `token_hash` CHAR(64) NOT NULL , 
            `is_active` BOOLEAN NOT NULL , 
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
            `last_used` TIMESTAMP NULL , 
            PRIMARY KEY (`id`)
        ) ENGINE = InnoDB;",
        "ALTER TABLE `settings` CHANGE `json_content` `json_content` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
