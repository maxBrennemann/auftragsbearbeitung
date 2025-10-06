<?php

namespace Src\Classes\Routes;

use MaxBrennemann\PhpUtilities\Router\Routes;

class StickerRoutes extends Routes
{
    /**
     * @uses \Src\Classes\Sticker\StickerCollection::getStickerStatus()
     * @uses \Src\Classes\Sticker\StickerCollection::getStickerSizes()
     * @uses \Src\Classes\Sticker\StickerCollection::getPriceScheme()
     *
     * @uses \Src\Classes\Sticker\StickerCollection::empty()
     * @uses \Src\Classes\Sticker\StickerCollection::empty()
     * @uses \Src\Classes\Sticker\StickerCollection::empty()
     * @uses \Src\Classes\Sticker\StickerTagManager::countTagOccurences()
     * @uses \Src\Classes\Sticker\Tags\TagController::getTagSuggestions()
     *
     * @uses \Src\Classes\Sticker\StickerCollection::getStickerOverview()
     * @uses \Src\Classes\Sticker\StickerCollection::getStickerStates()
     *
     * @uses \Src\Classes\Sticker\TextModification::iterateText()
     * @uses \Src\Classes\Sticker\TextModification::getTextGenerationTemplate()
     */
    protected static $getRoutes = [
        "/sticker/{id}/status" => [\Src\Classes\Sticker\StickerCollection::class, "getStickerStatus"],
        "/sticker/{id}/sizes" => [\Src\Classes\Sticker\StickerCollection::class, "getStickerSizes"],
        "/sticker/{id}/priceScheme" => [\Src\Classes\Sticker\StickerCollection::class, "getPriceScheme"],

        "/sticker/tags" => [\Src\Classes\Sticker\StickerCollection::class, "empty"],
        "/sticker/tags/crawl" => [\Src\Classes\Sticker\StickerCollection::class, "empty"],
        "/sticker/tags/groups" => [\Src\Classes\Sticker\StickerCollection::class, "empty"],
        "/sticker/tags/overview" => [\Src\Classes\Sticker\StickerTagManager::class, "getTagOverview"],
        "/sticker/{id}/tags-template" => [\Src\Classes\Sticker\Tags\TagController::class, "getTagSuggestions"],

        "/sticker/overview" => [\Src\Classes\Sticker\StickerCollection::class, "getStickerOverview"],
        "/sticker/states" => [\Src\Classes\Sticker\StickerCollection::class, "getStickerStates"],

        "/sticker/texts/{id}/{type}/{form}" => [\Src\Classes\Sticker\TextModification::class, "iterateText"],
        "/sticker/texts/{id}/get-template" => [\Src\Classes\Sticker\TextModification::class, "getTextGenerationTemplate"],
    ];

    /**
     * @uses \Src\Classes\Sticker\StickerCollection::addStickerCron()
     * @uses \Src\Classes\Sticker\Textil::toggleTextile()
     * @uses \Src\Classes\Sticker\Textil::setPrice()
     * @uses \Src\Classes\Sticker\StickerCollection::setPriceScheme()
     * @uses \Src\Classes\Sticker\StickerCollection::addFiles()
     *
     * @uses \Src\Classes\Sticker\StickerCollection::addSticker()
     * @uses \Src\Classes\Sticker\Tags\TagController::addTagGroup()
     * @uses \Src\Classes\Sticker\StickerTagManager::addTag()
     * @uses \Src\Classes\Sticker\StickerTagManager::crawlAllTags()
     *
     * @uses \Src\Classes\Sticker\AufkleberWandtattoo::updateSizes()
     * @uses \Src\Classes\Sticker\Textil::makeColorizable()
     *
     * @uses \Src\Classes\Sticker\ProductCrawler::crawlAll()
     *
     * @uses \Src\Classes\Sticker\Exports\ExportFacebook::createExport()
     *
     * @uses \Src\Classes\Sticker\TextModification::newText()
     */
    protected static $postRoutes = [
        "/sticker/{id}/export-scheduled" => [\Src\Classes\Sticker\StickerCollection::class, "addStickerCron"],
        "/sticker/{id}/textile/{idTextile}/toggle" => [\Src\Classes\Sticker\Textil::class, "toggleTextile"],
        "/sticker/{id}/textile/{idTextile}/price" => [\Src\Classes\Sticker\Textil::class, "setPrice"],
        "/sticker/{id}/priceScheme" => [\Src\Classes\Sticker\StickerCollection::class, "setPriceScheme"],
        "/sticker/{id}/{type}/add-files" => [\Src\Classes\Sticker\StickerCollection::class, "addFiles"],

        "/sticker" => [\Src\Classes\Sticker\StickerCollection::class, "addSticker"],
        "/sticker/tags" => [\Src\Classes\Sticker\StickerCollection::class, "addTag"],
        "/sticker/tags/groups" => [\Src\Classes\Sticker\Tags\TagController::class, "addTagGroup"],
        "/sticker/tags/crawl" => [\Src\Classes\Sticker\StickerTagManager::class, "crawlAllTags"],

        "/sticker/{id}/sizes" => [\Src\Classes\Sticker\AufkleberWandtattoo::class, "updateSizes"],
        "/sticker/{id}/svg-colorizable" => [\Src\Classes\Sticker\Textil::class, "makeColorizable"],

        "/sticker/crawl/all" => [\Src\Classes\Sticker\ProductCrawler::class, "crawlAll"],

        "/sticker/export/facebook" => [\Src\Classes\Sticker\Exports\ExportFacebook::class, "createExport"],

        "/sticker/texts/{id}/{type}/{form}" => [\Src\Classes\Sticker\TextModification::class, "newText"],
    ];

    /**
     * @uses \Src\Classes\Sticker\AufkleberWandtattoo::addSize()
     * @uses \Src\Classes\Sticker\StickerCollection::setCreationDate()
     * @uses \Src\Classes\Sticker\StickerCollection::setTitle()
     * @uses \Src\Classes\Sticker\StickerCollection::writeDirectory()
     * @uses \Src\Classes\Sticker\StickerCollection::writeAdditonalInfo()
     * @uses \Src\Classes\Sticker\StickerCollection::setExportStatus()
     * @uses \Src\Classes\Sticker\StickerCollection::setAltTitle()
     * @uses \Src\Classes\Sticker\StickerCollection::toggleStatus()
     * @uses \Src\Classes\Sticker\Sticker::setDescription()
     * 
     * @uses \Src\Classes\Sticker\StickerImageManager::updateDescription()
     */
    protected static $putRoutes = [
        "/sticker/sizes" => [\Src\Classes\Sticker\AufkleberWandtattoo::class, "addSize"],
        "/sticker/{id}/creation-date" => [\Src\Classes\Sticker\StickerCollection::class, "setCreationDate"],
        "/sticker/{id}/title" => [\Src\Classes\Sticker\StickerCollection::class, "setTitle"],
        "/sticker/{id}/directory" => [\Src\Classes\Sticker\StickerCollection::class, "writeDirectory"],
        "/sticker/{id}/additional-info" => [\Src\Classes\Sticker\StickerCollection::class, "writeAdditonalInfo"],
        "/sticker/{id}/export-status" => [\Src\Classes\Sticker\StickerCollection::class, "setExportStatus"],
        "/sticker/{id}/{type}/alt-title" => [\Src\Classes\Sticker\StickerCollection::class, "setAltTitle"],
        "/sticker/{id}/{type}/toggle" => [\Src\Classes\Sticker\StickerCollection::class, "toggleStatus"],
        "/sticker/{id}/{textType}/description" => [\Src\Classes\Sticker\Sticker::class, "setDescription"],

        "/sticker/image/{imageId}" => [\Src\Classes\Sticker\StickerImageManager::class, "updateDescription"],
    ];

    /**
     * @uses \Src\Classes\Sticker\StickerTagManager::removeTag()
     * @uses \Src\Classes\Sticker\AufkleberWandtattoodtattoo::deleteSize()
     */
    protected static $deleteRoutes = [
        "/sticker/tags" => [\Src\Classes\Sticker\StickerTagManager::class, "removeTag"],
        "/sticker/sizes/{id}" => [\Src\Classes\Sticker\AufkleberWandtattoo::class, "deleteSize"],
    ];
}
