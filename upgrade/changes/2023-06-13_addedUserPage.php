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
        "ALTER TABLE `user` CHANGE `username` `username` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;",
        "ALTER TABLE `user` CHANGE `password` `password` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;",
        "ALTER TABLE `user` CHANGE `max_working_hours` `max_working_hours` INT(11) NULL DEFAULT '0';",
        "INSERT INTO user (user_id, lastname, prename, username, email, `password`, role, max_working_hours)
        SELECT m.id, m.Nachname, m.Vorname, me.username, m.Email, me.password, 0, me.maxWorkingHours
        FROM mitarbeiter m LEFT JOIN members_mitarbeiter mm ON m.id = mm.id_mitarbeiter LEFT JOIN members me ON mm.id_member = me.id",
        "CREATE TABLE `user_roles` ( `id` INT NOT NULL AUTO_INCREMENT , `role_name` VARCHAR(64) NOT NULL , `role_description` TINYTEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;",
        "INSERT INTO login_history (id, user_id, loginstamp, user_login_key_id) SELECT ll.id, ll.id_member, ll.loginstamp, 0 FROM last_login ll;",
        "DROP TABLE members",
        "DROP TABLE members_mitarbeiter",
        "DROP TABLE mitarbeiter",
        "ALTER TABLE `user` CHANGE `user_id` `id` INT(11) NOT NULL AUTO_INCREMENT;",
        "ALTER TABLE `user_login_key` ADD `expiration_date` DATE NOT NULL AFTER `login_key`;",
        "DROP TABLE last_login;",
        "DROP TABLE user_login;",
        "UPDATE history SET member_id = 11 WHERE member_id = 1",
        "UPDATE history SET member_id = 12 WHERE member_id = 2",
        "UPDATE history SET member_id = 13 WHERE member_id = 3",
        "UPDATE history SET member_id = 1 WHERE member_id = 12",
        "UPDATE history SET member_id = 2 WHERE member_id = 11",
        "UPDATE history SET member_id = 4 WHERE member_id = 13",
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