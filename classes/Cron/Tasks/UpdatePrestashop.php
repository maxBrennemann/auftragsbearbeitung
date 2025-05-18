<?php

namespace Classes\Cron\Tasks;

use Classes\Cron\Queueable;
use Classes\Models\TaskExecutions;
use Classes\Sticker\StickerCollection;

class UpdatePrestashop implements Queueable
{

    public static function handle() {
        // get current tasks
        $taskExecutions = new TaskExecutions();
        $tasks = $taskExecutions->read([]);

        foreach ($tasks as $task) {
            $type = $task["job_name"];
            $metadata = $task["metadata"];
            $metadata = json_decode($metadata, true);

            $id = $metadata["id"];
            $type = str_replace("export_", "", $type);
            $overwrite = $metadata[$type];

            StickerCollection::exportSticker($id, $type, $overwrite);
        }

        // update responses
    }
}
