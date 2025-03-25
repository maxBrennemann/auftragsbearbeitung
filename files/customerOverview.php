<?php

use MaxBrennemann\PhpUtilities\Tools;

use Classes\Link;
use Classes\Project\Kunde;
use Classes\Project\SearchController;

$query = Tools::get("query");
$customers = [];

if ($query !== null) {
    $results = SearchController::initSearch("customer", $query, 10);
    foreach ($results as $resultType) {
        foreach ($resultType as $result) {
            $customers[] = new Kunde($result["row"]["Kundennummer"]);
        }
    }
} else {
    $customers = Kunde::getAllCustomerOverviews();
}

?>

<div class="search bg-gray-100 p-4 rounded-md inline-flex w-full items-center">
    <p class="mr-3">Suche:</p>
    <div class="inline-flex border rounded-xl overflow-hidden">
        <input class="border-none p-2 rounded-none shadow-none box-content outline-none placeholder:text-[#374151] w-16 md:w-48" value="<?= Tools::get("query") ?>" id="search" data-url="<?= Link::getPageLink('kunde') ?>">
        <span class="inline-flex items-center mr-2">
            <svg class="inline" style="width:15px;height:15px" viewBox="0 0 24 24">
                <path fill="#374151" d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"></path>
            </svg>
        </span>
    </div>
</div>
<div class="grid grid-cols-3 xl:grid-cols-4 w-full">
    <?php foreach ($customers as $customer) : ?>
        <?php $link = Link::getPageLink("kunde") . "?id=" . $customer->getKundennummer(); ?>
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
    <?php endforeach; ?>
</div>