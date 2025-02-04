<?php

namespace Classes\Cron;

class Schedule
{
    protected array $tasks = [
        "hourly" => [],
        "daily" => [],
        "exact_hour" => []
    ];

    public function runEveryHour(callable $task)
    {
        $this->tasks["hourly"][] = $task;
    }

    public function runEveryDay(callable $task)
    {
        $this->tasks["daily"][] = $task;
    }

    public function runAtHour(int $hour, callable $task)
    {
        $this->tasks["exact_hour"][$hour][] = $task;
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }
}
