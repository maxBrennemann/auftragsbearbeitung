<?php 

require_once('classes/DBAccess.php');

class Upload {
    private $uploadDir = "upload/";
    
    public function uploadFiles($auftragsnummer) {
        $datetime = new DateTime();
        $filename = $datetime->getTimestamp() . basename($_FILES["uploadedFile"]["name"]);
        $originalname = basename($_FILES["uploadedFile"]["name"]);
        $insertQuery = "INSERT INTO dateien (dateiname, auftragsnummer, originalname) VALUES ('$filename', $auftragsnummer, '$originalname')";
        
        if (move_uploaded_file($_FILES["uploadedFile"]["tmp_name"], $this->uploadDir . $filename)) {
            DBAccess::insertQuery($insertQuery);
            $link = Link::getPageLink("auftrag") . "?id=" . $auftragsnummer;
            header("Location:$link");
        } else {
            return -1;
        }
    }

    public static function getFiles($auftragsnummer) {
        $files = DBAccess::selectQuery("SELECT dateiname, originalname FROM dateien WHERE auftragsnummer = $auftragsnummer");
        $html = "";
        foreach ($files as $file) {
            $link = Link::getResourcesShortLink($file['dateiname'], "upload");
            $html .= "<span><a href=\"$link\" download=\"${file['originalname']}\">${file['originalname']}</a></span><br>";
        }
        return $html;
    }
}

?>