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

    private $texts = [
        "kurzfristig" => "<p>Werbeaufkleber, der für den kurzfristigen Einsatz gedacht ist und sich daher auch wieder leicht ablösen lässt.</p>",
        "langfristig" => "<p>Diesen Aufkleber gibt es&nbsp;als Hochleistungsfolie, der für langfristige Beschriftungen oder Dekorationen gedacht&nbsp;ist.</p><p>Bringe Deinen Aufkleber als Deko für Privat oder für Dein Geschäft an.</p>",
        "kurzundlang" => "<p></p><p>Diesen Aufkleber gibt es in zwei Folienvarianten:</p><ol><li>Aufkleber aus Hochleistungsfolie, der für langfristige Beschriftungen oder Dekorationen gedacht&nbsp;ist.</li><li>Werbeaufkleber, der für den kurzfristigen Einsatz gedacht ist und sich daher auch wieder leicht ablösen lässt.</li></ol><p>Der Aufkleber eignet sich gut fürs Auto oder fürs Fenster, natürlich sind auch andere Anwendungen möglich.</p>",
        "mehrteilig" => "<p>Mehrfarbige Aufkleber werden als separate Teile geliefert.<br>Die Folien werden per Plotter aus einfarbiger Folie geschnitten und müssen daher beim Kleben Farbe für Farbe angebracht werden.</p>",
        "info" => "<p><span>Es wird jeweils nur der entsprechende Artikel oder das einzelne Motiv verkauft. Andere auf den Bildern befindliche Dinge sind nicht Bestandteil des Angebotes.</span></p>",
    ];

    function __construct($id) {
        $query = "SELECT * FROM module_sticker_sticker_data WHERE id = $id";
        $data = DBAccess::selectQuery($query)[0];
        $this->id = $id;
        $this->name = $data["name"];
        $this->data = $data;

        $query = "SELECT * FROM prstshp_product WHERE reference = {$this->id}";
        $this->stickerDB = new StickerShopDBController($this->id, $this->name, "test", "test", 20);
        $this->stickerDB->select($query);
        $this->shopProducts = $this->stickerDB->getResult();
        $this->getConnectedFiles();
    }

    public static function creatStickerImage() {
        $query = "";
        $id = DBAccess::insertQuery($query);
        return new StickerImage($id);
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET name = '$name' WHERE id = $this->id");
        echo "success";
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
        $description = $this->texts["info"];

        if ($this->data["is_short_time"] == "1" && $this->data["is_long_time"] == "1") {
            $description .= $this->texts["kurzundlang"];
        } else if ($this->data["is_short_time"] == "1") {
            $description .= $this->texts["kurzfristig"];
        } else if ($this->data["is_long_time"] == "1") {
            $description .= $this->texts["langfristig"];
        }

        if ($this->data["is_multipart"] == "1") {
            $description .= $this->texts["mehrteilig"];
        }
        
        $this->stickerDB = new StickerShopDBController($this->id, $this->name, $description, "test", 20);
        $this->stickerDB->addImages($this->getImagesAufkleber());
        $this->createCombinations();
        $this->stickerDB->addSticker();
    }

    private function generateLinks() {

    }

    public function isInShop() {
        return $this->shopProducts == null ? false : true;
    }

    public function updateSizeTable($data) {
        $width = (int) $data["width"] * 10;
        $height = (int) $data["height"] * 10;
        $query = "UPDATE module_sticker_sizes SET height = $height WHERE id_sticker = $this->id AND width = $width";
        DBAccess::updateQuery($query);
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
		$t->setType("sizes");
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
        $allFiles = DBAccess::selectQuery("SELECT dateien.dateiname, dateien.originalname AS alt, dateien.typ, dateien.id, module_sticker_images.is_aufkleber, module_sticker_images.is_wandtattoo, module_sticker_images.is_textil FROM dateien, dateien_motive, module_sticker_images WHERE dateien_motive.id_datei = dateien.id AND module_sticker_images.id_image = dateien.id AND dateien_motive.id_motive = {$this->id}");

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

    public function getImagesAufkleber() {
        $links = [];
        $images = $this->getImages();
        foreach ($images as $i) {
            if ($i["is_aufkleber"] == "1") {
                array_push($links, $i["link"]);
            }
        }
        return $links;
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

    public static function updateImageStatus() {
        $is_aufkleber = (int) $_POST["is_aufkleber"];
        $is_wandtatto = (int) $_POST["is_wandtatto"];
        $is_textil = (int) $_POST["is_textil"];

        $id_image = (int) $_POST["id_image"];

        $query = "UPDATE module_sticker_images SET is_aufkleber = $is_aufkleber, is_wandtattoo = $is_wandtatto, is_textil = $is_textil WHERE id_image = $id_image";
        DBAccess::updateQuery($query);
    }

}

?>