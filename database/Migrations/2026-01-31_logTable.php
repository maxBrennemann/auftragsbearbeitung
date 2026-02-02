<?php

return new class {

    private $queries = [
        "CREATE TABLE `logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `log_action` VARCHAR(100) NOT NULL,
            `log_comment` TEXT NULL,
            `additional_info` JSON NULL,
            `status` VARCHAR(50) DEFAULT 'pending',
            `initiator` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`log_action`),
            INDEX (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
