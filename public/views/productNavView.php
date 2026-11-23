<?php

use Src\Classes\Link;
use Src\Classes\Project\Icon;

/**
 * Navigation im Produktbereich. $current: "produkt" | "neues-produkt" | "attributes"
 */
/* Icons mit dem Schlüssel icon angeben, damit generate-icons.mjs sie findet */
$items = [
    "produkt" => ["label" => "Produkte", "icon" => "tag"],
    "neues-produkt" => ["label" => "Neues Produkt", "icon" => "plus"],
    "attributes" => ["label" => "Attribute", "icon" => "list"],
];

?>
<nav class="mx-2.5 mt-2 mb-3 flex flex-wrap gap-1" aria-label="Produktbereich">
    <?php foreach ($items as $page => ["label" => $label, "icon" => $icon]): ?>
        <?php $isCurrent = ($current ?? "") === $page; ?>
        <a href="<?= Link::getPageLink($page) ?>"
            class="<?= $isCurrent ? "btn-secondary bg-ink-soft border-ink" : "btn-ghost" ?> btn-sm"
            <?= $isCurrent ? 'aria-current="page"' : "" ?>>
            <?= Icon::get($icon, 14) ?><?= $label ?>
        </a>
    <?php endforeach; ?>
</nav>
