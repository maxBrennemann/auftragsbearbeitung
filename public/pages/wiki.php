<?php

use Src\Classes\Project\Icon;
use Src\Classes\Project\Wiki;

$eintraege = Wiki::getTexts();

?>
<div class="grid grid-cols-1 lg:grid-cols-3">
    <section class="defCont lg:col-span-2">
        <div class="section-head">
            <h2 class="section-title">Firmenwiki</h2>
            <span class="section-meta"><?= count($eintraege) == 1 ? "1 Eintrag" : count($eintraege) . " Einträge" ?></span>
        </div>
        <?php if (empty($eintraege)): ?>
            <p class="text-sm text-text-muted">Es gibt noch keine Einträge.</p>
        <?php else: ?>
            <div class="flex flex-col gap-3">
                <?php foreach ($eintraege as $eintrag): ?>
                    <article class="sheet">
                        <h3 class="font-heading font-bold text-ink"><?= $eintrag["title"] ?></h3>
                        <p class="mt-1 text-sm whitespace-pre-line"><?= $eintrag["content"] ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="defCont">
        <div class="section-head">
            <h2 class="section-title">Neuer Eintrag</h2>
        </div>
        <div class="flex flex-col gap-3">
            <label class="field">
                <span class="field-label">Titel</span>
                <input type="text" id="newTitle" class="input-primary">
            </label>
            <label class="field">
                <span class="field-label">Inhalt</span>
                <textarea id="newContent" class="input-primary min-h-40"></textarea>
            </label>
            <button data-fun="addEntry" data-binding="true" class="btn-primary self-start"><?= Icon::get("plus") ?>Eintrag speichern</button>
        </div>
    </section>
</div>
