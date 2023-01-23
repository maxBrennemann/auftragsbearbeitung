<?php

class StickerController {

    private $stickerCollection;

    function __construct() {
        $id = 0;
        $this->stickerCollection = new StickerCollection($id);
    }

    public function getImages() {
        
    }

    public static function updateAll() {

    }

    private function createAll() {
        $this->stickerCollection->createAll();
        foreach ($this->stickerCollection as $product) {
            $product->create();
            $product->uploadImages();
            $product->setCategory();
            $product->createCombinations();
        }
    }

    public static function updateFileLocation() {

    }

    public static function updateNotes() {

    }


}

?>