ALTER TABLE `module_sticker_sticker_data` ADD `size_summary` TEXT NOT NULL AFTER `sizeid`;
ALTER TABLE `module_sticker_sticker_data` CHANGE `size_summary` `size_summary` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
