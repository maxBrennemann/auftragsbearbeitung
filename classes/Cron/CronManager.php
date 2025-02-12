<?php

namespace Classes\Cron;

use Classes\Cron\Schedule;

class CronManager
{
    public static function run()
    {
        $schedule = new Schedule();

        self::scheduleTasks($schedule);
        self::execute($schedule);
    }

    protected static function scheduleTasks(Schedule $schedule)
    {
        $schedule->runEveryDay([\Classes\Cron\Tasks\CleanLogins::class, 'handle']);
    }

    protected static function execute(Schedule $schedule)
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
        }
    }
}
