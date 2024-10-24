<?php

require_once "UpdateMySql.php";

return new class extends UpdateMySql {

    private $queries = [
        "DELETE FROM dateien WHERE dateien.id IN (SELECT id_datei FROM dateien_motive);",
        "CREATE TABLE `module_sticker_image` ( `id_datei` INT NOT NULL , `id_motiv` INT NOT NULL , `image_sort` ENUM('general','aufkleber','wandtattoo','textil') NOT NULL , `id_product` INT NULL ) ENGINE = InnoDB;",
        "ALTER TABLE `module_sticker_image` ADD PRIMARY KEY(`id_datei`);",
        "ALTER TABLE `module_sticker_image` ADD CONSTRAINT `ref_dateien` FOREIGN KEY (`id_datei`) REFERENCES `dateien`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;",
        "CREATE TABLE `module_sticker_image_order` ( `id_position` INT NOT NULL AUTO_INCREMENT , `id_datei` INT NOT NULL , `position_number` INT NOT NULL , PRIMARY KEY (`id_position`)) ENGINE = InnoDB;",
        "DROP TABLE dateien_motive;",
        "DROP TABLE module_sticker_images;",
        "ALTER TABLE `module_sticker_image` CHANGE `image_sort` `image_sort` ENUM('general','aufkleber','wandtattoo','textil','textilsvg') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;",
    ];

    public function upgrade() {
        foreach ($this->queries as $query) {
            DBAccess::insertQuery($query);
        }
        
        return "upgraded database";
    }

    public function downgrade() {
        /* TODO: downgrade schreiben */
    }

    /* testing if anonymous class is accessible */
    public function getHello() {
        return "hello";
    }

}

?>