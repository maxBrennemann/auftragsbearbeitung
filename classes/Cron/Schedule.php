<?php

namespace Classes\Cron;

class Schedule
{

    function __construct() {}

    public function runEveryHour($tasks)
    {
        $this->runTasks($tasks, 3600);
    }

    public function runEveryDay($tasks)
    {
        $this->runTasks($tasks, 86400);
    }

    private function runTasks($tasks, $interval)
    {
        foreach ($tasks as $task) {
            $task->run();
        }
    }
}
