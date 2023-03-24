<?php

require_once('classes/project/StickerShopDBController.php');
require_once('classes/project/modules/sticker/StickerChangelog.php');

class StickerImage {

    private $id;
    private $name;

    public $data;

    private $shopProducts;
    private $stickerDB;

    private $allFiles = [];
    private $images = [];
    private $files = [];

    public $descriptions;

    private $texts = [
        "kurzfristig" => "<p>Werbeaufkleber, der für den kurzfristigen Einsatz gedacht ist und sich daher auch wieder leicht ablösen lässt.</p>",
        "langfristig" => "<p>Diesen Aufkleber gibt es&nbsp;als Hochleistungsfolie, der für langfristige Beschriftungen oder Dekorationen gedacht&nbsp;ist.</p><p>Bringe Deinen Aufkleber als Deko für Privat oder für Dein Geschäft an.</p>",
        "kurzundlang" => "<p></p><p>Diesen Aufkleber gibt es in zwei Folienvarianten:</p><ol><li>Aufkleber aus Hochleistungsfolie, der für langfristige Beschriftungen oder Dekorationen gedacht&nbsp;ist.</li><li>Werbeaufkleber, der für den kurzfristigen Einsatz gedacht ist und sich daher auch wieder leicht ablösen lässt.</li></ol><p>Der Aufkleber eignet sich gut fürs Auto oder fürs Fenster, natürlich sind auch andere Anwendungen möglich.</p>",
        "mehrteilig" => "<p>Mehrfarbige Aufkleber werden als separate Teile geliefert.<br>Die Folien werden per Plotter aus einfarbiger Folie geschnitten und müssen daher beim Kleben Farbe für Farbe angebracht werden.</p>",
        "info" => "<p><span>Es wird jeweils nur der entsprechende Artikel oder das einzelne Motiv verkauft. Andere auf den Bildern befindliche Dinge sind nicht Bestandteil des Angebotes.</span></p>",
    ];

    function __construct($id) {
        $query = "SELECT * FROM module_sticker_sticker_data WHERE id = $id LIMIT 1";
        $data = DBAccess::selectQuery($query);
        if ($data == null) {
            $this->id = 0;
            return null;
        }
        $data = $data[0];

        $this->id = $id;
        $this->name = $data["name"];
        $this->data = $data;

        $this->getConnectedFiles();
        $this->descriptions = [
            1 => $this->getDescriptions(1),
            2 => $this->getDescriptions(2),
            3 => $this->getDescriptions(3),
        ];

        $this->shopProducts = json_decode($data["additional_data"], true);
    }

    public static function creatStickerImage() {
        $query = "";
        $id = DBAccess::insertQuery($query);
        return new StickerImage($id);
    }

    public function getShopProducts($type, $data) {
        if (isset($this->shopProducts["products"][$type])) {
            if (isset($this->shopProducts["products"][$type][$data])) {
                return $this->shopProducts["products"][$type][$data];
            }
        }
        return "#";
    }

    public function getName() {
        return $this->name;
    }

    public function getId() {
        return $this->id;
    }

    public function setName($name) {
        DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET `name` = '$name' WHERE id = $this->id");
        echo "success";

        StickerChangelog::log($this->id, "", $this->id, "module_sticker_sticker_data", "name", $name);
    }

    private function getPriceTextil() {
        switch ($this->data["price_type"]) {
            case "57":
                $price = "23.59";
                break;
            case "58":
                $price = "20.52";
                break;
            case "59":
                $price = "30.78";
                break;
            case "60":
                $price = "33.85";
                break;
            default:
                $price = 0;
        }
        return $price;
    }

    public function saveAufkleber() {
        if ($this->data["is_plotted"] == "0") {
            return;
        }
        $this->generateAufkleber();
    }

    /**
     * TODO: remove hardcoded categories
     */
    public function saveWandtattoo() {
        if ($this->data["is_walldecal"] == "0") {
            return;
        }

        $descriptions = $this->getDescriptions(2);
        $descriptionShort = $this->data["size_summary"] . $descriptions["short"];

        $query = "SELECT id, width, height, price FROM module_sticker_sizes WHERE id_sticker = {$this->id} ORDER BY width";
        $data = DBAccess::selectQuery($query);
        $difficulty = (int) $this->data["price_class"];
        $data = $this->calculatePrices($data, $difficulty, false);

        $prices = [];
        $buyingPrices = [];
        $sizes = $this->getSizeIds();
        for ($i = 0; $i < sizeof($data); $i++) {
            $price = $data[$i]["price"] / 100;
            array_push($prices, [$sizes["ids"][$i] => $price]);
            array_push($buyingPrices, 0);
        }

        $this->stickerDB = new StickerShopDBController($this->id, "Wandtattoo " . $this->name, $descriptions["long"], $descriptionShort, 20);

        $this->stickerDB->addTags(["Wandtattoo", "Sticker", "Motiv"]);

        /* TODO: muss später so hinzugefügt werden, erst product erstellen und dann tags hinzufügen */
        //$tags = new StickerTagManager($this->id);
        //$tags->saveTags();

        $this->stickerDB->prices = $prices;
        $this->stickerDB->buyingPrices = $buyingPrices;
        $this->stickerDB->addImages($this->getImagesByType("is_wandtattoo"));
        $this->stickerDB->addAttributeArray($this->getSizeIds()["ids"]);
        $this->stickerDB->addSticker();

        /* Wandtattoo und Aufkleber Kategorien */
        $this->stickerDB->setCategory([62, 13]);
    }

    /**
     * @param currency when true, then the price is returned in € with tax, otherwise its a float without tax
     */
    public function calculatePrices($priceTable, $difficulty, $currency = true) {
        foreach($priceTable as &$size) {
            /* leeres Tabellenfeld heißt, dass der berechnete Wert verwendet werden soll */
            if ($size["price"] == null) {
                $size["price"] = $this->getPrice($size["width"], $size["height"], $difficulty);
            }

            if ($currency) {
                $size["price"] = number_format($size["price"] / 100, 2, ',', '') . "€";
            } else {
                $size["price"] = number_format($size["price"] / 100 / 1.19, 2);
            }
        }

        return $priceTable;
    }

    public function getPrice($width, $height, $difficulty) {
        if ($width >= 1200) {
            $base = 2100;
        } else if ($width >= 900) {
            $base = 1950;
        } else if ($width >= 600) {
            $base = 1700;
        } else if ($width >= 300) {
            $base = 1500;
        } else {
            $base = 1200;
        }

        $base = $base + 200 * $difficulty;
        if ($height >= 0.5 * $width) {
            $base += 100;
        }
        
        return $base;
    }

    /**
     * TODO: remove hardcoded ids;
     */
    public function saveTextil() {
        if ($this->data["is_shirtcollection"] == "0") {
            return;
        }

        $descriptions = $this->getDescriptions(3);
        $this->stickerDB = new StickerShopDBController($this->id, "Textil " . $this->name, $descriptions["long"], $descriptions["short"], $this->getPriceTextil());

        $this->stickerDB->addTags(["Textil", "Shirt", "Motiv", "Druck"]);

        $this->stickerDB->addImages($this->getImagesByType("is_textil"));

        /* if the textil is not colorable, the color options must not be set */
        if ($this->data["is_colorable"] == "1") {
            $this->stickerDB->addAttributeArray([164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183]);
        }
       
        $this->stickerDB->addSticker(25);

        $this->stickerDB->uploadSVG($this->getSVG());
        $this->stickerDB->setCategory([25]);
    }

    /**
     * this function generates a new sticker in the shop 
     */
    private function generateAufkleber() {
        $descriptions = $this->getDescriptions(1);
        $description = $this->texts["info"];

        /* figering out which description is right */
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
        
        $description .= $descriptions["long"];
        $descriptionShort = $this->data["size_summary"] . $descriptions["short"];

        $query = "SELECT id, width, height, price FROM module_sticker_sizes WHERE id_sticker = {$this->id} ORDER BY width";
        $data = DBAccess::selectQuery($query);
        $difficulty = (int) $this->data["price_class"];
        $data = $this->calculatePrices($data, $difficulty, false);
        /* TODO: correct price calculation or correct format, currently just for rounding */
        if (isset($data[0])) {
            $basePrice = $data[0]["price"];
        } else {
            $basePrice = 0;
        }
        $basePrice = number_format($basePrice, 4);

        $this->stickerDB = new StickerShopDBController($this->id, "Aufkleber " . $this->name, $description, $descriptionShort, $basePrice);
        $this->stickerDB->addTags(["Aufkleber", "Sticker", "Motiv"]);

        if ($this->data["is_short_time"] == "1" && $this->data["is_long_time"] == "1") {
            $this->stickerDB->addAttributeArray([163, 162]);
        }

        $prices = [];
        $sizes = $this->getSizeIds();
        for ($i = 0; $i < sizeof($data); $i++) {
            $price = $data[$i]["price"];
            $price = (float) $price - $basePrice;
            $prices[$sizes["ids"][$i]] = number_format($price, 4);
        }

        $this->stickerDB->prices = $prices;
        $this->stickerDB->addImages($this->getImagesByType("is_aufkleber"));

        /* only if is_multipart is false, add colors */
        $this->addSizesToProduct();
        if ($this->data["is_multipart"] == "0") {
            $this->addColorsToProduct();
        }

        $this->stickerDB->addSticker();

        $this->stickerDB->setCategory([2, 13]);
    }

    public function getDescriptions($target) {
        $description = DBAccess::selectQuery("SELECT content, `type` FROM module_sticker_texts WHERE id_sticker = $this->id AND `target` = $target AND `type` = 'long'");
        if ($description != null) {
            $description = $description[0]["content"];
        } else {
            $description = "";
        }

        $descriptionShort = DBAccess::selectQuery("SELECT content, `type` FROM module_sticker_texts WHERE id_sticker = $this->id AND `target` = $target AND `type` = 'short'");
        if ($descriptionShort != null) {
            $descriptionShort = $descriptionShort[0]["content"];
        } else {
            $descriptionShort = "";
        }

        return ["long" => $description, "short" => $descriptionShort];
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

    /**
     * sets new height for a sticker,
     * adjusts price if necessary,
     * writes to changelog
     */
    public function updateSizeTable($data) {
        $width = (int) $data["width"];
        $height = (int) $data["height"];
        
        $difficulty = (int) $this->data["price_class"];
        $currentPrice = $this->getPrice($width, $height, $difficulty);

        $query = "UPDATE module_sticker_sizes SET height = $height, price = NULL WHERE id_sticker = $this->id AND width = $width";
        
        StickerChangelog::log($this->id, "", 0, "module_sticker_sizes", "height", $height);
        echo "preis: " . $currentPrice . " " . $data["price"] . " ";

        if ($currentPrice != $data["price"]) {
            $price = $data["price"];
            $query = "UPDATE module_sticker_sizes SET height = $height, price = $price WHERE id_sticker = $this->id AND width = $width";
            StickerChangelog::log($this->id, "", 0, "module_sticker_sizes", "price", $price);
        }

        DBAccess::updateQuery($query);
    }

    public function updatePrice(int $width, int $height, int $price) {
        $difficulty = (int) $this->data["price_class"];
        $currentPrice = $this->getPrice($width, $height, $difficulty);

        if ($currentPrice != $price) {
            $query = "UPDATE module_sticker_sizes SET price = $price WHERE id_sticker = $this->id AND width = $width";
            StickerChangelog::log($this->id, "", 0, "module_sticker_sizes", "price", $price);
        }

        DBAccess::updateQuery($query);
    }

    private function getConnectedFiles() {
        $allFiles = DBAccess::selectQuery("SELECT dateien.dateiname, dateien.originalname AS alt, dateien.typ, dateien.id, module_sticker_images.is_aufkleber, module_sticker_images.is_wandtattoo, module_sticker_images.is_textil FROM dateien, dateien_motive, module_sticker_images WHERE dateien_motive.id_datei = dateien.id AND module_sticker_images.id_image = dateien.id AND dateien_motive.id_motive = {$this->id}");

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

    public function getImagesByType($type) {
        $links = [];
        $images = $this->getImages();
        foreach ($images as $i) {
            if ($i[$type] == "1") {
                array_push($links, WEB_URL . $i["link"]);
                $this->resizeImage($i);
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
                    "link" => Link::getResourcesShortLink("default_image.png", "img"),
                    "dateiname" => "Standardbild",
                    "typ" => "png",
                    "is_aufkleber" => 0,
                    "is_wandtattoo" => 0,
                    "is_textil" => 0,
                ],
            ];
        }

        return $this->images;
    }

    public function getFiles() {
        if (sizeof($this->files) == 0) {
            return;
        }
        $download = "<p>Download ";
        foreach ($this->files as $f) {
            $link = Link::getResourcesShortLink($f["dateiname"], "upload");
            $filename = $f["dateiname"];
            $originalname = $f["alt"] ?: "ohne Name";
            $id = $f["id"];
            $download .= "<a class=\"imageTag\" data-image-id=\"$id\" download=\"$filename\" data-deletable=\"true\" href=\"$link\" title=\"Zum Herunterladen von '$originalname' klicken\">(" . $originalname . ") " . strtoupper($f["typ"]) . "</a> ";
        }
        return $download . "</p>";
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

    /* save sticker fields */
    public function saveSentData($jsonData) {
        $data = json_decode($jsonData);
        switch ($data->name) {
            case "plotted":
                $column = "is_plotted";
                $newVal = $data->plotted;
                if ($newVal == "0") {
                    DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET `is_short_time` = 0 WHERE id = {$this->id}");
                    DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET `is_long_time` = 0 WHERE id = {$this->id}");
                    DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET `is_multipart` = 0 WHERE id = {$this->id}");
                }
                break;
            case "short":
                $column = "is_short_time";
                $newVal = $data->short;
                break;
            case "long":
                $column = "is_long_time";
                $newVal = $data->long;
                break;
            case "multi":
                $column = "is_multipart";
                $newVal = $data->multi;
                break;
            default:
                echo "error";
                return;
        }

        $newVal = (int) $newVal;
        $query = "UPDATE module_sticker_sticker_data SET `$column` = $newVal WHERE id = {$this->id}";
        DBAccess::updateQuery($query);
        echo "success";
    }

    /**
     * gibt die ids der id_attribute_group 5 (Breite) zurück,
     * dabei wird geprüft, ob zu dem Breitenwert schon eine id_attribute existiert und falls nicht,
     * wird diese erstellt
     */
    public function getSizeIds() {
        $query = "SELECT width FROM module_sticker_sizes WHERE id_sticker = {$this->id} ORDER BY width";
        $data = DBAccess::selectQuery($query);

        $sizesInCm = [];
        foreach ($data as &$d) {
            $singleSizeInCm = str_replace(".", ",", ((int) $d["width"]) / 10) . "cm";
            array_push($sizesInCm, $singleSizeInCm);
        }

        $sizeIds = array();
        foreach ($sizesInCm as $sizeInCm) {
            $sizeId = (int) $this->stickerDB->addAttribute("5", $sizeInCm);
            array_push($sizeIds, $sizeId);
        }

        return ["id" => 5, "ids" => $sizeIds, "sizes" => $sizesInCm];
    }

    /**
     * gibt die ids der id_attribute_group 6 (Farbe) zurück, also alle benötigten Farben für die Aufkleber,
     * kann später durch eine Auswahloption ergänzt oder ersetzt werden;
     */
    public function getColorIds() {
        return ["id" => 6, "ids" => [70, 60, 67, 79, 91, 107, 111]];
    }

    public function addSizesToProduct() {
        $this->stickerDB->addAttributeArray($this->getSizeIds()["ids"]);
    }

    public function addColorsToProduct() {
        $this->stickerDB->addAttributeArray($this->getColorIds()["ids"]);
    }

    public static function updateImageStatus() {
        $is_aufkleber = (int) $_POST["is_aufkleber"];
        $is_wandtatto = (int) $_POST["is_wandtatto"];
        $is_textil = (int) $_POST["is_textil"];

        $id_image = (int) $_POST["id_image"];

        $query = "UPDATE module_sticker_images SET is_aufkleber = $is_aufkleber, is_wandtattoo = $is_wandtatto, is_textil = $is_textil WHERE id_image = $id_image";
        DBAccess::updateQuery($query);
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

}

?>