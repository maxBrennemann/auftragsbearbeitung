<?php

return new class () {
    private $queries = [
        "ALTER TABLE `color` CHANGE `Farbe` `color_name` VARCHAR(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL;",
        "ALTER TABLE `color` CHANGE `Farbwert` `hex_value` VARCHAR(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL;",
        "ALTER TABLE `color` CHANGE `Bezeichnung` `short_name` VARCHAR(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL;",
        "ALTER TABLE `color` CHANGE `Hersteller` `producer` VARCHAR(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }

};
