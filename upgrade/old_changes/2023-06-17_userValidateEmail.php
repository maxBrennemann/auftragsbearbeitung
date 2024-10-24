<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "CREATE TABLE `user_validate_mail` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `mail_key` VARCHAR(32) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
        "ALTER TABLE `user` ADD `validated` TINYINT(1) NOT NULL DEFAULT '0' AFTER `password`;",
        "ALTER TABLE `user_devices` DROP `device_name`;",
        "ALTER TABLE `user_devices` ADD `os` VARCHAR(32) NOT NULL AFTER `user_device_name`, ADD `browser` VARCHAR(32) NOT NULL AFTER `os`, ADD `device_type` ENUM('mobile','tablet','desktop','unrecognized') NOT NULL AFTER `browser`;",
        "ALTER TABLE `user_devices` CHANGE `user_device_name` `user_device_name` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;",
        "ALTER TABLE `user_devices` CHANGE `browser_agent` `browser_agent` VARCHAR(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;",
        "",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>