<?php

use MaxBrennemann\PhpUtilities\DBAccess;

$eintraege = DBAccess::selectQuery("SELECT title, content FROM wiki_articles");

$content_id = 0;
if (isset($_GET["id"])) {
    $content_id = $_GET["id"];
}

?>
<div class="defCont">
    <span class="search_wrapper">
        <input class="search" type="text" placeholder="Suchen">
        <span id="lupeSpan"><span>&#9906;</span></span>
    </span>
    <?= \Src\Classes\Controller\TemplateController::getTemplate("search"); ?>
</div>
<div class="defCont">
    <p>Titel</p>
    <input type="text" id="newTitle" class="input-primary">
    <p>Inhalt</p>
    <textarea id="newContent" class="input-primary"></textarea>
    <div class="mt-2">
        <button data-fun="addEntry" data-binding="true" class="btn-primary">Hinzufügen</button>
    </div>
</div>
<?php foreach ($eintraege as $eintrag): ?>
    <div class="defCont">
        <h2><?= $eintrag["title"] ?></h2>
        <p><?= $eintrag["content"] ?></p>
    </div>
<?php endforeach; ?>