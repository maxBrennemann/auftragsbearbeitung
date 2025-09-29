<?php

namespace Classes\Cron\Tasks;

use Classes\Cron\Queueable;
use Classes\Models\TaskExecutions;
use Classes\Protocol;
use Classes\Sticker\StickerCollection;
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
