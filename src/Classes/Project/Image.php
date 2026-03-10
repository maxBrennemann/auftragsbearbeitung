<?php

namespace Src\Classes\Project;

use InvalidArgumentException;
use Src\Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class Image
{
    private int $imageId = 0;
    private string $url = "";

    public function __construct(int $id)
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("Id $id is not a valid image id.");
        }

        $fileData = DBAccess::selectQuery("SELECT dateiname FROM dateien WHERE id = :id", [
            "id" => $id,
        ]);

        $fileName = $fileData["0"]["dateiname"];

        $this->url = Link::getResourcesShortLink($fileName, "upload");
        $this->imageId = $id;
    }

    public function getImageId(): int
    {
        return $this->imageId;
    }

    public function getImageURL(): string
    {
        return $this->url;
    }

    public static function setDefault(): Image
    {
        $img = new self(-1);
        $img->url = Link::getDefaultImage();
        return $img;
    }

    public static function getLogo(): string
    {
        $logoId = Settings::get("company.logoId");

        if ($logoId == null) {
            return "";
        }

        $query = "SELECT dateiname FROM dateien WHERE id = :id";
        $data = DBAccess::selectQuery($query, [
            "id" => $logoId,
        ]);

        return $data[0]["dateiname"] ?? "";
    }

    public static function addLogo(): void
    {
        $uploadHandler = new UploadHandler("default", [
            "image/png",
            "image/jpg",
            "image/jpeg",
        ], 25000000, 1);
        $fileData = $uploadHandler->uploadMultiple();

        if (count($fileData) == 0) {
            JSONResponseHandler::throwError(422, "unsupported file type");
        }

        $logoId = (int) $fileData[0]["id"];
        Settings::set("company.logoId", $logoId);

        JSONResponseHandler::sendResponse([
            "logoId" => $fileData[0]["id"],
            "file" => Link::getResourcesShortLink($fileData[0]["saved_name"], "upload"),
        ]);
    }

    public static function addFavicon(): void
    {
        $uploadHandler = new UploadHandler("default", [
            "image/png",
            "image/jpg",
            "image/jpeg",
            "image/x-icon",
            "image/svg+xml",
        ], 25000000, 1);
        $fileData = $uploadHandler->uploadMultiple();

        if (count($fileData) == 0) {
            JSONResponseHandler::throwError(422, "unsupported file type");
        }

        $uploadedFile = Config::get("paths.uploadDir.default") . $fileData[0]["storage_path"];
        $targetDir = ROOT . "public/assets/img/";

        $info = getimagesize($uploadedFile);
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $src = imagecreatefromjpeg($uploadedFile);
                break;
            case 'image/png':
                $src = imagecreatefrompng($uploadedFile);
                break;
            case 'image/webp':
                $src = imagecreatefromwebp($uploadedFile);
                break;
            default:
                JSONResponseHandler::throwError(422, "Unsupported image format for GD");
        }

        $process = function ($src, $size, $destPath) {
            $new = imagecreatetruecolor($size, $size);

            imagealphablending($new, false);
            imagesavealpha($new, true);

            $width = imagesx($src);
            $height = imagesy($src);

            imagecopyresampled($new, $src, 0, 0, 0, 0, $size, $size, $width, $height);
            imagepng($new, $destPath, 9);
            imagedestroy($new);
        };

        try {
            $process($src, 32,  $targetDir . "favicon.png");
            $process($src, 32,  $targetDir . "favicon.ico");
            $process($src, 180, $targetDir . "apple-touch-icon.png");

            imagedestroy($src);

            JSONResponseHandler::sendResponse([
                "Favicons updated via GD",
            ]);
        } catch (\Exception $e) {
            JSONResponseHandler::throwError(500, "GD Processing failed");
        }
    }
}
