ALTER TABLE `module_sticker_sticker_data` ADD `creation_date` DATE NULL DEFAULT NULL AFTER `size_summary`;
ALTER TABLE `module_sticker_sticker_data` ADD `directory_name` VARCHAR(256) NULL DEFAULT NULL AFTER `creation_date`;
ALTER TABLE `module_sticker_sticker_data` ADD `additional_info` TEXT NULL DEFAULT NULL AFTER `directory_name`;
CREATE TABLE `module_sticker_texts` (`id` INT NOT NULL AUTO_INCREMENT , `id_sticker` INT NOT NULL , `type` INT NOT NULL , `content` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `module_sticker_texts` ADD FOREIGN KEY (`id_sticker`) REFERENCES `module_sticker_sticker_data`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE `module_sticker_texts` ADD `target` INT NULL DEFAULT NULL AFTER `type`;
ALTER TABLE `module_sticker_texts` CHANGE `type` `type` VARCHAR(16) NULL DEFAULT NULL;
ALTER TABLE `module_sticker_texts` ADD UNIQUE(`id_sticker`, `type`, `target`);
ALTER TABLE `module_sticker_sticker_data` CHANGE `creation_date` `creation_date` DATE NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `module_sticker_sticker_data` ADD `price_type` INT NULL DEFAULT NULL AFTER `is_shirtcollection`;
