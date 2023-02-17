<?php

class Aufkleber extends Sticker {

    private $texts = [
        "kurzfristig" => "<p>Werbeaufkleber, der für den kurzfristigen Einsatz gedacht ist und sich daher auch wieder leicht ablösen lässt.</p>",
        "langfristig" => "<p>Diesen Aufkleber gibt es&nbsp;als Hochleistungsfolie, der für langfristige Beschriftungen oder Dekorationen gedacht&nbsp;ist.</p><p>Bringe Deinen Aufkleber als Deko für Privat oder für Dein Geschäft an.</p>",
        "kurzundlang" => "<p></p><p>Diesen Aufkleber gibt es in zwei Folienvarianten:</p><ol><li>Aufkleber aus Hochleistungsfolie, der für langfristige Beschriftungen oder Dekorationen gedacht&nbsp;ist.</li><li>Werbeaufkleber, der für den kurzfristigen Einsatz gedacht ist und sich daher auch wieder leicht ablösen lässt.</li></ol><p>Der Aufkleber eignet sich gut fürs Auto oder fürs Fenster, natürlich sind auch andere Anwendungen möglich.</p>",
        "mehrteilig" => "<p>Mehrfarbige Aufkleber werden als separate Teile geliefert.<br>Die Folien werden per Plotter aus einfarbiger Folie geschnitten und müssen daher beim Kleben Farbe für Farbe angebracht werden.</p>",
    ];

    private $isShortTimeSticker = false;
    private $isLongTimeSticker = false;
    private $isMultipartSticker = false;

    function __construct($idSticker) {
        // test
        $this->idProduct = 810;
        parent::__construct($idSticker);
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

    public function getColors(): array {
        return []; // TODO: implement
    }

    public function getSiezToPrice(): array {
        return []; // TODO: implement
    }

    public function getSizeTableFormatted() {
        return "";
    }

    public function getDescription(int $target = 1): String {
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

    public function getDescriptionShort(int $target = 1): String {
        return $this->getSizeTableFormatted() . parent::getDescriptionShort($target);
    }

    public function create($param1 = "", $param2 = "") {
        parent::create($this->getDescription(), $this->getDescriptionShort());
    }
}

?>