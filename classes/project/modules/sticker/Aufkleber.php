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
}

?>