<?php

require_once('classes/project/modules/sticker/AufkleberWandtattoo.php');

class Wandtattoo extends AufkleberWandtattoo {

    const TYPE = "wandtattoo";

    private $isWalldecal = false;

    function __construct($idWaldecal) {
        parent::__construct($idWaldecal);
        $this->instanceType = "wandtattoo";

        /* is true, if sticker exists or is activated */
        $this->isWalldecal = (int) $this->stickerData["is_walldecal"];
    }

    public function isInShop() {
        return parent::checkIsInShop(self::TYPE);
    }

    public function getName(): String {
        return "Wandtattoo " . parent::getName();
    }

    public function getIsWalldecal() {
        return $this->isWalldecal;
    }

    public function getAltTitle($default = ""): String {
        return parent::getAltTitle(self::TYPE);
    }

    public function getShopLink() {
        return parent::getShopLinkHelper(self::TYPE);
    }

    public function save() {
        $productId = (int) $this->getIdProduct();
        $stickerUpload = new StickerUpload($this->idSticker, $this->getName(), $this->getBasePrice(), $this->getDescription(), $this->getDescriptionShortWithDefaultText());

        if ($productId == 0) {
            $stickerUpload->createSticker();
        } else {
            $stickerUpload->updateSticker($productId);
        }
        $stickerUpload->setCategoires([2, 62, 13]);
        
        $stickerTagManager = new StickerTagManager($this->getId(), $this->getName());
        $stickerTagManager->setProductId($this->idProduct);
        $stickerTagManager->saveTags();

        $stickerCombination = new StickerCombination($this);
        $stickerCombination->createCombinations();
        
        $this->connectAccessoires();

        $images = $this->imageData->getWandtattooImages();
        $this->uploadImages($images);
    }

    public function getAttributes() {
        return [$this->getSizeIds()];
    }

}

?>