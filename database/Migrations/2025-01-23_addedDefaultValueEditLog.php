<?php

return new class () {
    private $queries = [
        "ALTER TABLE `user_timetracking` CHANGE `edit_log` `edit_log` VARCHAR(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }

};
