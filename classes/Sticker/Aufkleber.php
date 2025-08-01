<?php

namespace Classes\Sticker;

class Aufkleber extends AufkleberWandtattoo
{
    public const TYPE = "aufkleber";

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

    public function __construct($idSticker)
    {
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

    public function isInShop()
    {
        return parent::checkIsInShop(self::TYPE);
    }

    public function getName(): String
    {
        if ($this->getAltTitle() != "") {
            return $this->getAltTitle();
        }

        return "Aufkleber " . parent::getName();
    }

    /* TODO: es muss angezeigt werden, wenn sich die Titel unterscheiden, sodass Alttitel auch wirklich absichtlich festgelegt werden können */
    public function getAltTitle($default = ""): String
    {
        return parent::getAltTitle(self::TYPE);
    }

    public function getIsShortTimeSticker()
    {
        return $this->isShortTimeSticker;
    }

    public function getIsLongTimeSticker()
    {
        return $this->isLongTimeSticker;
    }

    public function getIsMultipart()
    {
        return $this->isMultipartSticker;
    }

    public function getPriceClass()
    {
        return $this->priceClass;
    }

    public function getIsPlotted()
    {
        return $this->isPlotted;
    }

    public function getShopLink()
    {
        return parent::getShopLinkHelper(self::TYPE);
    }

    public function getColors(): array
    {
        return [
            94,
            68,
            111,
            70,
            91,
            67,
            226,
            74,
            107,
            225,
            294,
            79,
            61,
            60,
            295,
            296,
        ];
    }

    /* hardcoded color names */
    public function getColorName(int $colorId): String
    {
        $colors = [
            70 => "schwarz",
            60 => "gelb",
            67 => "rot",
            79 => "dunkelblau",
            91 => "grün",
            107 => "grau",
            111 => "weiß",
            225 => "silber",
            226 => "gold",
            74 => "pink",
            68 => "hellrot",
            294 => "lichtblau",
            94 => "gelbgrün",
            61 => "schwefelgelb",
            295 => "enzianblau",
            296 => "pastellorange",
        ];

        if (isset($colors[$colorId])) {
            return $colors[$colorId];
        }
        return "";
    }

    /**
     * sets the descriptions for the sticker with default text
     */
    private function getDescriptionWithDefaultText(): String
    {
        $descriptionEnd = $this->getDescription();
        $description = "<p><span>Es wird jeweils nur der entsprechende Artikel oder das einzelne Motiv verkauft. Andere auf den Bildern befindliche Dinge sind nicht Bestandteil des Angebotes.</span></p>";

        /* choose text by sticker type */
        if ($this->isShortTimeSticker && $this->isLongTimeSticker) {
            $description .= $this->texts["kurzundlang"];
        } elseif ($this->isShortTimeSticker) {
            $description .= $this->texts["kurzfristig"];
        } elseif ($this->isLongTimeSticker) {
            $description .= $this->texts["langfristig"];
        }

        /* append multipart text if necessary */
        if ($this->isMultipartSticker) {
            $description .= $this->texts["mehrteilig"];
        }

        $description .= $descriptionEnd;
        return $description;
    }

    public function getPurchasingPricesMatched()
    {
        return $this->buyingPrices;
    }

    /**
     * updates or saves the current sticker and uploads all
     * tags, combinations and images
     *
     * @param bool $isOverwrite if true, all images will be overwritten
     */
    public function save($isOverwrite = false): String|null
    {
        if (!$this->getIsPlotted()) {
            return null;
        }

        $errorStatus = "";

        $productId = (int) $this->getIdProduct();

        $stickerUpload = new StickerUpload($this->idSticker, $this->getName(), $this->getBasePrice(), $this->getDescriptionWithDefaultText(), $this->getDescriptionShortWithDefaultText());
        $stickerCombination = new StickerCombination($this);

        /**
         * if the product does not exist, create a new one,
         * otherwise update the existing one
         */
        if ($productId == 0) {
            $this->idProduct = $stickerUpload->createSticker();
            $productId = $this->idProduct;
        } else {
            $stickerUpload->updateSticker($productId);
            $stickerCombination->removeOldCombinations($productId);
        }

        /* set categories, duplicate entries of category ids causes errors with prestashop */
        $categories = StickerCategory::getCategoriesForSticker($this->getId());
        $defaultCategories = [2, 13];
        $mergedCategories = [...$defaultCategories, ...$categories];
        $mergedCategories = array_unique($mergedCategories);
        $stickerUpload->setCategoires($mergedCategories);

        $stickerTagManager = new StickerTagManager($this->getId(), $this->getName());
        $stickerTagManager->setProductId($productId);

        try {
            $stickerTagManager->saveTags();
        } catch (\Exception $e) {
            $errorStatus = "Fehler beim Speichern der Tags: " . $e->getMessage();
        }

        $stickerCombination->createCombinations();

        $this->connectAccessoires();

        if ($isOverwrite) {
            $this->imageData->deleteAllImages($this->idProduct);
        }

        $this->imageData->handleImageProductSync("aufkleber", $this->idProduct);

        if ($errorStatus != "") {
            return $errorStatus;
        }

        return null;
    }

    public function getAttributes()
    {
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
}
