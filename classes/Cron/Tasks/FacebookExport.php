<?php

namespace Classes\Cron\Tasks;

use Classes\Cron\Queueable;
use Classes\Sticker\Exports\ExportFacebook;

class FacebookExport implements Queueable
{
    public static function handle(): void
    {
        $exportFacebook = new ExportFacebook();
        $exportFacebook->generateCSV();
    }
}
