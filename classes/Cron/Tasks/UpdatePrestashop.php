<?php

namespace Classes\Cron\Tasks;

use Classes\Cron\Queueable;
use Classes\Models\TaskExecutions;
use Classes\Sticker\StickerCollection;
use MaxBrennemann\PhpUtilities\DBAccess;

class UpdatePrestashop implements Queueable
{

    public static function handle() {
        // get current tasks
        $taskExecutions = new TaskExecutions();
        $tasks = $taskExecutions->read([
            "status" => "scheduled",
        ]);

        foreach ($tasks as $task) {
            $type = $task["job_name"];
            $metadata = $task["metadata"];
            $metadata = json_decode($metadata, true);

            $id = $metadata["id"];
            $type = str_replace("export_", "", $type);
            $overwrite = $metadata[$type];

            $response = StickerCollection::exportSticker($id, $type, $overwrite);

            DBAccess::updateQuery("UPDATE task_executions SET `status` = :status WHERE id = :id", [
                "status" => $response["status"],
                "id" => $task["id"],
            ]);
        }
    }
}
