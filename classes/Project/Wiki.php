<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Wiki
{
    public static function add(): void
    {
        $title = Tools::get("title");
        $content = Tools::get("content");
        DBAccess::insertQuery("INSERT INTO wiki_articles (title, content) VALUES (:title, :content)", [
            "title" => $title,
            "content" => $content,
        ]);
    }

    /**
     * @return array<void>
     */
    public static function get(): array
    {
        $query = "";
        return [];
    }

    public static function ajaxGetText(): void
    {
        $infoId = (int) Tools::get("id");
        $infoText = DBAccess::selectQuery("SELECT info FROM info_texte WHERE id = :infoId;", [
            "infoId" => $infoId,
        ]);

        if ($infoText == null) {
            JSONResponseHandler::returnNotFound();
        }

        JSONResponseHandler::sendResponse([
            "info" => $infoText[0]["info"],
        ]);
    }
}
