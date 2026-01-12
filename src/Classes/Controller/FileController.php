<?php

namespace Src\Classes\Controller;

use Src\Classes\Project\Config;

class FileController
{
    public static function getPath(string $resourceName): ?string
    {
        $baseDir = Config::get('paths.uploadDir.default');
        $subDir1 = substr($resourceName, 0, 2);
        $subDir2 = substr($resourceName, 2, 2);

        $filePath = $baseDir . $subDir1 . '/' . $subDir2 . '/' . $resourceName;

        if (file_exists($filePath)) {
            return $filePath;
        }

        return null;
    }
}
