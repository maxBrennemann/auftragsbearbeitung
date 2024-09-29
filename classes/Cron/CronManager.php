<?php

namespace Classes\Cron;

use Classes\Cron\Schedule;
use Classes\Project\Modules\Sticker\TextilManager;
use Classes\Project\Modules\Sticker\StickerImageManager;

class CronManager
{

    public static function schedule(Schedule $schedule)
    {
        $schedule->runEveryHour([]);
        $schedule->runEveryDay([
            new TextilManager(),
            new StickerImageManager()
        ]);
    }
}
