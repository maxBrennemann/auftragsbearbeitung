<?php

namespace Classes\Cron\Tasks;

use Classes\Cron\Queueable;

use Classes\Project\Modules\Sticker\Exports\ExportFacebook;

class FacebookExport implements Queueable
{

    public static function handle()
    {
        $exportFacebook = new ExportFacebook();
        $exportFacebook->generateCSV();
    }
}
