<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class UploadRoutes extends Routes
{
    protected static $getRoutes = [];

    /**
     * @uses \Src\Classes\Project\UploadHandler::deleteUnusedFiles()
     * @uses \Src\Classes\Project\UploadHandler::migrateFiles()
     */
    protected static $postRoutes = [
        "/upload/clear-files" => [\Src\Classes\Project\UploadHandler::class, "deleteUnusedFiles"],
        "/upload/adjust-files" => [\Src\Classes\Project\UploadHandler::class, "migrateFiles"],
    ];

    protected static $putRoutes = [];

    protected static $deleteRoutes = [];
}
