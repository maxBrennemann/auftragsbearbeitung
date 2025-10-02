<?php

use Src\Classes\Link;

$link = Link::getPageLink("kunde") . "?id=" . $customer->getKundennummer();

?>

<div class="rounded-md m-3 bg-gray-100">
    <a class="bg-gray-300 hover:bg-gray-400 p-2 rounded-t-md block font-semibold" href="<?= $link ?>">
        <p><?= $customer->getFrontOfficeName() ?> →</p>
    </a>
    <div class="p-2">
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
    </div>
</div>