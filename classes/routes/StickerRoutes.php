<?php

require_once("classes/routes/Routes.php");
require_once("classes/project/modules/sticker/StickerCollection.php");
require_once("classes/project/modules/sticker/StickerTagManager.php");

class StickerRoutes extends Routes
{

    /**
     * @uses StickerCollection::getStickerStatus
     * 
     * @uses StickerTagManager::getTagSuggestions
     */
    protected static $getRoutes = [
        "/sticker/{id}/status" => "StickerCollection::getStickerStatus",

        "/sticker/tags" => "",
        "/sticker/tags/crawl" => "",
        "/sticker/tags/suggestions" => "StickerTagManager::getTagSuggestions",
        "/sticker/tags/groups" => "",
    ];

    /**
     * @uses StickerCollection::exportSticker
     * 
     * @uses StickerTagManager::addTag
     */
    protected static $postRoutes = [
        "/sticker/{id}/export" => "StickerCollection::exportSticker",

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
