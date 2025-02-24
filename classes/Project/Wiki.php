<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class Wiki
{
    public static function add(): void
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        DBAccess::insertQuery("INSERT INTO wiki_articles (title, content) VALUES ('$title', '$content')");
    }

    public static function get(): array
    {
        $query = "";
        return [];
    }
}
