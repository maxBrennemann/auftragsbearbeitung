<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "DELETE FROM articles WHERE src = 'help.php';",
        "UPDATE articles SET src = 'mitarbeiter', articleUrl = 'mitarbeiter.php' WHERE pageName = 'Mitarbeiter';",
        "CREATE TABLE `user` ( `user_id` INT NOT NULL AUTO_INCREMENT , `lastname` VARCHAR(32) NOT NULL , `prename` VARCHAR(32) NOT NULL , `email` VARCHAR(64) NOT NULL , `password` VARCHAR(128) NOT NULL , `role` INT NOT NULL , `max_working_hours` INT NOT NULL , PRIMARY KEY (`user_id`)) ENGINE = InnoDB;",
        "ALTER TABLE `user` ADD `username` VARCHAR(32) NOT NULL AFTER `prename`;",
        "CREATE TABLE `user_devices` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `md_hash` VARCHAR(64) NOT NULL , `device_name` TEXT NOT NULL , `ip_address` VARCHAR(64) NOT NULL , `browser_agent` VARCHAR(64) NOT NULL , `last_usage` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , `user_device_name` VARCHAR(64) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
        "CREATE TABLE `user_login_key` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `login_key` CHAR(12) NOT NULL , `user_device_id` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
        "CREATE TABLE `login_history` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `loginstamp` DATETIME NOT NULL , `user_login_key_id` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
        "INSERT INTO user (user_id, lastname, prename, username, email, `password`, role, max_working_hours)
        SELECT m.id, m.Vorname, m.Nachname, me.username, m.Email, me.password, 0, me.maxWorkingHours
        FROM mitarbeiter m LEFT JOIN members_mitarbeiter mm ON m.id = mm.id_mitarbeiter LEFT JOIN members me ON mm.id_member = me.id",
        "CREATE TABLE `user_roles` ( `id` INT NOT NULL AUTO_INCREMENT , `role_name` VARCHAR(64) NOT NULL , `role_description` TINYTEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
        "INSERT INTO user_login_key (id, user_id, login_key, user_device_id) SELECT ul.id, ul.user_id, ",
        "INSERT INTO login_history (id, user_id, loginstamp, user_login_key_id) FROM",
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