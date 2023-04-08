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
        return parent::checkIsInShop(self::TYPE);
    }

    public function getName(): String {
        return "Aufkleber " . parent::getName();
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
        return [70, 60, 67, 79, 91, 107, 111];
    }

    public function getSizeToPrice(): array {
        $attributeIds = [];
        return []; // TODO: implement
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

    public function getPurchasingPricesMatched() {
        return $this->buyingPrices;
    }

    public function save() {
        $productId = (int) $this->getIdProduct();
        $stickerUpload = new StickerUpload($this->idSticker, $this->getName(), $this->getBasePrice(), $this->getDescriptionWithDefaultText(), $this->getDescriptionShortWithDefaultText());

        if ($productId == 0) {
            $stickerUpload->createSticker();
        } else {
            $stickerUpload->updateSticker($productId);
        }
        $stickerUpload->setCategoires([2, 13]);

        $stickerCombination = new StickerCombination($this);
        $stickerCombination->removeOldCombinations($this->getIdProduct());
        
        $stickerTagManager = new StickerTagManager($this->getId(), $this->getName());
        $stickerTagManager->setProductId($this->idProduct);
        $stickerTagManager->saveTags();
        
        $stickerCombination->createCombinations();
        
        $this->connectAccessoires();

        $images = $this->imageData->getAufkleberImages();
        $this->uploadImages($images, $this->idProduct);
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
        if (!$this->getIsMultipart()) {
            $attributes[] = $this->getColors();
        }

        return $attributes;
    }

        /* save sticker fields */
    public function saveSentData($jsonData) {
        $data = json_decode($jsonData);
        switch ($data->name) {
            case "plotted":
                $column = "is_plotted";
                $newVal = $data->plotted;
                if ($newVal == "0") {
                    DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET `is_short_time` = 0 WHERE id = :id", ["id" => $this->getId()]);
                    DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET `is_long_time` = 0 WHERE id = :id", ["id" => $this->getId()]);
                    DBAccess::updateQuery("UPDATE module_sticker_sticker_data SET `is_multipart` = 0 WHERE id = :id", ["id" => $this->getId()]);
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
        $query = "UPDATE module_sticker_sticker_data SET `$column` = $newVal WHERE id = :id";
        DBAccess::updateQuery($query, ["id" => $this->getId()]);
        echo "success";
    }
    
}

?>