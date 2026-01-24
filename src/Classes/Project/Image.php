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
}
