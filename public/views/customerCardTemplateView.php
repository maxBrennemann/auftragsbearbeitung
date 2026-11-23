<?php

use Src\Classes\Link;
use Src\Classes\Project\Icon;

/**
 * Kundenkarte (Kundenübersicht, Suche, Dialog „Anderem Kunden zuweisen“).
 * $targetLink = true: ganze Kopfzeile verlinkt zum Kunden; sonst ist die Karte auswählbar (orderManager.ts, aria-pressed).
 */
$link = Link::getPageLink("kunde") . "?id=" . $customer->getKundennummer();

$hasAddress = trim((string)$customer->getStrasse()) !== '' || trim((string)$customer->getOrt()) !== '' || (int)$customer->getPostleitzahl() !== 0;
$hasContact = $customer->getTelefonFestnetz() || $customer->getTelefonMobil() || $customer->getEmail() || $customer->getWebsite();

/* Icon als fertiges SVG übergeben (Icon::get mit festem Namen), damit generate-icons.mjs es findet */
$contactLine = function (string $iconSvg, string $content): string {
    return "<p class=\"flex items-center gap-1.5 min-w-0\"><span class=\"flex-none text-text-muted\">{$iconSvg}</span><span class=\"truncate\">{$content}</span></p>";
};

?>
<div class="customer-card sheet flex flex-col h-full p-0 transition-colors hover:border-line" data-customer-id="<?= $customer->getKundennummer() ?>">
    <div class="flex items-start justify-between gap-2 px-3 pt-3 pb-2 border-b border-line-soft">
        <?php if (isset($targetLink) && $targetLink) : ?>
            <a class="font-heading font-bold text-ink leading-snug line-clamp-2 hover:underline" href="<?= $link ?>" title="<?= $customer->getFrontOfficeName() ?>">
                <?= $customer->getFrontOfficeName() ?>
            </a>
        <?php else: ?>
            <p class="font-heading font-bold text-ink leading-snug line-clamp-2" title="<?= $customer->getFrontOfficeName() ?>"><?= $customer->getFrontOfficeName() ?></p>
            <a href="<?= $link ?>" class="btn-ghost btn-icon btn-sm flex-none" title="Kunde in neuem Tab öffnen" aria-label="Kunde in neuem Tab öffnen" target="_blank"><?= Icon::get("external-link", 14) ?></a>
        <?php endif; ?>
        <span class="kpi-label flex-none mt-0.5"><?= $customer->getKundennummer() ?></span>
    </div>
    <div class="px-3 py-2 flex-1 text-sm space-y-0.5">
        <?php if ($hasAddress) : ?>
            <p><?= $customer->getStrasse() ?> <?= $customer->getHausnummer() ?></p>
            <p class="mb-1.5"><?= $customer->getPostleitzahl() ?> <?= $customer->getOrt() ?></p>
        <?php endif; ?>
        <?php if ($customer->getTelefonFestnetz() != null) : ?>
            <?= $contactLine(Icon::get("phone", 14), $customer->getTelefonFestnetz()) ?>
        <?php endif; ?>
        <?php if ($customer->getTelefonMobil() != null) : ?>
            <?= $contactLine(Icon::get("smartphone", 14), $customer->getTelefonMobil()) ?>
        <?php endif; ?>
        <?php if ($customer->getEmail() != null) : ?>
            <?= $contactLine(Icon::get("mail", 14), "<a class=\"link-primary font-normal\" href=\"mailto:{$customer->getEmail()}\">{$customer->getEmail()}</a>") ?>
        <?php endif; ?>
        <?php if ($customer->getWebsite() != null) : ?>
            <?= $contactLine(Icon::get("globe", 14), "<a class=\"link-primary font-normal\" href=\"{$customer->getWebsite()}\" target=\"_blank\" rel=\"noopener\">{$customer->getWebsite()}</a>") ?>
        <?php endif; ?>
        <?php if (!$hasAddress && !$hasContact): ?>
            <p class="text-text-muted">Keine Kontaktdaten hinterlegt</p>
        <?php endif; ?>
    </div>
</div>
