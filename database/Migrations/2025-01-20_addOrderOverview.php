<?php

return new class () {
    private $queries = [
        "INSERT INTO `articles` (`id`, `articleUrl`, `pageName`, `src`) VALUES (NULL, 'orderOverview.php', 'Auftragsübersicht', 'order-overview');",
    ];

    public function getQueries()
    {
        return $this->queries;
    }

};
