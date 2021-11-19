<?php

class Image {

    private $imageId = 0;
    private $url = "";

    function __construct($id) {
        $fileName = DBAccess::selectQuery("SELECT dateiname FROM dateien WHERE id = $id")["0"]["dateiname"];
        $this->url = Link::getResourcesShortLink($fileName, "upload");
        $this->imageId = $id;
    }

    function getImageId() {
        return $this->imageId;
    }

    function getImageURL() {
        return $this->url;
    }

}

?>