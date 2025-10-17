<?php

namespace Src\Classes\Cron\Tasks;

use Src\Classes\Cron\Queueable;
use Src\Classes\Sticker\Exports\ExportFacebook;

class FacebookExport implements Queueable
{
    public static function handle(): void
    {
        $exportFacebook = new ExportFacebook();
        $exportFacebook->generateCSV();
    }
}
