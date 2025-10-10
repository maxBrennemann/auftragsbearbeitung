<?php

namespace Src\Classes\Cron\Tasks;

use Src\Classes\Cron\Queueable;
use Src\Classes\Sticker\Imports\ImportGoogleSearchConsole;

class ImportStats implements Queueable
{
    public static function handle(): void
    {
        ImportGoogleSearchConsole::import();
    }
}
