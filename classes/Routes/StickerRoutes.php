<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class StickerRoutes extends Routes
{

    /**
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::getStickerStatus()
     * @uses \Classes\Project\Modules\Sticker\ChatGPTConnection::iterateText()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::getStickerSizes()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::getPriceScheme()
     * 
     * @uses
     * @uses
     * @uses \Classes\Project\Modules\Sticker\StickerTagManager::getTagSuggestions()
     * @uses
     * @uses \Classes\Project\Modules\Sticker\StickerTagManager::countTagOccurences()
     * @uses \Classes\Project\Modules\Sticker\StickerTagManager::getTagsHTML()
     * 
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::getStickerOverview()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::getStickerStates()
     */
    protected static $getRoutes = [
        "/sticker/{id}/status" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getStickerStatus"],
        "/sticker/{id}/texts/{type}/{form}" => [\Classes\Project\Modules\Sticker\ChatGPTConnection::class, "iterateText"],
        "/sticker/{id}/sizes" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getStickerSizes"],
        "/sticker/{id}/priceScheme" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getPriceScheme"],

        "/sticker/tags" => [],
        "/sticker/tags/crawl" => [],
        "/sticker/tags/suggestions" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getTagSuggestions"],
        "/sticker/tags/groups" => [],
        "/sticker/tags/overview" => [\Classes\Project\Modules\Sticker\StickerTagManager::class, "getTagOverview"],
        "/sticker/{id}/tags-template" => [\Classes\Project\Modules\Sticker\StickerTagManager::class, "getTagsHTML"],

        "/sticker/overview" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getStickerOverview"],
        "/sticker/states" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "getStickerStates"],
    ];

    /**
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::exportSticker()
     * @uses \Classes\Project\Modules\Sticker\Textil::toggleTextile()
     * @uses \Classes\Project\Modules\Sticker\Textil::setPrice()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::setPriceScheme()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::addFiles()
     * 
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::addSticker()
     * 
     * @uses \Classes\Project\Modules\Sticker\StickerTagManager::addTag()
     * @uses \Classes\Project\Modules\Sticker\StickerTagManager::crawlAllTags()
     * 
     * @uses \Classes\Project\Modules\Sticker\AufkleberWandtattoo::updateSizes()
     * 
     * @uses \Classes\Project\Modules\Sticker\ProductCrawler::crawlAll()
     * 
     * @uses \Classes\Project\Modules\Sticker\Exports\ExportFacebook::createExport()
     */
    protected static $postRoutes = [
        "/sticker/{id}/export" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "exportSticker"],
        "/sticker/{id}/textile/{idTextile}/toggle" => [\Classes\Project\Modules\Sticker\Textil::class, "toggleTextile"],
        "/sticker/{id}/textile/{idTextile}/price" => [\Classes\Project\Modules\Sticker\Textil::class, "setPrice"],
        "/sticker/{id}/priceScheme" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "setPriceScheme"],
        "/sticker/{id}/{type}/add-files" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "addFiles"],

        "/sticker/{id}/texts/{type}/{form}" => [\Classes\Project\Modules\Sticker\ChatGPTConnection::class, "newText"],

        "/sticker" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "addSticker"],
        "/sticker/tags" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "addTag"],
        "/sticker/tags/groups" => [],
        "/sticker/tags/crawl" => [\Classes\Project\Modules\Sticker\StickerTagManager::class, "crawlAllTags"],

        "/sticker/{id}/sizes" => [\Classes\Project\Modules\Sticker\AufkleberWandtattoo::class, "updateSizes"],

        "/sticker/crawl/all" => [\Classes\Project\Modules\Sticker\ProductCrawler::class, "crawlAll"],

        "/sticker/export/facebook" => [\Classes\Project\Modules\Sticker\Exports\ExportFacebook::class, "createExport"],
    ];

    /**
     * @uses \Classes\Project\Modules\Sticker\AufkleberWandtattoo::addSize()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::setCreationDate()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::setTitle()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::setRevised()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::writeDirectory()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::writeAdditonalInfo()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::setExportStatus()
     * @uses \Classes\Project\Modules\Sticker\StickerCollection::setAltTitle()
     */
    protected static $putRoutes = [
        "/sticker/sizes" => [\Classes\Project\Modules\Sticker\AufkleberWandtattoo::class, "addSize"],
        "/sticker/{id}/creation-date" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "setCreationDate"],
        "/sticker/{id}/title" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "setTitle"],
        "/sticker/{id}/revised" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "setRevised"],
        "/sticker/{id}/directory" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "writeDirectory"],
        "/sticker/{id}/additional-info" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "writeAdditonalInfo"],
        "/sticker/{id}/export-status" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "setExportStatus"],
        "/sticker/{id}/{type}/alt-title" => [\Classes\Project\Modules\Sticker\StickerCollection::class, "setAltTitle"],
    ];

    /**
     * @uses \Classes\Project\Modules\Sticker\StickerTagManager::removeTag()
     * @uses \Classes\Project\Modules\Sticker\AufkleberWandtattoodtattoo::deleteSize()
     */
    protected static $deleteRoutes = [
        "/sticker/tags" => [\Classes\Project\Modules\Sticker\StickerTagManager::class, "removeTag"],
        "/sticker/sizes/{id}" => [\Classes\Project\Modules\Sticker\AufkleberWandtattoo::class, "deleteSize"],
    ];
}
