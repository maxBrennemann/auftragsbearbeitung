<?php

class Aufkleber extends Sticker {

    const TYPE = "aufkleber";

    private $texts = [
        "kurzfristig" => "<p>Werbeaufkleber, der für den kurzfristigen Einsatz gedacht ist und sich daher auch wieder leicht ablösen lässt.</p>",
        "langfristig" => "<p>Diesen Aufkleber gibt es&nbsp;als Hochleistungsfolie, der für langfristige Beschriftungen oder Dekorationen gedacht&nbsp;ist.</p><p>Bringe Deinen Aufkleber als Deko für Privat oder für Dein Geschäft an.</p>",
        "kurzundlang" => "<p></p><p>Diesen Aufkleber gibt es in zwei Folienvarianten:</p><ol><li>Aufkleber aus Hochleistungsfolie, der für langfristige Beschriftungen oder Dekorationen gedacht&nbsp;ist.</li><li>Werbeaufkleber, der für den kurzfristigen Einsatz gedacht ist und sich daher auch wieder leicht ablösen lässt.</li></ol><p>Der Aufkleber eignet sich gut fürs Auto oder fürs Fenster, natürlich sind auch andere Anwendungen möglich.</p>",
        "mehrteilig" => "<p>Mehrfarbige Aufkleber werden als separate Teile geliefert.<br>Die Folien werden per Plotter aus einfarbiger Folie geschnitten und müssen daher beim Kleben Farbe für Farbe angebracht werden.</p>",
    ];

    private $isShortTimeSticker = false;
    private $isLongTimeSticker = false;
    private $isMultipartSticker = false;
    private $isPlotted = false;

    private $priceClass;

    private $idShopAttributes = [];
    private $prices = [];
    private $buyingPrices = [];

    function __construct($idSticker) {
        parent::__construct($idSticker);

        /* is true, if sticker exists or is activated */
        $this->isPlotted = $this->stickerData["is_plotted"];

        /* sticker settings */
        $this->isShortTimeSticker = (int) $this->stickerData["is_short_time"];
        $this->isLongTimeSticker = (int) $this->stickerData["is_long_time"];
        $this->isMultipartSticker = (int) $this->stickerData["is_multipart"];

        $this->priceClass = (int) $this->stickerData["price_class"];
    }

    public function isInShop() {
        parent::isInShop();
        return parent::checkIsInShop(self::TYPE);
    }

    /* TODO: es muss angezeigt werden, wenn sich die Titel unterscheiden, sodass Alttitel auch wirklich absichtlich festgelegt werden können */
    public function getAltTitle($default = ""): String {
        return parent::getAltTitle(self::TYPE);
    }

    public function getIsShortTimeSticker() {
        return $this->isShortTimeSticker;
    }

    public function getIsLongTimeSticker() {
        return $this->isLongTimeSticker;
    }

    public function getIsMultipart() {
        return $this->isMultipartSticker;
    }

    public function getPriceClass() {
        return $this->priceClass;
    }

    public function getSizeSummary() {
        return $this->stickerData["size_summary"];
    }

    public function getIsPlotted() {
        return $this->isPlotted;
    }

    public function getShopLink() {
        return parent::getShopLinkHelper(self::TYPE);
    }

    public function getColors(): array {
        return []; // TODO: implement
    }

    public function getSizeToPrice(): array {
        $attributeIds = [];
        return []; // TODO: implement
    }

    public function getSizeTableFormatted() {
        return "";
    }

    public function getDescription(int $target = 1): String {
        return parent::getDescription($target);
    }

    public function getDescriptionShort(int $target = 1): String {
        return parent::getDescriptionShort($target);
    }

    public function getDescription2(int $target = 1): String {
        $descriptionEnd = parent::getDescription($target);
        $description = "<p><span>Es wird jeweils nur der entsprechende Artikel oder das einzelne Motiv verkauft. Andere auf den Bildern befindliche Dinge sind nicht Bestandteil des Angebotes.</span></p>";

        /* choose text by sticker type */
        if ($this->isShortTimeSticker && $this->isLongTimeSticker) {
            $description .= $this->texts["kurzundlang"];
        } else if ($this->isShortTimeSticker) {
            $description .= $this->texts["kurzfristig"];
        } else if ($this->isLongTimeSticker) {
            $description .= $this->texts["langfristig"];
        }

        /* append multipart text if necessary */
        if ($this->isMultipartSticker) {
            $description .= $this->texts["mehrteilig"];
        }
        
        $description .= $descriptionEnd;
        return $description;
    }

    public function getDescriptionShort2(int $target = 1): String {
        return $this->getSizeTableFormatted() . parent::getDescriptionShort($target);
    }

    public function create($param1 = "", $param2 = "") {
        parent::create($this->getDescription(), $this->getDescriptionShort());
    }

    public function save() {
        // save parent
        parent::save();

        $stickerTagManager = new StickerTagManager($this->getId());
        $stickerTagManager->saveTags($this->getTags());

        $this->connectAccessoires();
    }

    public function getAttributes() {
        $attributes = [];

        $attributes[] = $this->getSizeIds();

        /* 
         * 163 and 162 are the attribute ids from the shop for these sticker types, 
         * TODO: remove hardcoded ids or use config files
         */
        if ($this->getIsShortTimeSticker() && $this->getIsLongTimeSticker()) {
            $attributes[] = [163, 162];
        }

        /*
         * fügt ids der id_attribute_group 6 (Farbe) zurück, also alle benötigten Farben für die Aufkleber,
         * kann später durch eine Auswahloption ergänzt oder ersetzt werden, selbes TODO wie oben;
         */
        if ($this->getIsMultipart()) {
            $attributes[] = [70, 60, 67, 79, 91, 107, 111];
        }

        return $attributes;
    }

    /**
     * gibt die ids der id_attribute_group 5 (Breite) zurück,
     * dabei wird geprüft, ob zu dem Breitenwert schon eine id_attribute existiert und falls nicht,
     * wird diese erstellt
     */
    private function getSizeIds() {
        $query = "SELECT `price`, `width` FROM `module_sticker_sizes` WHERE `id_sticker` = :idSticker ORDER BY `width`";
        $data = DBAccess::selectQuery($query, ["idSticker" => $this->getId()]);

        /* TODO: hardcoded idAttributeGroup entfernen */
        $idAttributeGroup = 5;
        $sizeIds = [];
        $prices = [];
        $buyingPrices = [];

        foreach ($data as &$d) {
            $singleSizeInCm = str_replace(".", ",", ((int) $d["width"]) / 10) . "cm";
            $sizeId = (int) $this->addAttribute($idAttributeGroup, $singleSizeInCm);
            $sizeIds[] = $sizeId;

            $prices[$sizeId] = 0;
            $buyingPrices[$sizeId] = 0;
        }

        $this->idShopAttributes = $sizeIds;
        $this->prices = $prices;
        $this->buyingPrices = $buyingPrices;
        return $sizeIds;
    }

    private function addAttribute($attributeGroupId, $attributeName) {
        try {
            $xml = $this->getXML('product_option_values?filter[id_attribute_group]=' . $attributeGroupId . '&filter[name]=' . $attributeName . '&limit=1');
            $resources = $xml->children()->children();

            if (!empty($resources)) {
                $attributes = $resources->product_option_value->attributes();
                return $attributes['id'];
            }
        } catch(PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
        
        try {
            $xml = $this->getXML('/api/product_options?schema=synopsis');
            $resources = $xml->children()->children();
        
            unset($resources->id);
            $resources->{"name"} = $attributeName;
            $resources->{"id_lang"} = "de";
        
            $opt = array(
                'resource' => 'product_options',
                'postXml' => $xml->asXML()
            );

            $this->addXML($opt);
            $id = $this->xml->product->id;
            return $id;
        } catch(PrestaShopWebserviceException $e) {
            echo $e->getMessage();
        }
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

    // TODO: Aufkleber und Wandtattoo bei gleichen Funktionen zusammenführen
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

    public function getSizeTable() {
        $query = "SELECT id, width, height, price, 
                ((width / 1000) * (height / 1000) * 7.5) as costs 
            FROM module_sticker_sizes 
            WHERE id_sticker = :idSticker
            ORDER BY width";
        
        $data = DBAccess::selectQuery($query, ["idSticker" => $this->getId()]);
        $column_names = array(
            0 => array("COLUMN_NAME" => "id", "ALT" => "Nummer"),
            1 => array("COLUMN_NAME" => "width", "ALT" => "Breite"),
            2 => array("COLUMN_NAME" => "height", "ALT" => "Höhe"),
            3 => array("COLUMN_NAME" => "price", "ALT" => "Preis (brutto)"),
            4 => array("COLUMN_NAME" => "costs", "ALT" => "Material"),
        );

        $difficulty = (int) $this->getPriceClass();
        $data = $this->calculatePrices($data, $difficulty);

        foreach ($data as &$d) {
            $d["width"] = str_replace(".", ",", ((int) $d["width"]) / 10) . "cm";
            $d["height"] = str_replace(".", ",", ((int) $d["height"]) / 10) . "cm";
            $d["costs"] = number_format($d["costs"], 2, ',', '') . "€";
        }

		$t = new Table();
		$t->createByData($data, $column_names);
		$t->setType("module_sticker_sizes");
		$t->addActionButton("delete", "id");
		$t->addNewLineButton();
        $t->addAction(null, Icon::$iconReset, "Preis zurücksetzen");

        $pattern = [
            "id_sticker" => [
                "status" => "preset",
                "value" => $this->getId(),
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
            "price" => [
                "status" => "unset",
                "value" => 3,
                "type" => "float",
                "cast" => ["separator" => ","],
                "default" => null,
            ],
        ];

		$t->defineUpdateSchedule(new UpdateSchedule("module_sticker_sizes", $pattern));
        $_SESSION[$t->getTableKey()] = serialize($t);
		return $t->getTable();
    }
}

?>