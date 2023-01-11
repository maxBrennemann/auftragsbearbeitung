<?php

require_once('classes/project/modules/sticker/StickerShopUpload.php');
require_once('classes/project/modules/sticker/StickerExport.php');
require_once('classes/project/modules/sticker/StickerChangelog.php');

class StickerTag implements StickerShopUpload, StickerExport {

    private $idProduct;
    private $idTag;
    private $value;

    function __construct($idProduct, $idTag, $value = "") {

    }

    public function get() {

    }

    public function change() {
        StickerChangelog::log($this->idProduct, "", 0, "", "", "");
    }

    public function remove() {

    }

    public function saveChanges() {

    }
}

?>