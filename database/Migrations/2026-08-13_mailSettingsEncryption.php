<?php

return new class () {
    private $queries = [
        "ALTER TABLE `config_settings` CHANGE `content` `content` VARCHAR(512) NULL;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }

};
