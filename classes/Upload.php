<?php 

require_once('classes/DBAccess.php');
require_once('classes/project/Auftragsverlauf.php');

class Upload {
    private $uploadDir = "upload/";
    
    public function uploadFilesAuftrag($auftragsnummer) {
        $ids = $this->uploadFiles();
        if (is_array($ids)) {
            foreach ($ids as $id) {
                /*echo "id: " . $id . " /id";*/
                DBAccess::insertQuery("INSERT INTO dateien_auftraege (id_datei, id_auftrag) VALUES ($id, $auftragsnummer)");
        
                $auftragsverlauf = new Auftragsverlauf($auftragsnummer);
                $auftragsverlauf->addToHistory($id, 4, "added");

                $link = Link::getPageLink("auftrag") . "?id=" . $auftragsnummer;
            }
        }

        echo Upload::getFilesAuftrag($auftragsnummer);
        return $ids;
    }

    public function uploadFilesVehicle($fahrzeugnummer, $auftragsnummer) {
        $ids = $this->uploadFilesAuftrag($auftragsnummer);
        if (is_array($ids)) {
            foreach ($ids as $id) {
                if ($id != -1) {
                    DBAccess::insertQuery("INSERT INTO dateien_fahrzeuge (id_datei, id_fahrzeug) VALUES ($id, $fahrzeugnummer)");
                }
            }
        }
    }

    public function uploadFilesProduct($produktnummer) {
        $ids = $this->uploadFiles();
        if (is_array($ids)) {
            foreach ($ids as $id) {
                /*echo "id: " . $id . " /id";*/
                DBAccess::insertQuery("INSERT INTO dateien_produkte (id_datei, id_produkt) VALUES ($id, $produktnummer)");
            }
        }


        echo Upload::getFilesProduct($produktnummer);
    }

    public function uploadFilesMotive($name, $id = 0) {
        $ids = $this->uploadFiles();

        if (is_array($ids)) {
            $motivnummer = 0;
            if ($id == 0) {
                $motivnummer = DBAccess::insertQuery("INSERT INTO module_sticker_sticker_data (`name`) VALUES ('$name')");
            } else {
                $motivnummer = $id;
            }
            
            $imageIds = [];
            foreach ($ids as $id) {
                $imageId = DBAccess::insertQuery("INSERT INTO dateien_motive (id_datei, id_motive) VALUES ($id, $motivnummer)");
                DBAccess::insertQuery("INSERT INTO module_sticker_images (id_image, id_sticker) VALUES ($id, $motivnummer)");
                array_push($imageIds, $imageId);
            }
            return json_encode(["motiv" => $motivnummer, "imageIds" => $imageIds]);
        }
        return json_encode(["error" => "an error occured"]);
    }

    public function uploadFilesPosten($postennummer) {
        $ids = $this->uploadFiles();
        if (is_array($ids)) {
            foreach ($ids as $id) {
                DBAccess::insertQuery("INSERT INTO dateien_posten (id_file, id_posten) VALUES ($id, $postennummer)");
            }
        }
    }

    private function uploadFiles() {
        error_reporting(-1);

        /* https://stackoverflow.com/questions/2704314/multiple-file-upload-in-php */
        $datetime = new DateTime();
        $total = count($_FILES["files"]["name"]);
        /*echo "filename: " . $_FILES["files"]["name"][0];*/
        $ids = array();

        /* files is the name of the file input element in frontend */

        for ($i = 0; $i < $total; $i++) {
            $filename = $datetime->getTimestamp() . basename($_FILES["files"]["name"][$i]);
            $originalname = basename($_FILES["files"]["name"][$i]);
            $filetype = pathinfo($_FILES["files"]["name"][$i], PATHINFO_EXTENSION);
            $date = date("Y-m-d");

            $insertQuery = "INSERT INTO dateien (dateiname, originalname, typ, `date`) VALUES ('$filename', '$originalname','$filetype', '$date')";
            
            if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $this->uploadDir . $filename)) {
                array_push($ids, DBAccess::insertQuery($insertQuery));
            } else {
                return -1;
            }
        }

        return $ids;
    }

    public static function getFiles() {
        
    }

    public static function getFilesAuftrag($auftragsnummer) {
        $files = DBAccess::selectQuery("SELECT DISTINCT dateiname AS Datei, originalname, `date` AS Datum, typ as Typ FROM dateien LEFT JOIN dateien_auftraege ON dateien_auftraege.id_datei = dateien.id WHERE dateien_auftraege.id_auftrag = $auftragsnummer");
        
        for ($i = 0; $i < sizeof($files); $i++) {
            $link = Link::getResourcesShortLink($files[$i]['Datei'], "upload");

            $filePath = "upload/" . $files[$i]['Datei'];
            /*
             * checks at first if the image exists
             * then checks if it is an image with exif_imagetype function,
             * suppresses with @ the notice and then checks if getimagesize
             * returns a value
             */
            if (file_exists($filePath) && (@exif_imagetype($filePath) != false) && getimagesize($filePath) != false) {
                $html = "<a target=\"_blank\" rel=\"noopener noreferrer\" href=\"$link\"><img class=\"img_prev_i\" src=\"$link\" width=\"40px\"><p class=\"img_prev\">{$files[$i]['originalname']}</p></a>";
            } else {
                $html = "<span><a target=\"_blank\" rel=\"noopener noreferrer\" href=\"$link\">{$files[$i]['originalname']}</a></span>";
            }

            $files[$i]['Datei'] = $html;
        }

        $column_names = array(
            0 => array("COLUMN_NAME" => "Datei"), 
            1 => array("COLUMN_NAME" => "Typ"), 
            2 => array("COLUMN_NAME" => "Datum")
        );

        $t = new Table();
		$t->createByData($files, $column_names);
		$t->setType("dateien");
        $t->addActionButton("delete", $identifier = "id");

		return $t->getTable();
    }

    public static function getFilesProduct($idProduct) {
        $files = DBAccess::selectQuery("SELECT DISTINCT dateiname AS Datei, originalname, `date` AS Datum, typ as Typ FROM dateien LEFT JOIN dateien_produkte ON dateien_produkte.id_datei = dateien.id WHERE dateien_produkte.id_produkt = $idProduct");
        
        for ($i = 0; $i < sizeof($files); $i++) {
            $link = Link::getResourcesShortLink($files[$i]['Datei'], "upload");

            if (getimagesize("upload/" . $files[$i]['Datei'])) {
                $html = "<a target=\"_blank\" rel=\"noopener noreferrer\" href=\"$link\"><img class=\"img_prev_i\" src=\"$link\" width=\"40px\"><p class=\"img_prev\">{$files[$i]['originalname']}</p></a>";
            } else {
                $html = "<span><a target=\"_blank\" rel=\"noopener noreferrer\" href=\"$link\">{$files[$i]['originalname']}</a></span>";
            }

            $files[$i]['Datei'] = $html;
        }

        $column_names = array(
            0 => array("COLUMN_NAME" => "Datei"), 
            1 => array("COLUMN_NAME" => "Typ"), 
            2 => array("COLUMN_NAME" => "Datum")
        );

        $t = new Table();
		$t->createByData($files, $column_names);
		$t->setType("dateien");
        $t->addActionButton("delete", $identifier = "id");

		return $t->getTable();
    }

    public function ajaxUpload() {

    }

}

?>