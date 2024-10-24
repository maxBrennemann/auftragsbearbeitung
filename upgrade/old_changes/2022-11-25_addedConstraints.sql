ALTER TABLE `dateien_motive` ADD PRIMARY KEY(`id_datei`, `id_motive`);
ALTER TABLE `dateien_motive` ADD FOREIGN KEY (`id_datei`) REFERENCES `dateien`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE `module_sticker_images` ADD UNIQUE(`id_image`);
ALTER TABLE `module_sticker_images` ADD FOREIGN KEY (`id_image`) REFERENCES `dateien`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE `module_sticker_sizes` ADD `price` INT NULL DEFAULT NULL AFTER `height`;