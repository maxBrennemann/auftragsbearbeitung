<?php

namespace Classes;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

use Classes\Project\Produkt;

use Classes\Sticker\StickerImage;

class Upload
{

    private $uploadDir = "upload/";

    public function __construct($setUploadDir = "")
    {
        if ($setUploadDir != "") {
            $this->uploadDir = $setUploadDir;
        }
    }

    public function uploadFilesProduct($produktnummer)
    {
        $ids = $this->uploadFiles();
        if (is_array($ids)) {
            foreach ($ids as $id) {
                /*echo "id: " . $id . " /id";*/
                DBAccess::insertQuery("INSERT INTO dateien_produkte (id_datei, id_produkt) VALUES ($id, $produktnummer)");
            }
        }

        echo Produkt::getFiles($produktnummer);
    }

    /*
     * uploads sticker files and returns a json object with all data about images,
     * so that the new images can all be added via ajax
     */
    public function uploadFilesMotive($name, $id = 0)
    {
        $ids = $this->uploadFiles();

        if (is_array($ids)) {
            $motivnummer = 0;

            /* create new sticker if no id is set */
            if ($id == 0) {
                $motivnummer = DBAccess::insertQuery("INSERT INTO module_sticker_sticker_data (`name`) VALUES ('$name')");
            } else {
                $motivnummer = $id;
            }

            $imageData = [];

            /* upload each image */
            foreach ($ids as $id) {
                $imageCategory = Tools::get("imageCategory");
                $query = "INSERT INTO module_sticker_image (id_datei, id_motiv, image_sort) VALUES (:id, :motivnummer, :imageCategory)";

                $params = [
                    "id" => $id,
                    "motivnummer" => $motivnummer,
                    "imageCategory" => $imageCategory,
                ];

                /* delete all textilsvg for this motiv id */
                /* TODO: bad practice: refactor code */
                if ($imageCategory == "textilsvg") {
                    StickerImage::handleSVGStatus($motivnummer);
                }

                DBAccess::insertQuery($query, $params);

                $image = DBAccess::selectQuery("SELECT dateiname, originalname FROM dateien WHERE id = $id LIMIT 1");
                $url = Link::getResourcesShortLink($image[0]["dateiname"], "upload");
                $originalname = $image[0]["originalname"];
                $type = pathinfo($url)["extension"];

                array_push($imageData, [
                    "id" => $id,
                    "url" => $url,
                    "original" => $originalname,
                    "type" => $type,
                ]);
            }

            echo json_encode([
                "motiv" => $motivnummer,
                "imageData" => $imageData,
                "files" => $_FILES["files"]["tmp_name"],
            ]);
            return;
        }

        echo json_encode([
            "error" => "an error occured",
            "message" => $ids,
            "files" => $_FILES["files"]["tmp_name"],
        ]);
    }

    public function uploadFilesPosten($postennummer)
    {
        $ids = $this->uploadFiles();
        if (is_array($ids)) {
            foreach ($ids as $id) {
                DBAccess::insertQuery("INSERT INTO dateien_posten (id_file, id_posten) VALUES ($id, $postennummer)");
            }
        }
    }

    /**
     * Uploads the files in $_FILES["files"] to the server and 
     * returns an array with the ids of the files in the database,
     * or a string with an error message
     * 
     * @return array|string
     */
    private function uploadFiles(): array|string
    {
        $msg = "";

        try {
            /* https://stackoverflow.com/questions/2704314/multiple-file-upload-in-php */
            $datetime = new \DateTime();
            $total = count($_FILES["files"]["name"]);
            /*echo "filename: " . $_FILES["files"]["name"][0];*/
            $ids = array();

            /* files is the name of the file input element in frontend */
            for ($i = 0; $i < $total; $i++) {
                $filename = basename($_FILES["files"]["name"][$i]);
                $filename = self::adjustFileName($filename);
                $filename = $datetime->getTimestamp() . $filename;

                $originalname = basename($_FILES["files"]["name"][$i]);
                $filetype = pathinfo($_FILES["files"]["name"][$i], PATHINFO_EXTENSION);
                $date = date("Y-m-d");

                $insertQuery = "INSERT INTO dateien (dateiname, originalname, typ, `date`) VALUES ('$filename', '$originalname','$filetype', '$date')";

                if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $this->uploadDir . $filename)) {
                    array_push($ids, DBAccess::insertQuery($insertQuery));
                } else {
                    return $filename . " could not be uploaded, " . $_FILES["files"]["error"][$i];
                }
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }

        return $msg == "" ? $ids : $msg;
    }

    /**
     * Adjusts the filename to remove spaces, @ and &
     * and shortens the name if it is longer than 70 characters
     */
    private static function adjustFileName($name): String
    {
        $adjustedFilename = str_replace(" ", "", $name);
        $adjustedFilename = str_replace("&", "", $adjustedFilename);
        $adjustedFilename = str_replace("@", "", $adjustedFilename);

        /* cuts the last 30 characters from the end if the name is too long */
        if (strlen($adjustedFilename) > 70) {
            $adjustedFilename = substr($adjustedFilename, -30);
        }

        return $adjustedFilename;
    }
}
