<?php

return new class () {
    private $queries = [
        "ALTER TABLE `invoice` CHANGE `payment_type` `payment_type` VARCHAR(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'unbezahlt';",
        "ALTER TABLE `invoice` CHANGE `payment_date` `payment_date` DATE NULL;",
        "DROP TABLE IF EXISTS `invoice_items`;",
        "CREATE TABLE IF NOT EXISTS invoice_number_tracker (
            id INT PRIMARY KEY,
            last_used_number INT
        );",
        "ALTER TABLE `invoice` ADD `invoice_id` INT NOT NULL AFTER `id`;",
        "ALTER TABLE `invoice` ADD `status` VARCHAR(16) NOT NULL DEFAULT 'draft' AFTER `order_id`;",
        "ALTER TABLE `invoice` ADD `finalized_date` DATE NULL AFTER `payment_date`;",
        "ALTER TABLE `invoice` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;",
        "ALTER TABLE `invoice` ADD `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;",
        "UPDATE invoice SET invoice_id = id WHERE invoice_id = 0;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
