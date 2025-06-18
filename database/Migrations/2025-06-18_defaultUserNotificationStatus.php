<?php

return new class {

    private $queries = [
        "ALTER TABLE `user_notifications` CHANGE `ischecked` `ischecked` TINYINT(1) NOT NULL DEFAULT '0';",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
