<?php

namespace Classes\Cron;

use Classes\Cron\Schedule;

class CronManager
{

    public static function schedule(Schedule $schedule)
    {
        $schedule->runEveryHour([]);
        $schedule->runEveryDay([]);
    }
}
