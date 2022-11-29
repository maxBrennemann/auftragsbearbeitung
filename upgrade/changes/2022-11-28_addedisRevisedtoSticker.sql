ALTER TABLE `module_sticker_sticker_data` ADD `is_revised` INT NULL DEFAULT NULL AFTER `directory_name`;
ALTER TABLE `module_sticker_sticker_data` CHANGE `is_revised` `is_revised` INT(11) NULL DEFAULT '0';
ALTER TABLE `module_sticker_sticker_data` ADD `is_marked` BOOLEAN NOT NULL DEFAULT FALSE AFTER `is_revised`;
