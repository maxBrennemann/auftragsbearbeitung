<?php

require_once "classes/routes/Routes.php";
require_once "classes/project/modules/sticker/StickerCollection.php";
require_once "classes/project/modules/sticker/StickerTagManager.php";
require_once "classes/project/modules/sticker/Textil.php";

class StickerRoutes extends Routes
{

    /**
     * @uses StickerCollection::getStickerStatus
     * 
     * @uses StickerTagManager::getTagSuggestions
     * 
     * @uses StickerCollection::getStickerOverview
     */
    protected static $getRoutes = [
        "/sticker/{id}/status" => "StickerCollection::getStickerStatus",

        "/sticker/tags" => "",
        "/sticker/tags/crawl" => "",
        "/sticker/tags/suggestions" => "StickerTagManager::getTagSuggestions",
        "/sticker/tags/groups" => "",

        "/sticker/overview" => "StickerCollection::getStickerOverview",
    ];

    /**
     * @uses StickerCollection::exportSticker
     * @uses Textil::toggleTextile
     * @uses Textil::setPrice
     * 
     * @uses StickerCollection::addSticker
     * 
     * @uses StickerTagManager::addTag
     */
    protected static $postRoutes = [
        "/sticker/{id}/export" => "StickerCollection::exportSticker",
        "/sticker/{id}/textile/{idTextile}/toggle" => "Textil::toggleTextile",
        "/sticker/{id}/textile/{idTextile}/price" => "Textil::setPrice",

        "/sticker" => "StickerCollection::addSticker",

        "/sticker/tags" => "StickerTagManager::addTag",
        "/sticker/tags/groups" => "",
    ];

    /**
     * @uses StickerTagManager::removeTag
     */
    protected static $deleteRoutes = [
        
        "/sticker/tags" => "StickerTagManager::removeTag",
    ];

    public function __construct()
    {
        parent::__construct();
    }
}
