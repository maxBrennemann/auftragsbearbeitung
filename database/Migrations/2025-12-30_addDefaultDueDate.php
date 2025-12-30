<?php

return new class {

    private $queries = [
        "INSERT INTO `settings` (`id`, `title`, `content`, `defaultValue`, `isBool`, `isNullable`) VALUES (NULL, 'companyDueDate', '0', '0', '0', '1');",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
