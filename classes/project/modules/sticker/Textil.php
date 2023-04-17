<?php

require_once("classes/project/modules/sticker/Sticker.php");

class Textil extends Sticker {

    const TYPE = "textil";

    /*
     * SELECT color, prstshp_attribute_lang.name FROM `prstshp_attribute`, prstshp_attribute_lang WHERE prstshp_attribute.id_attribute = prstshp_attribute_lang.id_attribute AND id_attribute_group = 11 AND prstshp_attribute_lang.id_lang = 1; 
     * TODO: read from shop and cache
     */
    public $textilColors = [
        ["hexCol" => "#ffffff", "name" => "Weiss"],
        ["hexCol" => "#FCCC00", "name" => "Maisgelb"],
        ["hexCol" => "#F5E61A", "name" => "Gelb"],
        ["hexCol" => "#910C19", "name" => "Rot"],
        ["hexCol" => "#DB3400", "name" => "Orange"],
        ["hexCol" => "#000000", "name" => "Schwarz"],
        ["hexCol" => "#11307D", "name" => "Königsblau"],
        ["hexCol" => "#0053AA", "name" => "Enzianblau"],
        ["hexCol" => "#009999", "name" => "Helltuerkis"],
        ["hexCol" => "#004429", "name" => "Dunkelgruen"],
        ["hexCol" => "#008955", "name" => "Hellgruen"],
        ["hexCol" => "#60C340", "name" => "Apfelgruen"],
        ["hexCol" => "#45291E", "name" => "Braun"],
        ["hexCol" => "#2C2E31", "name" => "Anthrazit"],
        ["hexCol" => "#748289", "name" => "Silber"],
        ["hexCol" => "#878A8D", "name" => "Grau"],
        ["hexCol" => "#ccff00", "name" => "Neongelb"],
        ["hexCol" => "#00ff00", "name" => "Neongruen"],
        ["hexCol" => "#fd5f00", "name" => "Neonorange"],
        ["hexCol" => "#ff019a", "name" => "Neonpink"],
    ];

    private $isShirtcollection = false;
    private $isColorable = false;

    function __construct($idTextile) {
        parent::__construct($idTextile);
        $this->instanceType = "textil";

        /* is true, if sticker exists or is activated */
        $this->isShirtcollection = (int) $this->stickerData["is_shirtcollection"];
        $this->isColorable = (int) $this->stickerData["is_colorable"];
    }

    public function isInShop() {
        return parent::checkIsInShop(self::TYPE);
    }

    public function getName(): String {
        return "Textil " . parent::getName();
    }

    public function getIsShirtcollection() {
        return $this->isShirtcollection;
    }

    public function getIsColorable() {
        return $this->isColorable;
    }

    public function getAltTitle($default = ""): String {
        return parent::getAltTitle(self::TYPE);
    }

    public function getShopLink() {
        return parent::getShopLinkHelper(self::TYPE);
    }

    public function getPriceTextilFormatted() {
        $price = number_format($this->getPrice(), 2, ',', '') . "€";
        return $price;
    }

    private function getPrice() {
        switch ($this->stickerData["price_type"]) {
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

    public function toggleIsColorable() {
        DBAccess::updateQuery("UPDATE `module_sticker_sticker_data` SET `is_colorable` = NOT `is_colorable` WHERE id = :id", ["id" => $this->getId()]);
        $this->isColorable = !$this->isColorable;
    }

    /* returns the image array for the current svg */
    public function getCurrentSVG() {
        return $this->imageData->getTextilSVG($this->isColorable);
    }

    private function uploadSVG() {
        $url = $this->url . "?upload=svg&id=" . $this->idProduct;
        $currentSVG = $this->getCurrentSVG();
        $filename = "upload/" . $currentSVG["dateiname"];
        $cImage = new CurlFile($filename, 'image/svg+xml', "image");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => $cImage));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        echo $result;
    }

    public function uploadImages($imageURLs, $idProduct) {
        parent::uploadImages($imageURLs, $idProduct);
        $this->uploadSVG();
    }

    /**
     * aktuell ist der basePrice bei Textilien gleich dem Endpreis, muss später noch geändert werden
     * TODO: Textilpreise dynamisch gestalten
     */
    public function getBasePrice() {
        return $this->getPrice();
    }

    /**
     * idCategory für Textilien ist 25
     * TODO: überarbeiten, da hardcoded
     */
    public function getIdCategory() {
        return 25;
    }

    public function save() {
        if (!$this->getIsShirtcollection()) {
            return;
        }

        $productId = (int) $this->getIdProduct();
        $category = 25;
        $stickerUpload = new StickerUpload($this->idSticker, $this->getName(), $this->getBasePrice(), $this->getDescription(), $this->getDescriptionShort());
        $stickerUpload->setIdCategoryPrimary($category);

        $stickerCombination = new StickerCombination($this);

        if ($productId == 0) {
            $this->idProduct = $productId = $stickerUpload->createSticker();
        } else {
            $stickerUpload->updateSticker($productId);
        }
        
        $stickerCombination->removeOldCombinations($productId);

        $stickerTagManager = new StickerTagManager($this->getId(), $this->getName());
        $stickerTagManager->setProductId($productId);
        $stickerTagManager->saveTags();

        $stickerCombination->createCombinations();
        
        $this->connectAccessoires();

        $this->uploadSVG();
        $images = $this->imageData->getTextilImages();
        $this->uploadImages($images, $this->idProduct);
    }

    /**
     * returns hardcoded color ids
     */
    public function getAttributes() {
        if ($this->getIsColorable()) {
            return [
                [164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183]
            ];
        }
        return [];
    }

}

?>