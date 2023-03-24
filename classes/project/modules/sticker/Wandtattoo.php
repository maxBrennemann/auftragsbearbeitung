<?php

require_once('classes/project/modules/sticker/AufkleberWandtattoo.php');

class Wandtattoo extends AufkleberWandtattoo {

    const TYPE = "wandtattoo";

    private $isWalldecal = false;

    function __construct($idWaldecal) {
        parent::__construct($idWaldecal);

        /* is true, if sticker exists or is activated */
        $this->isWalldecal = (int) $this->stickerData["is_walldecal"];
    }

    public function isInShop() {
        parent::isInShop();
        return parent::checkIsInShop(self::TYPE);
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

}

?>