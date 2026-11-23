<?php

use Src\Classes\Link;
use Src\Classes\Project\Icon;

?>
<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Zahlungen und Finanzen</h2>
    </div>
    <div class="note note-info">
        <p class="note-head"><?= Icon::get("info") ?>Im Aufbau</p>
        <p>Dieser Bereich ist noch nicht umgesetzt. Geplant sind Datenimport, Tabellenübersicht, Auswertung und Kategorien.</p>
    </div>
    <span class="group-label mt-5 mb-2">Schon verfügbar</span>
    <div class="flex flex-wrap gap-2">
        <a class="btn-secondary" href="<?= Link::getPageLink("offene-rechnungen") ?>"><?= Icon::get("receipt-euro") ?>Offene Rechnungen</a>
        <a class="btn-secondary" href="<?= Link::getPageLink("diagramme") ?>"><?= Icon::get("chart-line") ?>Auswertungen</a>
    </div>
</section>
