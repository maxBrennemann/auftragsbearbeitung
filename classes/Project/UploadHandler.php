<?php

namespace Classes\Project;

use Classes\Protocol;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use RuntimeException;

class UploadHandler
{
    private string $uploadBaseDir = "upload";
    private array $allowedMimeTypes = [];
    private int $maxFileSize = 25000000;
    private int $fileUploadLimit = 0;

    public function __construct(string $uploadBaseDir = "upload", array $allowedMimeTypes = [
        "application/pdf",
        "image/png",
        "image/jpg",
        "image/jpeg",
        "application/doc",
        "application/docx"
    ], int $maxFileSize = 25000000, int $fileUploadLimit = 0)
    {
        $this->uploadBaseDir = $uploadBaseDir;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxFileSize = $maxFileSize;
        $this->fileUploadLimit = $fileUploadLimit;
    }

    public function uploadMultiple(string $uploadName = "files"): array
    {
        $results = [];
        try {
            $normalizedFiles = $this->normalizeFilesArray($_FILES[$uploadName]);

            foreach ($normalizedFiles as $file) {
                if (
                    $this->fileUploadLimit > 0
                    && count($results) >= $this->fileUploadLimit
                ) {
                    continue;
                }
                $results[] = $this->handleUpload($file);
            }
        } catch (RuntimeException $e) {
            Protocol::write("upload file error", $e->getMessage(), "ERROR");
            return [];
        }

        return $results;
    }

    public function handleUpload(array $file): array
    {
        if (!isset($file) || $file["error"] !== UPLOAD_ERR_OK) {
            throw new RuntimeException("No file uploaded or upload error.");
        }

        if ($file["size"] > $this->maxFileSize) {
            throw new RuntimeException("File too large.");
        }

        $mimeType = self::detectMimeType($file["tmp_name"]);
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            throw new RuntimeException("Unsupported file type.");
        }

        $hash = hash_file("sha256", $file["tmp_name"]);
        $subDir = substr($hash, 0, 2) . "/" . substr($hash, 2, 2);
        $uploadDir = $this->uploadBaseDir . "/" . $subDir;

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new RuntimeException("Failed to create upload directory.");
        }

        $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $safeFileName = $hash . ($extension ? "." . $extension : "");

        $destination = $uploadDir . "/" . $safeFileName;

        $alreadyExisted = file_exists($destination);
        if (!$alreadyExisted) {
            if (!move_uploaded_file($file["tmp_name"], $destination)) {
                throw new RuntimeException("Failed to move uploaded file.");
            }
        }

        $fileId = $this->saveToDb($safeFileName, $file["name"], $mimeType);

        return [
            "id" => $fileId,
            "original_name" => $file["name"],
            "saved_name" => $safeFileName,
            "mime_type" => $mimeType,
            "file_size" => $file["size"],
            "storage_path" => $subDir . "/" . $safeFileName,
            "uploaded_at" => date("Y-m-d H:i:s"),
            "already_existed" => $alreadyExisted,
        ];
    }

    private static function detectMimeType(string $filePath): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($filePath);
    }

    private function saveToDb(string $fileName, string $originalName, string $fileType): int
    {
        $query = "INSERT INTO dateien (dateiname, originalname, typ, `date`) VALUES (:fileName, :originalName, :fileType, :date)";
        $id = DBAccess::insertQuery($query, [
            "fileName" => $fileName,
            "originalName" => $originalName,
            "fileType" => $fileType,
            "date" => date("Y-m-d H:i:s"),
        ]);

        return $id;
    }

    private function normalizeFilesArray(array $filesArray): array
    {
        $normalized = [];
        foreach ($filesArray["name"] as $index => $name) {
            $normalized[] = [
                "name"     => $filesArray["name"][$index],
                "type"     => $filesArray["type"][$index],
                "tmp_name" => $filesArray["tmp_name"][$index],
                "error"    => $filesArray["error"][$index],
                "size"     => $filesArray["size"][$index],
            ];
        }
        return $normalized;
    }

    public static function deleteUnusedFiles(): array
    {
        $deletedFiles = [];

        $dbFiles = DBAccess::selectQuery("SELECT dateiname FROM dateien;");
        $dbFileNames = array_map(fn ($row) => $row["dateiname"], $dbFiles);
        $dbFileNames[] = ".gitkeep";

        self::deleteUnusedFilesInDirectory("upload", $dbFileNames, $deletedFiles);
        self::deleteUnusedFilesInDirectory("generated", $dbFileNames, $deletedFiles);

        JSONResponseHandler::sendResponse([
            "deleted_count" => count($deletedFiles),
            "deleted_files" => $deletedFiles,
            "status" => "success",
        ]);

        return [
            "deleted_count" => count($deletedFiles),
            "deleted_files" => $deletedFiles,
        ];
    }

    private static function deleteUnusedFilesInDirectory(string $directory, array $usedFiles, &$deletedFiles): void
    {
        $realBase = realpath($directory);
        if (!$realBase || !is_dir($realBase)) {
            throw new \InvalidArgumentException("Invalid directory: $directory");
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $fullPath = $fileInfo->getPathname();
            $relativePath = substr($fullPath, strlen($realBase) + 1);

            if (!in_array($relativePath, $usedFiles)) {
                unlink($fullPath);
                $deletedFiles[] = $relativePath;
            }
        }
    }

    public static function migrateFiles()
    {
        $query = "SELECT id, dateiname FROM dateien";
        $data = DBAccess::selectQuery($query);

        foreach ($data as $fileRef) {
            $fileName = $fileRef["dateiname"];
            $fileId = $fileRef["id"];

            $adjustedName = self::adjustFileName($fileName);
            if ($adjustedName == false) {
                continue;
            }

            DBAccess::updateQuery("UPDATE dateien SET dateiname = :adjustedName WHERE id = :fileId;", [
                "adjustedName" => $adjustedName,
                "fileId" => $fileId,
            ]);
        }

        JSONResponseHandler::returnOK();
    }

    private static function adjustFileName(string $fileName): string|bool
    {
        if (!file_exists("upload/" . $fileName)) {
            return false;
        }

        $hash = hash_file("sha256", "upload/" . $fileName);
        $subDir = substr($hash, 0, 2) . "/" . substr($hash, 2, 2);
        $uploadDir = "upload/" . $subDir;

        $extension = strtolower(pathinfo("upload/" . $fileName, PATHINFO_EXTENSION));
        $safeFileName = $hash . ($extension ? "." . $extension : "");

        $destination = $uploadDir . "/" . $safeFileName;

        if (!file_exists($destination)) {
            rename("upload/" . $fileName, $destination);
        }

        return $safeFileName;
    }
}
