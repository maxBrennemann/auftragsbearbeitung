CREATE TABLE `module_sticker_sticker_data` (`id` INT NOT NULL AUTO_INCREMENT , `category` VARCHAR(256) NOT NULL , `name` VARCHAR(256) NOT NULL , `is_plotted` BOOLEAN NOT NULL , `is_short_time` BOOLEAN NOT NULL , `is_long_time` BOOLEAN NOT NULL , `is_walldecal` BOOLEAN NOT NULL , `is_multipart` BOOLEAN NOT NULL , `is_shirtcollection` BOOLEAN NOT NULL , `sizeid` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
CREATE TABLE `module_sticker_sizes` (`id` INT NOT NULL AUTO_INCREMENT , `id_sticker` INT NOT NULL , `width` INT NOT NULL , `height` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
INSERT INTO `attachments` (`id`, `articleId`, `anchor`, `fileSrc`, `fileName`, `fileType`) VALUES (NULL, '28', 'head', 'tableeditor.js', '0', 'js');

ALTER TABLE module_sticker_sticker_data AUTO_INCREMENT = 428;
