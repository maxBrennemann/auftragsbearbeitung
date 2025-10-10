<?php

namespace Src\Classes\Project;

use Src\Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;

class Image
{
    private int $imageId = 0;
    private string $url = "";

    public function __construct(int $id)
    {
        if ((int) $id >= 0) {
            $fileName = DBAccess::selectQuery("SELECT dateiname FROM dateien WHERE id = $id")["0"]["dateiname"];
            $this->url = Link::getResourcesShortLink($fileName, "upload");
            $this->imageId = $id;
        }
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
}
