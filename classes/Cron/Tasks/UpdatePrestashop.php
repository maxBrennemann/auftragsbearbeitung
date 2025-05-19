<?php

namespace Classes\Cron\Tasks;

use Classes\Cron\Queueable;
use Classes\Models\TaskExecutions;
use Classes\Protocol;
use Classes\Sticker\StickerCollection;
use MaxBrennemann\PhpUtilities\DBAccess;

class UpdatePrestashop implements Queueable
{

    public static function handle() {
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

            Protocol::write("transfer sticker", "id: $id", "INFO");

            $response = StickerCollection::exportSticker($id, $type, $overwrite);

            DBAccess::updateQuery("UPDATE task_executions SET `status` = :status, finished_at = :finishedAt WHERE id = :id", [
                "status" => $response["status"],
                "id" => $task["id"],
                "finishedAt" => date("Y-m-d h:i:s"),
            ]);

            if ($response["status"] == "error") {
                Protocol::write("transfer sticker failed", "id: $id", "FAILURE");
            }
        }
    }
}
