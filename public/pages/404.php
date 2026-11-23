<?php

use Src\Classes\Link;
use Src\Classes\Project\Icon;

?>
<div class="flex justify-center px-2.5 py-10">
    <section class="defCont w-full max-w-lg m-0">
        <div class="section-head">
            <h2 class="section-title">Seite nicht gefunden</h2>
            <span class="stamp stamp-muted">404</span>
        </div>
        <p>Diese Adresse gibt es nicht, oder die Seite wurde verschoben.</p>
        <div class="flex flex-wrap gap-2 mt-4">
            <a href="<?= Link::getPageLink("") ?>" class="btn-primary"><?= Icon::get("arrow-left") ?>Zur Startseite</a>
            <a href="<?= Link::getPageLink("functionalities") ?>" class="btn-secondary">Alle Funktionen</a>
        </div>
    </section>
</div>
