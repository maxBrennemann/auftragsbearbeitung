<?php

use Src\Classes\Project\Icon;

/**
 * Startseite: Links mit Eingabefeld werden zu „Direkt öffnen“-Feldern (Enter öffnet, siehe home.ts),
 * alle anderen zu Schnellzugriff-Kacheln.
 */
$lookups = array_filter($customLinks, fn($link) => $link["input"] !== false);
$tiles = array_filter($customLinks, fn($link) => $link["input"] === false);

?>
<?php if (!empty($lookups)): ?>
    <span class="group-label mb-2">Direkt öffnen</span>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
        <?php foreach ($lookups as $link): ?>
            <div class="sheet">
                <label class="field">
                    <span class="field-label">
                        <?= $link["icon"] ? Icon::get($link["icon"], 14) : "" ?>
                        <?= $link["name"] ?>
                    </span>
                    <span class="relative">
                        <input id="<?= $link["input"]["id"] ?>" type="<?= $link["input"]["type"] ?>"
                            <?= isset($link["input"]["min"]) ? "min=\"{$link["input"]["min"]}\"" : "" ?>
                            placeholder="<?= $link["input"]["placeholder"] ?? "" ?>"
                            class="input-primary w-full pr-10" autocomplete="off" enterkeyhint="go">
                        <kbd class="absolute right-2 top-1/2 -translate-y-1/2 text-[0.6875rem] font-semibold text-text-muted border border-line-soft rounded px-1 bg-[#f6f7f8]" aria-hidden="true">↵</kbd>
                    </span>
                </label>
                <?php if (isset($link["linkId"])): /* nur wo es eine echte Liste gibt; home.ts nutzt den Link bei leerer Eingabe */ ?>
                    <a href="<?= $link["url"] ?>" class="link-primary text-xs mt-1.5 inline-block" id="<?= $link["linkId"] ?>" data-url="<?= $link["url"] ?>">Alle anzeigen</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<span class="group-label mb-2">Seiten und Aktionen</span>
<ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
    <?php foreach ($tiles as $link): ?>
        <li class="m-0">
            <a href="<?= $link["url"] ?>" class="sheet flex items-center gap-2.5 h-full text-text no-underline transition-colors hover:border-ink hover:text-ink">
                <?php if ($link["icon"]): ?>
                    <span class="flex-none grid place-items-center w-8 h-8 rounded-control bg-ink-soft text-ink"><?= Icon::get($link["icon"]) ?></span>
                <?php endif; ?>
                <span class="font-semibold text-sm"><?= $link["name"] ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
