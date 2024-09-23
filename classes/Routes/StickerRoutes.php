<?php

namespace Classes\Routes;

class StickerRoutes extends Routes
{

    /**
     * @uses StickerCollection::getStickerStatus
     * @uses ChatGPTConnection::iterateText
     * @uses StickerCollection::getStickerSizes
     * @uses StickerCollection::getPriceScheme
     * 
     * @uses StickerTagManager::getTagSuggestions
     * 
     * @uses StickerCollection::getStickerOverview
     * @uses StickerCollection::getStickerStates
     */
    protected static $getRoutes = [
        "/sticker/{id}/status" => "StickerCollection::getStickerStatus",
        "/sticker/{id}/texts/{type}/{form}" => "ChatGPTConnection::iterateText",
        "/sticker/{id}/sizes" => "StickerCollection::getStickerSizes",
        "/sticker/{id}/priceScheme" => [StickerCollection::class, "getPriceScheme"],

        "/sticker/tags" => "",
        "/sticker/tags/crawl" => "",
        "/sticker/tags/suggestions" => "StickerTagManager::getTagSuggestions",
        "/sticker/tags/groups" => "",

        "/sticker/overview" => "StickerCollection::getStickerOverview",
        "/sticker/states" => "StickerCollection::getStickerStates",
    ];

    /**
     * @uses StickerCollection::exportSticker
     * @uses Textil::toggleTextile
     * @uses Textil::setPrice
     * @uses StickerCollection::setPriceScheme
     * 
     * @uses StickerCollection::addSticker
     * 
     * @uses StickerTagManager::addTag
     * 
     * @uses 
     */
    protected static $postRoutes = [
        "/sticker/{id}/export" => "StickerCollection::exportSticker",
        "/sticker/{id}/textile/{idTextile}/toggle" => "Textil::toggleTextile",
        "/sticker/{id}/textile/{idTextile}/price" => "Textil::setPrice",
        "/sticker/{id}/priceScheme" => [StickerCollection::class, "setPriceScheme"],

        "/sticker/{id}/texts/{type}/{form}" => "ChatGPTConnection::newText",

        "/sticker" => "StickerCollection::addSticker",

        "/sticker/tags" => "StickerTagManager::addTag",
        "/sticker/tags/groups" => "",

        "/sticker/sizes/{id}" => [],
    ];

    /**
     * @uses AufkleberWandtattoo::addSize
     */
    protected static $putRoutes = [
        "/sticker/sizes" => [AufkleberWandtattoo::class, "addSize"],
    ];

    /**
     * @uses StickerTagManager::removeTag
     * @uses AufkleberWandtattoo::deleteSize
     */
    protected static $deleteRoutes = [
        "/sticker/tags" => "StickerTagManager::removeTag",
        "/sticker/sizes/{id}" => [AufkleberWandtattoo::class, "deleteSize"],
    ];

    public function __construct()
    {
        parent::__construct();
    }
}
