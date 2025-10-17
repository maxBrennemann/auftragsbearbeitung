<?php

namespace Src\Classes\Cron\Tasks;

use Src\Classes\Cron\Queueable;
use Src\Classes\Models\TaskExecutions;
use Src\Classes\Protocol;
use Src\Classes\Sticker\StickerCollection;
use MaxBrennemann\PhpUtilities\DBAccess;

/**
 * Syncs tags and products
 */
class SyncPrestashop implements Queueable
{
    public static function handle(): void
    {
        $taskExecutions = new TaskExecutions();
        $tasks = $taskExecutions->read([
            "status" => "scheduled",
        ]);

        foreach ($tasks as $task) {
            
        }
    }
}
