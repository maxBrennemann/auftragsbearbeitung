<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class Wiki
{
    public static function add()
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        DBAccess::insertQuery("INSERT INTO wiki_articles (title, content) VALUES ('$title', '$content')");
    }
}
