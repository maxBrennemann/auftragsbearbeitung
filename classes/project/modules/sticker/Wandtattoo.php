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

    public function save($isOverwrite = false) {
        if (!$this->getIsWalldecal()) {
            return;
        }

        $productId = (int) $this->getIdProduct();
        $stickerUpload = new StickerUpload($this->idSticker, $this->getName(), $this->getBasePrice(), $this->getDescription(), $this->getDescriptionShortWithDefaultText());

        $stickerCombination = new StickerCombination($this);

        if ($productId == 0) {
            $this->idProduct = $productId = $stickerUpload->createSticker();
        } else {
            $stickerUpload->updateSticker($productId);
        }
        $stickerUpload->setCategoires([2, 62, 13]);

        $stickerCombination->removeOldCombinations($productId);
        
        $stickerTagManager = new StickerTagManager($this->getId(), $this->getName());
        $stickerTagManager->setProductId($productId);
        $stickerTagManager->saveTags();

        $stickerCombination->createCombinations();
        
        $this->connectAccessoires();

        if ($isOverwrite) {
            $this->imageData->deleteAllImages($this->idProduct);
        }

        $this->imageData->handleImageProductSync("wandtattoo", $this->idProduct);
    }

    public function getAttributes() {
        return [$this->getSizeIds()];
    }

}
