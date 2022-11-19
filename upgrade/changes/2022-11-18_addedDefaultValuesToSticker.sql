ALTER TABLE `module_sticker_sticker_data` CHANGE `category` `category` VARCHAR(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `module_sticker_sticker_data` CHANGE `is_plotted` `is_plotted` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `module_sticker_sticker_data` CHANGE `is_short_time` `is_short_time` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `module_sticker_sticker_data` CHANGE `is_long_time` `is_long_time` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `module_sticker_sticker_data` CHANGE `is_walldecal` `is_walldecal` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `module_sticker_sticker_data` CHANGE `is_multipart` `is_multipart` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `module_sticker_sticker_data` CHANGE `is_shirtcollection` `is_shirtcollection` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `module_sticker_sticker_data` CHANGE `sizeid` `sizeid` INT NOT NULL DEFAULT '0';