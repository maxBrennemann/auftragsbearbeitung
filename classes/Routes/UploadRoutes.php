<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class UploadRoutes extends Routes
{
    protected static $getRoutes = [];

    /**
     * @uses \Classes\Project\UploadHandler::deleteUnusedFiles()
     * @uses \Classes\Project\UploadHandler::migrateFiles()
     */
    protected static $postRoutes = [
        "/upload/clear-files" => [\Classes\Project\UploadHandler::class, "deleteUnusedFiles"],
        "/upload/adjust-files" => [\Classes\Project\UploadHandler::class, "migrateFiles"],
    ];

    protected static $putRoutes = [];

    protected static $deleteRoutes = [];
}
