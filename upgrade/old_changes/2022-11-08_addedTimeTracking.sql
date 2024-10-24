INSERT INTO `articles` (`articleUrl`, `pageName`, `src`) VALUES ('timeTracking.php', 'Zeiterfassung', 'zeiterfassung');

CREATE TABLE `user_timetracking` ( `id` INT NOT NULL , `user_id` INT NOT NULL , `started_at` DATETIME NOT NULL , `stopped_at` DATETIME NOT NULL , `duration_ms` INT NOT NULL , `task` VARCHAR(128) NOT NULL , `edit_log` VARCHAR(256) NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `user_timetracking` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);