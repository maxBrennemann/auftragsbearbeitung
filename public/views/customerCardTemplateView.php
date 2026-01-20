<?php

use Src\Classes\Link;
use Src\Classes\Project\Icon;

$link = Link::getPageLink("kunde") . "?id=" . $customer->getKundennummer();

$hasAddress = trim((string)$customer->getStrasse()) !== '' || trim((string)$customer->getOrt()) !== '' || (int)$customer->getPostleitzahl() !== 0;
$hasContact = $customer->getTelefonFestnetz() || $customer->getTelefonMobil() || $customer->getEmail() || $customer->getWebsite();
$hasInfo = $hasAddress || $hasContact;

?>

<div class="group rounded-xl bg-gray-50 ring-1 ring-gray-200 hover:ring-gray-300 hover:bg-white transition overflow-hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-400 flex flex-col h-full" data-customer-id="<?= $customer->getKundennummer() ?>">
    <?php if (isset($targetLink) && $targetLink) : ?>
        <a class="bg-gray-300 hover:bg-gray-400 p-2 rounded-t-md block font-semibold" href="<?= $link ?>">
            <p class="font-semibold leading-snug line-clamp-2 min-h-10" title="<?= $customer->getFrontOfficeName() ?>">
                <?= $customer->getFrontOfficeName() ?> <span aria-hidden="true"><?= Icon::getDefault("iconNewTab") ?></span>
            </p>
        </a>
    <?php else: ?>
        <div class="bg-gray-300 hover:bg-gray-400 p-2 rounded-t-md block font-semibold">
            <p class="font-semibold leading-snug line-clamp-2 min-h-10" title="<?= $customer->getFrontOfficeName() ?>">
                <?= $customer->getFrontOfficeName() ?> <a href="<?= $link ?>"><?= Icon::getDefault("iconNewTab") ?></a>
            </p>
        </div>
    <?php endif; ?>
    <div class="p-2 flex-1">
        <p class="mt-2"><?= $customer->getStrasse() ?> <?= $customer->getHausnummer() ?></p>
        <p><?= $customer->getPostleitzahl() ?> <?= $customer->getOrt() ?></p>

        <?php if ($customer->getTelefonFestnetz() != null) : ?>
            <p>☎ <?= $customer->getTelefonFestnetz() ?></p>
        <?php endif; ?>

        <?php if ($customer->getTelefonMobil() != null) : ?>
            <p>✆ <?= $customer->getTelefonMobil() ?></p>
        <?php endif; ?>

        <?php if ($customer->getEmail() != null) : ?>
            <p>@ <a href="mailto:<?= $customer->getEmail() ?>"><?= $customer->getEmail() ?></a></p>
        <?php endif; ?>

        <?php if ($customer->getWebsite() != null) : ?>
            <p>🔗 <a href="<?= $customer->getWebsite() ?>"><?= $customer->getWebsite() ?></a></p>
        <?php endif; ?>

        <?php if (!$hasInfo): ?>
            <p class="mt-2 text-gray-400 italic">Keine Kontaktdaten hinterlegt</p>
        <?php endif; ?>
    </div>
</div>