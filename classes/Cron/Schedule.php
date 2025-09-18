<?php

namespace Classes\Cron;

class Schedule
{
    protected array $tasks = [
        "hourly" => [],
        "daily" => [],
        "exact_hour" => []
    ];

    public function runEveryMinute(callable $task): void
    {
        $this->tasks["every_minute"][] = $task;
    }

    public function runEveryHour(callable $task): void
    {
        $this->tasks["hourly"][] = $task;
    }

    public function runEveryDay(callable $task): void
    {
        $this->tasks["daily"][] = $task;
    }

    public function runAtHour(int $hour, callable $task): void
    {
        $this->tasks["exact_hour"][$hour][] = $task;
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }
}
