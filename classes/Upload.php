<?php 

require_once('classes/DBAccess.php');

class Upload {
    private $uploadDir = "upload/";
    
    public function uploadFilesAuftrag($auftragsnummer) {
        $id = $this->uploadFiles();
        if ((int) $id != -1) {
            echo "id: " . $id . " /id";
            DBAccess::insertQuery("INSERT INTO dateien_auftraege (id_datei, id_auftrag) VALUES ($id, $auftragsnummer)");
    
            $link = Link::getPageLink("auftrag") . "?id=" . $auftragsnummer;
            header("Location:$link");
        }
    }

    public function uploadFilesProduct($produktnummer) {
        $id = $this->uploadFiles();
        echo "id: " . $id . " /id";
        DBAccess::insertQuery("INSERT INTO dateien_produkte (id_datei, id_produkt) VALUES ($id, $produktnummer)");
    
        $link = Link::getPageLink("neuesProdukt") . "?id=" . $auftragsnummer;
        header("Location:$link");
    }

    private function uploadFiles() {
        error_reporting(-1);

        $datetime = new DateTime();
       
        $filename = $datetime->getTimestamp() . basename($_FILES["uploadedFile"]["name"]);
        $originalname = basename($_FILES["uploadedFile"]["name"]);
        $filetype = pathinfo($_FILES["uploadedFile"]["name"], PATHINFO_EXTENSION);
        $date = date("Y-m-d");

        $insertQuery = "INSERT INTO dateien (dateiname, originalname, typ, `date`) VALUES ('$filename', '$originalname','$filetype', '$date')";
        
        if (move_uploaded_file($_FILES["uploadedFile"]["tmp_name"], $this->uploadDir . $filename)) {
            return DBAccess::insertQuery($insertQuery);
        } else {
            return -1;
        }
    }

    public static function getFiles() {
        
    }

    public static function getFilesAuftrag($auftragsnummer) {
        $files = DBAccess::selectQuery("SELECT DISTINCT dateiname AS Datei, originalname, `date` AS Datum, typ as Typ FROM dateien LEFT JOIN dateien_auftraege ON dateien_auftraege.id_datei WHERE dateien_auftraege.id_auftrag = $auftragsnummer");
        for ($i = 0; $i < sizeof($files); $i++) {
            $link = Link::getResourcesShortLink($files[$i]['Datei'], "upload");
            $html = "<span><a target=\"_blank\" rel=\"noopener noreferrer\" href=\"$link\">{$files[$i]['originalname']}</a></span>"; //download=\"{$files[$i]['originalname']}\"

            $files[$i]['Datei'] = $html;
        }

        $column_names = array(0 => array("COLUMN_NAME" => "Datei"), 1 => array("COLUMN_NAME" => "Typ"), 2 => array("COLUMN_NAME" => "Datum"));

        $form = new FormGenerator("dateien", "", "");
		$table = $form->createTableByData($files, $column_names, "dateien", null);
		return $table;
    }
}

?>