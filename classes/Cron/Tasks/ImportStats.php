<?php

namespace Classes\Cron\Tasks;

use Classes\Cron\Queueable;

use Classes\Project\Modules\Sticker\Imports\ImportGoogleSearchConsole;

class ImportStats implements Queueable
{

    public static function handle()
    {
        ImportGoogleSearchConsole::import();
    }
}
