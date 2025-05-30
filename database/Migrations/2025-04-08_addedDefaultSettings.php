<?php

return new class {

    private $queries = [
        "INSERT INTO `settings` (`id`, `title`, `content`, `defaultValue`, `isBool`, `isNullable`) VALUES (NULL, 'companyName', 'Auftragsbearbeitung', 'Auftragsbearbeitung', '0', '0');",
    ];

    public function getQueries() {
        return $this->queries;
    }

};
