<?php

return new class {

    private $queries = [
        "INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'customerOverview.php', 'Kundenübersicht', 'customer-overview');",
    ];

    public function getQueries() {
        return $this->queries;
    }

};
