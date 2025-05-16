<?php

namespace Classes\Cron\Tasks;

use Classes\Cron\Queueable;
use Classes\Models\TaskExecutions;

class UpdatePrestashop implements Queueable
{

    public static function handle() {
        // get current tasks
        $taskExecutions = new TaskExecutions();
        $tasks = $taskExecutions->read([]);

        // execute tasks

        // update responses
    }
}
