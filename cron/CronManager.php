<?php

use Cron\Schedule;

require_once "classes/project/modules/sticker/TextilManager.php";
require_once "classes/project/modules/sticker/StickerImageManager.php";

class CronManager {

    public static function schedule(Schedule $schedule) {
        $schedule->runEveryHour([]);
        $schedule->runEveryDay([
            new TextilManager(),
            new StickerImageManager()
        ]);

    }

}
