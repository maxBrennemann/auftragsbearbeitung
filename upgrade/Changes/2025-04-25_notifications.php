<?php

return new class {

    private $queries = [
        "ALTER TABLE `user_notifications` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;",
        "ALTER TABLE `user_notifications` ADD deleted_at DATETIME NULL;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
