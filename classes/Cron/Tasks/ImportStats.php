<?php

namespace Classes\Cron\Tasks;

use Classes\Cron\Queueable;
use Classes\Sticker\Imports\ImportGoogleSearchConsole;

class ImportStats implements Queueable
{
    public static function handle(): void
    {
        ImportGoogleSearchConsole::import();
    }
}
