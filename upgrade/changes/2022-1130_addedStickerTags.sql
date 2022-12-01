CREATE TABLE `module_sticker_tags` ( `id` INT NOT NULL AUTO_INCREMENT , `content` VARCHAR(256) NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
CREATE TABLE `module_sticker_sticker_tag` ( `id_tag` INT NOT NULL , `id_sticker` INT NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `module_sticker_sticker_tag` ADD PRIMARY KEY(`id_tag`, `id_sticker`);
ALTER TABLE `module_sticker_tags` ADD `id_tag_shop` INT NOT NULL AFTER `id`;
ALTER TABLE `module_sticker_tags` CHANGE `id_tag_shop` `id_tag_shop` INT(11) NULL DEFAULT NULL;
