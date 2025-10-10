<?php

namespace Src\Classes\Cron;

class Schedule
{

    /** @var array{every_minute:callable[], hourly:callable[], daily:callable[], exact_hour:array<int, callable[]>} */
    protected array $tasks = [
        "every_minute" => [],
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

    /**
     * @return array{every_minute:callable[], hourly:callable[], daily:callable[], exact_hour:array<int, callable[]>}
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }
}
