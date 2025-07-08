<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class StickerRoutes extends Routes
{
    /**
     * @uses \Classes\Sticker\StickerCollection::getStickerStatus()
     * @uses \Classes\Sticker\StickerCollection::getStickerSizes()
     * @uses \Classes\Sticker\StickerCollection::getPriceScheme()
     *
     * @uses
     * @uses
     * @uses \Classes\Sticker\StickerTagManager::getTagSuggestions()
     * @uses
     * @uses \Classes\Sticker\StickerTagManager::countTagOccurences()
     * @uses \Classes\Sticker\StickerTagManager::getTagsHTML()
     *
     * @uses \Classes\Sticker\StickerCollection::getStickerOverview()
     * @uses \Classes\Sticker\StickerCollection::getStickerStates()
     *
     * @uses \Classes\Sticker\TextModification::iterateText()
     * @uses \Classes\Sticker\TextModification::getTextGenerationTemplate()
     */
    protected static $getRoutes = [
        "/sticker/{id}/status" => [\Classes\Sticker\StickerCollection::class, "getStickerStatus"],
        "/sticker/{id}/sizes" => [\Classes\Sticker\StickerCollection::class, "getStickerSizes"],
        "/sticker/{id}/priceScheme" => [\Classes\Sticker\StickerCollection::class, "getPriceScheme"],

        "/sticker/tags" => [],
        "/sticker/tags/crawl" => [],
        "/sticker/tags/suggestions" => [\Classes\Sticker\StickerCollection::class, "getTagSuggestions"],
        "/sticker/tags/groups" => [],
        "/sticker/tags/overview" => [\Classes\Sticker\StickerTagManager::class, "getTagOverview"],
        "/sticker/{id}/tags-template" => [\Classes\Sticker\StickerTagManager::class, "getTagsHTML"],

        "/sticker/overview" => [\Classes\Sticker\StickerCollection::class, "getStickerOverview"],
        "/sticker/states" => [\Classes\Sticker\StickerCollection::class, "getStickerStates"],

        "/sticker/texts/{id}/{type}/{form}" => [\Classes\Sticker\TextModification::class, "iterateText"],
        "/sticker/texts/{id}/get-template" => [\Classes\Sticker\TextModification::class, "getTextGenerationTemplate"],
    ];

    /**
     * @uses \Classes\Sticker\StickerCollection::addStickerCron()
     * @uses \Classes\Sticker\Textil::toggleTextile()
     * @uses \Classes\Sticker\Textil::setPrice()
     * @uses \Classes\Sticker\StickerCollection::setPriceScheme()
     * @uses \Classes\Sticker\StickerCollection::addFiles()
     *
     * @uses \Classes\Sticker\StickerCollection::addSticker()
     *
     * @uses \Classes\Sticker\StickerTagManager::addTag()
     * @uses \Classes\Sticker\StickerTagManager::crawlAllTags()
     *
     * @uses \Classes\Sticker\AufkleberWandtattoo::updateSizes()
     *
     * @uses \Classes\Sticker\ProductCrawler::crawlAll()
     *
     * @uses \Classes\Sticker\Exports\ExportFacebook::createExport()
     *
     * @uses \Classes\Sticker\TextModification::newText()
     */
    protected static $postRoutes = [
        "/sticker/{id}/export-scheduled" => [\Classes\Sticker\StickerCollection::class, "addStickerCron"],
        "/sticker/{id}/textile/{idTextile}/toggle" => [\Classes\Sticker\Textil::class, "toggleTextile"],
        "/sticker/{id}/textile/{idTextile}/price" => [\Classes\Sticker\Textil::class, "setPrice"],
        "/sticker/{id}/priceScheme" => [\Classes\Sticker\StickerCollection::class, "setPriceScheme"],
        "/sticker/{id}/{type}/add-files" => [\Classes\Sticker\StickerCollection::class, "addFiles"],

        "/sticker" => [\Classes\Sticker\StickerCollection::class, "addSticker"],
        "/sticker/tags" => [\Classes\Sticker\StickerCollection::class, "addTag"],
        "/sticker/tags/groups" => [],
        "/sticker/tags/crawl" => [\Classes\Sticker\StickerTagManager::class, "crawlAllTags"],

        "/sticker/{id}/sizes" => [\Classes\Sticker\AufkleberWandtattoo::class, "updateSizes"],

        "/sticker/crawl/all" => [\Classes\Sticker\ProductCrawler::class, "crawlAll"],

        "/sticker/export/facebook" => [\Classes\Sticker\Exports\ExportFacebook::class, "createExport"],

        "/sticker/texts/{id}/{type}/{form}" => [\Classes\Sticker\TextModification::class, "newText"],
    ];

    /**
     * @uses \Classes\Sticker\AufkleberWandtattoo::addSize()
     * @uses \Classes\Sticker\StickerCollection::setCreationDate()
     * @uses \Classes\Sticker\StickerCollection::setTitle()
     * @uses \Classes\Sticker\StickerCollection::writeDirectory()
     * @uses \Classes\Sticker\StickerCollection::writeAdditonalInfo()
     * @uses \Classes\Sticker\StickerCollection::setExportStatus()
     * @uses \Classes\Sticker\StickerCollection::setAltTitle()
     * @uses \Classes\Sticker\StickerCollection::toggleStatus()
     */
    protected static $putRoutes = [
        "/sticker/sizes" => [\Classes\Sticker\AufkleberWandtattoo::class, "addSize"],
        "/sticker/{id}/creation-date" => [\Classes\Sticker\StickerCollection::class, "setCreationDate"],
        "/sticker/{id}/title" => [\Classes\Sticker\StickerCollection::class, "setTitle"],
        "/sticker/{id}/directory" => [\Classes\Sticker\StickerCollection::class, "writeDirectory"],
        "/sticker/{id}/additional-info" => [\Classes\Sticker\StickerCollection::class, "writeAdditonalInfo"],
        "/sticker/{id}/export-status" => [\Classes\Sticker\StickerCollection::class, "setExportStatus"],
        "/sticker/{id}/{type}/alt-title" => [\Classes\Sticker\StickerCollection::class, "setAltTitle"],
        "/sticker/{id}/{type}/toggle" => [\Classes\Sticker\StickerCollection::class, "toggleStatus"],
    ];

    /**
     * @uses \Classes\Sticker\StickerTagManager::removeTag()
     * @uses \Classes\Sticker\AufkleberWandtattoodtattoo::deleteSize()
     */
    protected static $deleteRoutes = [
        "/sticker/tags" => [\Classes\Sticker\StickerTagManager::class, "removeTag"],
        "/sticker/sizes/{id}" => [\Classes\Sticker\AufkleberWandtattoo::class, "deleteSize"],
    ];
}
