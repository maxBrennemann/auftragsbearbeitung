<?php

namespace Classes\Sticker;

use Classes\Cron\Queueable;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class StickerImageManager implements Queueable
{
    public static function handle(): void {}

    public static function updateDescription(): void
    {
        $imageId = Tools::get("imageId");
        $description = Tools::get("description");

        $query = "UPDATE module_sticker_image SET `description` = :description WHERE id_datei = :id;";
        DBAccess::updateQuery($query, [
            "description" => $description,
            "id" => $imageId,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }
}
