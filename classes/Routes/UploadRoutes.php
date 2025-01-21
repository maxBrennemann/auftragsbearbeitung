<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class UploadRoutes extends Routes
{

    protected static $getRoutes = [];

    /**
     * @uses \Classes\Upload::deleteUnusedFiles()
     * @uses \Classes\Upload::adjustFileNames()
     */
    protected static $postRoutes = [
        "/upload/clear-files" => [\Classes\Upload::class, "deleteUnusedFiles"],
        "/upload/adjust-files" => [\Classes\Upload::class, "adjustFileNames"],
    ];

    protected static $putRoutes = [];

    protected static $deleteRoutes = [];
}
