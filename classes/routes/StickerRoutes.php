<?php

require_once("classes/routes/Routes.php");
require_once("classes/project/modules/sticker/StickerCollection.php");

class StickerRoutes extends Routes
{

    /**
     * @uses StickerCollection::getStickerStatus
     */
    protected static $getRoutes = [
        "/sticker/{id}/status" => "StickerCollection::getStickerStatus",
    ];

    /**
     * @uses StickerCollection::exportSticker
     */
    protected static $postRoutes = [
        "/sticker/{id}/export" => "StickerCollection::exportSticker",
    ];

    public function __construct()
    {
        parent::__construct();
    }
}
