<?php

return new class {

    private $queries = [
        "DROP TABLE IF EXISTS `info_texte`;",
        "ALTER TABLE `settings` ADD `isJSON` BOOLEAN NULL DEFAULT FALSE AFTER `isNullable`;",
        "ALTER TABLE `settings` ADD `json_content` JSON NOT NULL AFTER `content`;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
