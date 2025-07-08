<?php

return new class () {
    private $queries = [
        "ALTER TABLE `user_timetracking` ADD `is_pending` BOOLEAN NULL AFTER `stopped_at`;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
