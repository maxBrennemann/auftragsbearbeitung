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

<div class="search">
    <p>Suche: <input value="<?=Tools::get("query")?>" id="search" data-url="<?=Link::getPageLink('kunde')?>">
    <span id="lupeSpan"><span id="lupe">&#9906;</span></span></p>
</div>
<div class="grid grid-cols-4">
    <?php foreach ($customers as $customer) :?>
        <?php $link = Link::getPageLink("kunde") . "?id=" . $customer->getKundennummer(); ?>
        <div class="shortSummary">
            <a class="shortSummaryHeader" href="<?=$link?>">
                <p><?=$customer->getAlternativeName()?></p>
            </a>
            <p><?=$customer->getStrasse()?> <?=$customer->getHausnummer()?></p>
            <p><?=$customer->getPostleitzahl()?> <?=$customer->getOrt()?></p>

            <?php if ($customer->getTelefonFestnetz() != null) :?>
                <p>☎ <?=$customer->getTelefonFestnetz()?></p>
            <?php endif; ?>

            <?php if ($customer->getTelefonMobil() != null) :?>
                <p>✆ <?=$customer->getTelefonMobil()?></p>
            <?php endif; ?>

            <?php if ($customer->getEmail() != null) :?>
                <p>@ <a href="mailto:<?=$customer->getEmail()?>"><?=$customer->getEmail()?></a></p>
            <?php endif; ?>

            <?php if ($customer->getWebsite() != null) :?>
                <p>🔗 <a href="<?=$customer->getWebsite()?>"><?=$customer->getWebsite()?></a></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
