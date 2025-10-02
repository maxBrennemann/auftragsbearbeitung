<?php

namespace Src\Classes\Cron\Tasks;

use Src\Classes\Cron\Queueable;
use Src\Classes\Models\TaskExecutions;
use Src\Classes\Protocol;
use Src\Classes\Sticker\StickerCollection;
use MaxBrennemann\PhpUtilities\DBAccess;

class UpdatePrestashop implements Queueable
{
    public static function handle(): void
    {
        $taskExecutions = new TaskExecutions();
        $tasks = $taskExecutions->read([
            "status" => "scheduled",
        ]);

        foreach ($tasks as $task) {
            $type = $task["job_name"];
            $metadata = $task["metadata"];
            $metadata = json_decode($metadata, true);

            $id = $metadata["stickerId"];
            $type = str_replace("export_", "", $type);
            $overwrite = $metadata["overwrite"];

            Protocol::write("transfer $type", "id: $id, isOverwrite: $overwrite", "INFO");

            try {
                $response = StickerCollection::exportSticker($id, $type, $overwrite);
            } catch (\Exception $e) {
                $response = [
                    "status" => "error",
                    "message" => $e->getMessage(),
                ];
            }

            Protocol::write("transfer sticker", json_encode($response), "INFO");

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
