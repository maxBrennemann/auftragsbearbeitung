<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class StickerRoutes extends Routes
{

    /**
     * @uses Classes\Project\Modules\Sticker\StickerCollection::getStickerStatus()
     * @uses Classes\Project\Modules\Sticker\ChatGPTConnection::iterateText()
     * @uses Classes\Project\Modules\Sticker\StickerCollection::getStickerSizes()
     * @uses Classes\Project\Modules\Sticker\StickerCollection::getPriceScheme()
     * 
     * @uses Classes\Project\Modules\Sticker\StickerTagManager::getTagSuggestions()
     * 
     * @uses Classes\Project\Modules\Sticker\StickerCollection::getStickerOverview()
     * @uses Classes\Project\Modules\Sticker\StickerCollection::getStickerStates()
     */
    protected static $getRoutes = [
        "/sticker/{id}/status" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getStickerStatus"],
        "/sticker/{id}/texts/{type}/{form}" => [\Classes\Project\Modules\Sticker\ChatGPTConnection::class, "iterateText"],
        "/sticker/{id}/sizes" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getStickerSizes"],
        "/sticker/{id}/priceScheme" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getPriceScheme"],

        "/sticker/tags" => "",
        "/sticker/tags/crawl" => "",
        "/sticker/tags/suggestions" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getTagSuggestions"],
        "/sticker/tags/groups" => "",

        "/sticker/overview" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getStickerOverview"],
        "/sticker/states" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getStickerStates"],
    ];

    /**
     * @uses Classes\Project\Modules\Sticker\StickerCollection::exportSticker()
     * @uses Classes\Project\Modules\Sticker\Textil::toggleTextile()
     * @uses Classes\Project\Modules\Sticker\Textil::setPrice()
     * @uses Classes\Project\Modules\Sticker\StickerCollection::setPriceScheme()
     * 
     * @uses Classes\Project\Modules\Sticker\StickerCollection::addSticker()
     * 
     * @uses Classes\Project\Modules\Sticker\StickerTagManager::addTag()
     * 
     * @uses 
     */
    protected static $postRoutes = [
        "/sticker/{id}/export" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "exportSticker"],
        "/sticker/{id}/textile/{idTextile}/toggle" => [\Classes\Project\Modules\Sticker\Textil::class, "toggleTextile"],
        "/sticker/{id}/textile/{idTextile}/price" => [\Classes\Project\Modules\Sticker\Textil::class, "setPrice"],
        "/sticker/{id}/priceScheme" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "setPriceScheme"],

        "/sticker/{id}/texts/{type}/{form}" => [\Classes\Project\Modules\Sticker\ChatGPTConnection::class, "newText"],

        "/sticker" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "addSticker"],

        "/sticker/tags" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "addTag"],
        "/sticker/tags/groups" => [],

        "/sticker/{id}/sizes" => [\Classes\Project\Modules\Sticker\AufkleberWandtattoo::class, "updateSizes"],
    ];

    /**
     * @uses Classes\Project\Modules\Sticker\AufkleberWandtattoo::addSize()
     */
    protected static $putRoutes = [
        "/sticker/sizes" => [\Classes\Project\Modules\Sticker\AufkleberWandtattoo::class, "addSize"],
    ];

    /**
     * @uses Classes\Project\Modules\Sticker\StickerTagManager::removeTag()
     * @uses Classes\Project\Modules\Sticker\AufkleberWandtattoodtattoo::deleteSize()
     */
    protected static $deleteRoutes = [
        "/sticker/tags" => [\Classes\Project\Modules\Sticker\StickerTagManager::class, "removeTag"],
        "/sticker/sizes/{id}" => [\Classes\Project\Modules\Sticker\AufkleberWandtattoo::class, "deleteSize"],
    ];
}
