<?php

return new class {

    private $queries = [
        "CREATE TABLE `pdf_texts` (`id` INT NOT NULL AUTO_INCREMENT , `type` VARCHAR(32) NOT NULL , `status` VARCHAR(32) NOT NULL , `text` TEXT NOT NULL , PRIMARY KEY (`id`));",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
