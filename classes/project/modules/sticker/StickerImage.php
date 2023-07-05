<?php

require_once('classes/project/modules/sticker/PrestashopConnection.php');

class StickerImage extends PrestashopConnection {
    
    private $idMotiv;

    private $allFiles = [];
    private $images = [];
    private $files = [];

    private $svgs = [];

    function __construct($idMotiv) {
        $this->idMotiv = $idMotiv;
        $this->getConnectedFiles();
        $this->prepareImageData();
    }

    /* reads from database */
    private function getConnectedFiles() {
        $allFiles = DBAccess::selectQuery("SELECT dateien.dateiname, dateien.originalname AS alt, 
                dateien.typ, dateien.id, module_sticker_image.image_sort, module_sticker_image.id_product, module_sticker_image.description
            FROM dateien, module_sticker_image 
            WHERE dateien.id = module_sticker_image.id_datei
                AND module_sticker_image.id_motiv = :idMotiv;",
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

    public function getSVGIfExists($colorable = false) {
        $f = $this->getTextilSVG($colorable);
        if ($f == null) {
            return "";
        }
        return Link::getResourcesShortLink($f["dateiname"], "upload");
    }

    /* adds new attributes "link" and "title" to all images */
    private function prepareImageData() {
        foreach ($this->images as &$image) {
            $image["link"] = Link::getResourcesShortLink($image["dateiname"], "upload");
            $image["title"] = "Produktbild";
        }
    }

    public function getAufkleberImages() {
        return array_filter(
            $this->images,
            fn($element) => $element["image_sort"] == "aufkleber"
        );
    }

    public function getWandtattooImages() {
        return array_filter(
            $this->images,
            fn($element) => $element["image_sort"] == "wandtattoo"
        );
    }

    public function getTextilImages() {
        return array_filter(
            $this->images,
            fn($element) => $element["image_sort"] == "textil"
        );
    }

    public function getTextilSVG($colorable = false) {
        foreach ($this->files as $f) {
            if ($f["image_sort"] == "textilsvg") {
                if ($colorable) {
                    return $this->makeSVGColorable($f);
                }
                return $f;
            }
        }
       
        return null;
    }

    public function getGeneralImages() {
        return array_filter(
            $this->images,
            fn($element) => $element["image_sort"] == "general"
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

    public function getFirstImageLink() {

    }

    public function convertJPGtoAvif() {

    }

    private function saveImage($filename) {
        /* add file to db */
        $query = "INSERT INTO `dateien` (`dateiname`, `originalname`, `date`, `typ`) VALUES (:newFile1, :newFile2, :today, 'svg');";
        $fileId = DBAccess::insertQuery($query, [
            "newFile1" => $filename,
            "newFile2" => $filename,
            "today" => date("Y-m-d")
        ]);

        $query = "INSERT INTO module_sticker_image (id_datei, id_motiv, image_sort) VALUES (:id, :motivnummer, :imageCategory)";
        $params = [
            "id" => $fileId,
            "motivnummer" => $this->idMotiv,
            "imageCategory" => "textilsvg",
        ];
        DBAccess::insertQuery($query, $params);
    }

    /* SVG section */
    public function getSVGCount() {
        return sizeof($this->svgs);
    }

    public function getSVG($number = 0) {
        $svgs = [];
        foreach ($this->files as $f) {
            if ($f["typ"] == "svg") {
                $svgs[] = $f;
            }
        }

        $this->svgs = $svgs;
        if (sizeof($svgs) > $number) {
            return "upload/" . $svgs[$number]["dateiname"];
        }

        return "";
    }
    
    /**
     * seaches for all occurances of colors in these two patterns:
     * fill:#FFFFFF
     * fill:#FFF
     * then it replaces "<svg" with "<svg id="svg_elem" only if it is not already set
     */
    public function makeSVGColorable($f) {
        $filename = $f["dateiname"];
        if ($filename == "") {
            return "";
        }

        $newFile = substr($filename, 0, -4);
        $newFile .= "_colorable.svg";

        if (!file_exists("upload/" . $newFile)) {
            $file = file_get_contents($filename);

            /* remove all fills */
            $file = preg_replace('/fill:#([0-9a-f]{6}|[0-9a-f]{3})/i', "", $file);

            /* remove all strokes */
            $file = preg_replace('/stroke:#([0-9a-f]{6}|[0-9a-f]{3})/i', "", $file);

            if (!str_contains($file, "<svg id=\"svg_elem\"")) {
                $file = str_replace("<svg", "<svg id=\"svg_elem\"", $file);
            }

            file_put_contents("upload/" . $newFile, $file);

            $this->saveImage($newFile);
            $f["dateiname"] = $newFile;
            return $f;
        } else {
            $f["dateiname"] = $newFile;
            return $f;
        }
    }

    public function uploadSVG($number) {

    }

    public static function handleSVGStatus(int $idMotiv) {
        $query = "DELETE FROM module_sticker_image WHERE id_motiv = :idMotiv AND image_sort = 'textilsvg';";
        DBAccess::deleteQuery($query, ["idMotiv" => $idMotiv]);
    }

    /**
     * deletes an image from the shop
     */
    public function deleteImage($idProduct, $idImageShop) {
        $this->deleteXML("images/products/$idProduct", $idImageShop);
    }

}

?>