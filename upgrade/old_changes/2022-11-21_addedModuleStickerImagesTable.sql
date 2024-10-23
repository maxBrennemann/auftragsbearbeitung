CREATE TABLE `module_sticker_images` ( `id` INT NOT NULL AUTO_INCREMENT , `id_image` INT NOT NULL , `id_sticker` INT NOT NULL , `is_aufkleber` BOOLEAN NOT NULL , `is_wandtattoo` BOOLEAN NOT NULL , `is_textil` BOOLEAN NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `module_sticker_images` CHANGE `is_aufkleber` `is_aufkleber` TINYINT(1) NULL DEFAULT '0';
ALTER TABLE `module_sticker_images` CHANGE `is_wandtattoo` `is_wandtattoo` TINYINT(1) NULL DEFAULT '0';
ALTER TABLE `module_sticker_images` CHANGE `is_textil` `is_textil` TINYINT(1) NULL DEFAULT '0';