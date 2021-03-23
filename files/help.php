<?php

$text = "";
if (isset($_GET['article'])) {
    $article = (int) $_GET['article'];
    $text = DBAccess::selectQuery("SELECT info FROM help WHERE id = $article")[0]["info"];
}

echo $text;

?>