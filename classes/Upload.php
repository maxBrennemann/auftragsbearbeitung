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
        $insertQuery = "INSERT INTO dateien (dateiname, originalname) VALUES ('$filename', '$originalname')";
        
        if (move_uploaded_file($_FILES["uploadedFile"]["tmp_name"], $this->uploadDir . $filename)) {
            return DBAccess::insertQuery($insertQuery);
        } else {
            return -1;
        }
    }

    public static function getFiles() {
        
    }

    public static function getFilesAuftrag($auftragsnummer) {
        $files = DBAccess::selectQuery("SELECT DISTINCT dateiname, originalname FROM dateien LEFT JOIN dateien_auftraege ON dateien_auftraege.id_datei WHERE dateien_auftraege.id_auftrag = $auftragsnummer");
        $html = "";
        foreach ($files as $file) {
            $link = Link::getResourcesShortLink($file['dateiname'], "upload");
            $html .= "<span><a href=\"$link\" download=\"${file['originalname']}\">${file['originalname']}</a></span><br>";
        }
        return $html;
    }
}

?>