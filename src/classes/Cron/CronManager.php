<?php

namespace Src\Classes\Cron;

class CronManager
{
    public static function run(): void
    {
        $schedule = new Schedule();

        self::scheduleTasks($schedule);
        self::execute($schedule);
    }

    protected static function scheduleTasks(Schedule $schedule): void
    {
        $schedule->runEveryDay([\Classes\Cron\Tasks\CleanLogins::class, "handle"]);
        $schedule->runEveryMinute([\Classes\Cron\Tasks\UpdatePrestashop::class, "handle"]);
    }

    protected static function execute(Schedule $schedule): void
    {
        $now = date('H:i');
        $currentHour = date('H');
        $currentMinute = date('i');

        foreach ($schedule->getTasks() as $type => $taskList) {
            if ($type === "hourly" && $currentMinute === '00') {
                foreach ($taskList as $task) {
                    call_user_func($task);
                }
            }

            if ($type === "daily" && $now === "00:00") {
                foreach ($taskList as $task) {
                    call_user_func($task);
                }
            }

            if ($type === "exact_hour" && isset($taskList[$currentHour])) {
                foreach ($taskList[$currentHour] as $task) {
                    call_user_func($task);
                }
            }

            if ($type === "every_minute") {
                foreach ($taskList as $task) {
                    call_user_func($task);
                }
            }
        }
    }
}
