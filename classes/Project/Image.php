<?php

namespace Classes\Project;

use Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;

class Image
{
    private $imageId = 0;
    private $url = "";

    public function __construct($id)
    {
        if ((int) $id >= 0) {
            $fileName = DBAccess::selectQuery("SELECT dateiname FROM dateien WHERE id = $id")["0"]["dateiname"];
            $this->url = Link::getResourcesShortLink($fileName, "upload");
            $this->imageId = $id;
        }
    }

    public function getImageId()
    {
        return $this->imageId;
    }

    public function getImageURL()
    {
        return $this->url;
    }

    public static function setDefault()
    {
        $img = new self(-1);
        $img->url = Link::getDefaultImage();
        return $img;
    }
}
