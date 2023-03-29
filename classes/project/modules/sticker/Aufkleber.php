<?php

require_once('classes/project/modules/sticker/AufkleberWandtattoo.php');

class Aufkleber extends AufkleberWandtattoo {

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
        $this->instanceType = "aufkleber";

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

    private function getDescriptionWithDefaultText(): String {
        $descriptionEnd = $this->getDescription();
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

    private function getDescriptionShortWithDefaultText(): String {
        return $this->getSizeTableFormatted() . $this->getDescriptionShort();
    }

    public function getPricesMatched() {
        return $this->prices;
    }

    public function getPurchasingPricesMatched() {
        return $this->buyingPrices;
    }

    public function save() {
        $productId = (int) $this->getIdProduct();

        if ($productId == 0) {
            $this->idProduct = StickerUpload::createSticker($this->idSticker, $this->getName(), $this->getBasePrice(), $this->getDescriptionWithDefaultText(), $this->getDescriptionShortWithDefaultText());
        } else {
            StickerUpload::updateSticker($this->idSticker, $productId, $this->getName(), $this->getBasePrice(), $this->getDescriptionWithDefaultText(), $this->getDescriptionShortWithDefaultText());
        }
        
        $stickerTagManager = new StickerTagManager($this->getId(), $this->getName());
        $stickerTagManager->setProductId($this->idProduct);
        $stickerTagManager->saveTags();

        $stickerCombination = new StickerCombination($this);
        $stickerCombination->createCombinations();
        
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
        $query = "SELECT `price`, `width`, ((`width` / 1000) * (`height` / 1000) * 10) as `costs` FROM `module_sticker_sizes` WHERE `id_sticker` = :idSticker ORDER BY `width`";
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

            $prices[$sizeId] = $d["price"];
            $buyingPrices[$sizeId] = $d["costs"];
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

    public function getSizeTable() {
        $query = "SELECT id, width, height, price, 
                ((width / 1000) * (height / 1000) * 10) as costs 
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

        foreach ($data as &$d) {
            $d["width"] = str_replace(".", ",", ((int) $d["width"]) / 10) . "cm";
            $d["height"] = str_replace(".", ",", ((int) $d["height"]) / 10) . "cm";
            $d["price"] = number_format((float) $d["price"] / 100, 2, ',', '') . "€";
            $d["costs"] = number_format((float) $d["costs"] / 100, 2, ',', '') . "€";
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