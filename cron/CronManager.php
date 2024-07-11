<?php

require_once "classes/project/modules/sticker/Textil.php";
require_once "classes/project/modules/sticker/StickerImage.php";

class CronManager {

    public static function schedule() {
        Textil::handle();
        StickerImage::handle();
    }

}
