<?php

namespace Src\Classes\Controller;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class ManualController
{
    public static function get(): void
    {
        $pageName = Tools::get("pageName");
        $intent = Tools::get("intent");
        $data = DBAccess::selectQuery("SELECT info FROM `manual` WHERE `page` = :page AND intent = :intent", [
            "page" => $pageName,
            "intent" => $intent,
        ]);

        JSONResponseHandler::sendResponse($data);
    }
}
