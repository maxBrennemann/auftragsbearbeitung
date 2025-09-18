<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
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
}
