<?php

return new class {

    private $queries = [
        "UPDATE `user_notifications`SET `type` = 6 WHERE `content` LIKE '%wurde abgeschlossen%' AND  `type` = 4;",
        "UPDATE `user_notifications`SET `type` = 7 WHERE `type` = 0;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
