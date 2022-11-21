<?php

require_once('classes/project/StickerShopDBController.php');

class StickerImage {

    private $id;
    private $name;
    private $number;

    public $data;

    private $shopProducts;
    private $stickerDB;

    private $allFiles = [];
    private $images = [];
    private $files = [];

    function __construct($id) {
        $query = "SELECT * FROM module_sticker_sticker_data WHERE id = $id";
        $data = DBAccess::selectQuery($query)[0];
        $this->id = $id;
        $this->name = $data["name"];
        $this->data = $data;

        $query = "SELECT * FROM prstshp_product WHERE reference = {$this->id}";
        $this->stickerDB = new StickerShopDBController($this->id, $this->name, "test", "test", 20);
        $this->stickerDB->select($query);
        /*$this->stickerDB->addImages(["https://media.4-paws.org/0/3/c/4/03c4df8eaa4f33f07c38c0f6b24839981174b2f3/VIER%20PFOTEN_2016-07-08_011-4993x3455-1920x1329.jpg", "https://cdn.mdr.de/wissen/katze-corona-104_v-variantBig16x9_w-1280_zc-b903ef86.jpg?version=38140"]);
        $this->stickerDB->addSticker();*/
        $this->shopProducts = $this->stickerDB->getResult();
        $this->getConnectedFiles();
        $this->createCombinations();
    }

    public static function creatStickerImage() {
        $query = "";
        $id = DBAccess::insertQuery($query);
        return new StickerImage($id);
    }

    public function getName() {
        return $this->name;
    }

    public function saveSticker() {
        $this->generateSticker();
        if ($this->isInShop()) {
            $this->updateSticker();
        } else {
            $this->generateSticker();
        }
    }

    private function updateSticker() {

    }

    private function generateSticker() {
        /*$this->stickerDB->addImages(["https://media.4-paws.org/0/3/c/4/03c4df8eaa4f33f07c38c0f6b24839981174b2f3/VIER%20PFOTEN_2016-07-08_011-4993x3455-1920x1329.jpg", "https://cdn.mdr.de/wissen/katze-corona-104_v-variantBig16x9_w-1280_zc-b903ef86.jpg?version=38140"]);
        $this->stickerDB->addSticker();*/
        $this->createCombinations();
        $this->stickerDB->addSticker();
    }

    private function generateLinks() {

    }

    public function isInShop() {
        return $this->shopProducts == null ? false : true;
    }

    public function getSizeTable() {
        $query = "SELECT id, width, height FROM module_sticker_sizes WHERE id_sticker = {$this->id} ORDER BY width";
        $data = DBAccess::selectQuery($query);
        $column_names = array(
            0 => array("COLUMN_NAME" => "id", "ALT" => "Kombinummer"),
            1 => array("COLUMN_NAME" => "width", "ALT" => "Breite"),
            2 => array("COLUMN_NAME" => "height", "ALT" => "Höhe")
        );

        if ($data == null) {
            $data = $this->loadDefault($query);
        }

        foreach ($data as &$d) {
            $d["width"] = str_replace(".", ",", ((int) $d["width"]) / 10) . "cm";
            $d["height"] = str_replace(".", ",", ((int) $d["height"]) / 10) . "cm";
        }

		$t = new Table();
		$t->createByData($data, $column_names);
		$t->addActionButton("edit");
		$t->setType("schritte");
		$t->addActionButton("delete", "id");
		$t->addNewLineButton();
			
        $pattern = [
            "id_sticker" => [
                "status" => "preset",
                "value" => $this->id,
            ],
            "width" => [
                "status" => "unset",
                "value" => 1,
                "type" => "cm",
                "cast" => [],
            ],
            "height" => [
                "status" => "unset",
                "value" => 2,
                "type" => "cm",
                "cast" => [],
            ],
        ];

		$t->defineUpdateSchedule(new UpdateSchedule("module_sticker_sizes", $pattern));
        $_SESSION[$t->getTableKey()] = serialize($t);
		return $t->getTable();
    }

    /* Änderung: proforma Daten erstellen als default value */
    private function loadDefault($query2) {
        $query = "INSERT INTO module_sticker_sizes (id_sticker, width, height) VALUES ($this->id, 300, 0), ($this->id, 600, 0), ($this->id, 900, 0), ($this->id, 1200, 0)";
        DBAccess::insertQuery($query);
        return DBAccess::selectQuery($query2);
    }

    private function getConnectedFiles() {
        $allFiles = DBAccess::selectQuery("SELECT dateien.dateiname, dateien.originalname AS title, dateien.typ FROM dateien, dateien_motive WHERE dateien_motive.id_datei = dateien.id AND dateien_motive.id_motive = {$this->id}");

        $this->allFiles = $allFiles;
        foreach ($this->allFiles as $f) {
            /* https://stackoverflow.com/questions/15408125/php-check-if-file-is-an-image */
            if(@is_array(getimagesize("upload/" . $f["dateiname"]))){
                array_push($this->images, $f);
            } else {
                array_push($this->files, $f);
            }
        }
    }

    public function getImages() {
        foreach ($this->images as &$image) {
            $image["link"] = Link::getResourcesShortLink($image["dateiname"], "upload");
            $image["alt"] = "";
        }

        if (sizeof($this->images) == 0) {
            $this->images = [
                0 => [
                    "title" => "default image",
                    "alt" => "default image",
                    "link" => Link::getResourcesShortLink("default_image.png", "upload")
                ],
            ];
        }

        return $this->images;
    }

    public function getFiles() {
        $download = "";
        foreach ($this->files as $f) {
            $link = Link::getResourcesShortLink($f["dateiname"], "upload");
            $download .= "<a href=\"$link\">" . strtoupper($f["typ"]) . "</a>";
        }
        return $download;
    }

    /* save sticker fields */
    public function saveSentData($jsonData) {
        $data = json_decode($jsonData);
        $plott = $data->plott;
        $short = $data->short;
        $long = $data->long;
        $multi = $data->multi;
        $query = "UPDATE module_sticker_sticker_data SET is_plotted = $plott, is_short_time = $short, is_long_time = $long, is_multipart = $multi WHERE id = {$this->id}";
        DBAccess::updateQuery($query);
        echo "success";
    }

    /**
     * gibt die ids der id_attribute_group 5 (Breite) zurück,
     * dabei wird geprüft, ob zu dem Breitenwert schon eine id_attribute existiert und falls nicht,
     * wird diese erstellt
     */
    public function getSizeIds() {
        $sizes = ["30cm", "60cm", "90cm", "120cm"];
        $sizeIds = array();
        foreach ($sizes as $size) {
            $sizeId = (int) $this->stickerDB->addAttribute("5", $size);
            array_push($sizeIds, $sizeId);
        }

        return ["id" => 5, "ids" => $sizeIds];
    }

    /**
     * gibt die ids der id_attribute_group 6 (Farbe) zurück, also alle benötigten Farben für die Aufkleber,
     * kann später durch eine Auswahloption ergänzt oder ersetzt werden;
     */
    public function getColorIds() {
        return ["id" => 6, "ids" => [70, 60, 67, 79, 91, 107, 111]];
    }

    public function createCombinations() {
        $this->stickerDB->setSizes($this->getSizeIds());
        $this->stickerDB->setColors($this->getColorIds());
    }

}

?>