<?php

class StickerImage {

    private $id;
    private $name;
    private $number;

    private $files;

    function __construct($id) {
        $query = "SELECT dateiname, typ, motive.name, motive.id FROM motive JOIN dateien_motive ON dateien_motive.id_motive = motive.id, JOIN dateien dateien.id = dateien_motive.id_datei WHERE motive.id = $id";
        $data = DBAccess::selectQuery($query);
        $this->id = $data[0]["id"];
        $this->files = [];

        foreach ($data as $d) {
            array_push($this->files, $d["dateiname"]);
        }
    }

    public static function creatStickerImage() {
        $query = "";
        $id = DBAccess::insertQuery($query);
        return new StickerImage($id);
    }

    private function generateLinks() {

    }

}

?>