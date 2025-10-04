<?php

namespace Src\Classes\Project;

use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class FileStats
{

    /**
     * @param string $path
     * @param array{count:int, size:float} $data
     * @return void
     */
    private static function getFilesInfoByPath(string $path, array &$data): void
    {
        $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            if ($file->getFilename() === ".gitkeep") {
                continue;
            }

            $data["count"] += 1;
            $data["size"] += $file->getSize();
        }
    }

    public static function getFilesInfo(): void
    {
        $paths = [
            "upload/",
            "storage/generated/",
        ];
        $data = [
            "count" => 0,
            "size" => 0,
        ];

        foreach ($paths as $path) {
            self::getFilesInfoByPath($path, $data);
        }

        JSONResponseHandler::sendResponse([
            "count" => $data["count"],
            "size" => ceil($data["size"] / 1000000)
        ]);
    }

}
