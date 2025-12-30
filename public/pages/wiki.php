<?php

use Src\Classes\Project\Wiki;

$eintraege = Wiki::getTexts();

?>
<div class="defCont">
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