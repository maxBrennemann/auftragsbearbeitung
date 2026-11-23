<?php
/**
 * Suchfeld. Aussehen: .searchContainer in input.css.
 * Struktur beibehalten: global.ts nutzt das erste ".searchContainer input" im Dokument (Kopfzeile, ⌘K),
 * customerOverview.ts erwartet die Lupe als nextElementSibling des Feldes.
 */
?>
<div class="searchContainer">
    <input
        id="<?= isset($searchId) ? $searchId : "" ?>"
        value="<?= isset($searchValue) ? $searchValue : "" ?>"
        placeholder="<?= isset($placeHolder) ? $placeHolder : "" ?>"
        title="<?= isset($placeHolder) ? $placeHolder : "Suchen" ?>"
        aria-label="<?= isset($placeHolder) ? $placeHolder : "Suchen" ?>"
        name="searchInput">
    <span aria-hidden="true">
        <?= Src\Classes\Project\Icon::get("search", 16) ?>
    </span>
</div>
