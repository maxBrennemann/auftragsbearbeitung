<?php

class StickerImage {
    
    private $idMotiv;

    private $allFiles = [];
    private $images = [];
    private $files = [];

    function __construct($idMotiv) {
        $this->idMotiv = $idMotiv;
        $this->getConnectedFiles();
        $this->prepareImageData();
    }

    private function getConnectedFiles() {
        $allFiles = DBAccess::selectQuery("SELECT dateien.dateiname, dateien.originalname AS alt, 
                dateien.typ, dateien.id, module_sticker_images.is_aufkleber, 
                module_sticker_images.is_wandtattoo, module_sticker_images.is_textil 
            FROM dateien, dateien_motive, module_sticker_images 
            WHERE dateien_motive.id_datei = dateien.id 
                AND module_sticker_images.id_image = dateien.id 
                AND dateien_motive.id_motive = :idMotiv",
        ["idMotiv" => $this->idMotiv]);

        $this->allFiles = $allFiles;
        foreach ($this->allFiles as $f) {
            /* https://stackoverflow.com/questions/15408125/php-check-if-file-is-an-image */
            if (@is_array(getimagesize("upload/" . $f["dateiname"]))){
                array_push($this->images, $f);
            } else {
                array_push($this->files, $f);
            }
        }
    }

    public function getFiles() {
        return $this->files;
    }

    public function getImages() {
        foreach ($this->images as &$image) {
            $image["link"] = Link::getResourcesShortLink($image["dateiname"], "upload");
            $image["title"] = "product image";
        }

        if (sizeof($this->images) == 0) {
            $this->images = [
                0 => [
                    "id" => 0,
                    "title" => "default image",
                    "alt" => "default image",
                    "link" => Link::getResourcesShortLink("default_image.png", "img"),
                    "dateiname" => "Standardbild",
                    "typ" => "png",
                ],
            ];
        }

        return $this->images;
    }

    public function getSVGIfExists() {
        $download = "";
        foreach ($this->files as $f) {
            $link = Link::getResourcesShortLink($f["dateiname"], "upload");
            if ($f["typ"] == "svg") {
                $download = $link;
            }
        }
        return $download;
    }

    // TODO: Funktionen für colorable toggle fertig schreiben
    public function getColorableSVG() {
        
    }

    private function prepareImageData() {
        foreach ($this->images as &$image) {
            $image["link"] = Link::getResourcesShortLink($image["dateiname"], "upload");
            $image["title"] = "Produktbild";
        }
    }

    public function getAufkleberImages() {
        return array_filter(
            $this->images,
            fn($element) => $element["is_aufkleber"] == "1"
        );
    }

    public function getWandtattooImages() {
        return array_filter(
            $this->images,
            fn($element) => $element["is_wandtattoo"] == "1"
        );
    }

    public function getTextilImages() {
        return array_filter(
            $this->images,
            fn($element) => $element["is_textil"] == "1"
        );
    }

    public function getSVG() {
        $filename = "";
        foreach ($this->files as $f) {
            if ($f["typ"] == "svg") {
                $filename = "upload/" . $f["dateiname"];
            }
        }
        return $filename;
    }

    public function getUnspecificImages() {
        return array_filter(
            $this->images,
            fn($element) => $element["is_aufkleber"] != "1" && $element["is_wandtattoo"] != "1" && $element["is_textil"] != "1"
        );
    }

    public function resizeImage($file) {
        list($width, $height) = getimagesize("upload/" . $file["dateiname"]);
        /* width and height do not matter any longer, images are only resized if filesize exeeds 2MB */
        if (filesize("upload/" . $file["dateiname"]) >= 2000000) {
            switch ($file["typ"]) {
                case "jpg":
                    if (function_exists("imagecreatefromjpeg")) {
                        $image = imagecreatefromjpeg("upload/" . $file["dateiname"]);
                        $imgResized = imagescale($image , 700, 700 * ($height / $width));
                        imagejpeg($imgResized, "upload/" . $file["dateiname"]);
                    }
                    break;
                case "png":
                    if (function_exists("imagecreatefrompng")) {
                        $image = imagecreatefrompng("upload/" . $file["dateiname"]);
                        $imgResized = imagescale($image , 700, 700 * ($height / $width));
                        imagepng($imgResized, "upload/" . $file["dateiname"]);
                    }
                    break;
                default:
                    return;
            }
        }
    }

}

?>