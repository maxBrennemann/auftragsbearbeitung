<?php

use MaxBrennemann\PhpUtilities\DBAccess;

$eintraege = DBAccess::selectQuery("SELECT title, content FROM wiki_articles");

$content_id = 0;
if (isset($_GET["id"])) {
    $content_id = $_GET["id"];
}

?>
<div class="defCont overrideColorscheme">
    <span class="search_wrapper">
        <input class="search" type="text" placeholder="Suchen">
        <span id="lupeSpan"><span id="lupe">&#9906;</span></span>
    </span>
</div>
<div class="defCont">
    <span>Titel</span>
    <br>
    <input type="text" id="newTitle">
    <br>
    <span>Inhalt</span>
    <br>
    <textarea id="newContent">
    </textarea>
    <br>
    <button data-fun="addEntry" data-binding="true">Hinzufügen</button>
</div>
<?php foreach ($eintraege as $eintrag): ?>
    <div class="defCont">
        <h2><?= $eintrag["title"] ?></h2>
        <p><?= $eintrag["content"] ?></p>
    </div>
<?php endforeach; ?>