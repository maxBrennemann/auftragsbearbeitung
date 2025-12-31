<?php

namespace Src\Classes\Project;

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
     * @return array<int, array<string, string>>
     */
    public static function getTexts(): array
    {
        $query = "SELECT id, title, content FROM wiki_articles ORDER BY title ASC;";
        return DBAccess::selectQuery($query);
    }

    /**
     * @return array<void>
     */
    public static function get(): array
    {
        $query = "";
        return [];
    }

    public static function ajaxGetManualText(): void
    {
        $infoKey = Tools::get("key");
        $infoText = DBAccess::selectQuery("SELECT info FROM info_texte WHERE key = :infoKey;", [
            "infoKey" => $infoKey,
        ]);

        if ($infoText == null) {
            JSONResponseHandler::returnNotFound();
        }

        JSONResponseHandler::sendResponse([
            "info" => $infoText[0]["info"],
        ]);
    }
}
