<?php

use Src\Classes\Link;
use Src\Classes\Project\Kunde;

$kundenlink = $kundenlink = Link::getPageLink("kunde") . "?id=" . $customerId;
$customer = new Kunde($customerId);

$isFinal = $offer->getState()->isFinal();
$stateLabels = [
    "open" => "Offen",
    "accepted" => "Angenommen",
    "rejected" => "Abgelehnt",
    "expired" => "Abgelaufen",
];
$validUntil = $offer->getValidUntil();

?>
<div class="defCont">
    <div>
        <p><a href="<?= $kundenlink ?>"><b><?= $customer->getFirmenname() ?></b></a></p>
        <p><?= $customer->getVorname() ?> <?= $customer->getNachname() ?></p>
        <p><?= $customer->getStrasse() ?> <?= $customer->getHausnummer() ?></p>
        <p><?= $customer->getPostleitzahl() ?> <?= $customer->getOrt() ?></p>
    </div>
    <div>
        <span>Datum: <input id="angebotsdatum" type="date" class="input-primary" value="<?= date('Y-m-d') ?>"></span><br>
        <span>Angebotsnummer: <?= $offer->getOfferNumber() > 0 ? $offer->getOfferNumber() : "Entwurf" ?></span><br>
        <span>Gültig bis: <?= $validUntil ? date('d.m.Y', strtotime($validUntil)) : "-" ?></span><br>
        <span>Status: <span class="info-badge"><?= $stateLabels[$offer->getState()->value] ?? $offer->getState()->value ?></span></span>
    </div>
</div>

<div class="defCont">
    <?= \Src\Classes\Controller\TemplateController::getTemplate("invoiceItems", [
        "services" => $services
    ]); ?>
</div>

<div class="defCont">
    <p>Text hinzufügen</p>
    <textarea class="input-primary"></textarea>
</div>

<div class="defCont">
    <?php if (!$isFinal): ?>
        <button class="btn-primary" data-fun="storeOffer" data-binding="true">Angebot abschließen</button>
        <button class="btn-primary" data-fun="sendOffer" data-binding="true">Angebot per E-Mail senden</button>
        <button class="btn-primary" data-fun="acceptOffer" data-binding="true">Angebot annehmen</button>
        <button class="btn-cancel" data-fun="rejectOffer" data-binding="true">Angebot ablehnen</button>
    <?php endif; ?>
    <button class="btn-cancel" data-fun="deleteOffer" data-binding="true">Angebot löschen</button>
</div>

<div class="defCont">
    <p class="font-semibold">Angebotsverlauf</p>
    <?= $history ?>
</div>

<iframe class="mt-2" id="offerPDFPreview" loading="lazy" src="/api/v1/order/offer/<?=$offer->getId()?>/pdf?customerId=<?=$customerId?>"></iframe>
