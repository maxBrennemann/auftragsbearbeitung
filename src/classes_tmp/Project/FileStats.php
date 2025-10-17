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
            Config::get("paths.uploadDir.default"),
            Config::get("paths.generatedDir"),
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

    public static function parseFileType(string $type): string
    {
        $type = strtolower(trim($type));

        switch ($type) {
            // --- Bilder ---
            case 'image/jpeg':
            case 'image/jpg':
            case 'jpg':
            case 'jpeg':
                return 'Bild (JPG)';

            case 'image/png':
            case 'png':
                return 'Bild (PNG)';

            case 'image/gif':
            case 'gif':
                return 'Bild (GIF)';

            case 'image/webp':
            case 'webp':
                return 'Bild (WebP)';

            case 'image/svg+xml':
            case 'svg':
                return 'Vektorgrafik (SVG)';

            case 'image/bmp':
            case 'bmp':
                return 'Bild (BMP)';

            case 'image/tiff':
            case 'tif':
            case 'tiff':
                return 'Bild (TIFF)';

                // --- Dokumente ---
            case 'application/pdf':
            case 'pdf':
                return 'PDF';

            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            case 'doc':
            case 'docx':
                return 'Word-Dokument';

            case 'application/vnd.ms-excel':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            case 'xls':
            case 'xlsx':
                return 'Excel-Tabelle';

            case 'application/vnd.ms-powerpoint':
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
            case 'ppt':
            case 'pptx':
                return 'PowerPoint-Präsentation';

            case 'text/plain':
            case 'txt':
                return 'Textdatei';

            case 'text/csv':
            case 'csv':
                return 'CSV-Datei';

            case 'application/json':
            case 'json':
                return 'JSON-Datei';

            case 'application/xml':
            case 'text/xml':
            case 'xml':
                return 'XML-Datei';

            case 'application/rtf':
            case 'rtf':
                return 'RTF-Dokument';

                // --- Archive ---
            case 'application/zip':
            case 'zip':
                return 'ZIP-Archiv';

            case 'application/x-7z-compressed':
            case '7z':
                return '7z-Archiv';

            case 'application/x-rar-compressed':
            case 'rar':
                return 'RAR-Archiv';

            case 'application/gzip':
            case 'application/x-gzip':
            case 'gz':
            case 'gzip':
                return 'GZIP-Archiv';

            case 'application/x-tar':
            case 'tar':
                return 'TAR-Archiv';

                // --- Code / Konfig ---
            case 'text/html':
            case 'html':
            case 'htm':
                return 'HTML-Datei';

            case 'text/css':
            case 'css':
                return 'CSS-Datei';

            case 'application/javascript':
            case 'text/javascript':
            case 'js':
                return 'JavaScript-Datei';

            case 'application/x-php':
            case 'text/x-php':
            case 'php':
                return 'PHP-Datei';

            case 'application/x-sh':
            case 'sh':
                return 'Shell-Skript';

            case 'application/x-yaml':
            case 'text/yaml':
            case 'yaml':
            case 'yml':
                return 'YAML-Datei';

            case 'application/x-toml':
            case 'toml':
                return 'TOML-Datei';

            case 'application/x-env':
            case 'env':
                return 'ENV-Datei';

                // --- Audio ---
            case 'audio/mpeg':
            case 'mp3':
                return 'Audio (MP3)';

            case 'audio/wav':
            case 'wav':
                return 'Audio (WAV)';

            case 'audio/ogg':
            case 'ogg':
                return 'Audio (OGG)';

                // --- Video ---
            case 'video/mp4':
            case 'mp4':
                return 'Video (MP4)';

            case 'video/webm':
            case 'webm':
                return 'Video (WebM)';

            case 'video/x-msvideo':
            case 'avi':
                return 'Video (AVI)';

                // --- Sonstige ---
            case 'application/octet-stream':
            case 'bin':
                return 'Binärdatei';

            default:
                return 'Unbekannter Dateityp';
        }
    }
}
