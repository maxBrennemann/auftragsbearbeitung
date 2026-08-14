<?php

use Src\Classes\Link;
use Src\Classes\Project\Address;
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

$offerAddresses = Address::getAllAdressesFormatted($customerId);
$offerContacts = Kunde::getContacts($customerId);

?>
<div class="defCont">
    <div>
        <p><a href="<?= $kundenlink ?>"><b><?= $customer->getFirmenname() ?></b></a></p>
        <p><?= $customer->getVorname() ?> <?= $customer->getNachname() ?></p>
        <p><?= $customer->getStrasse() ?> <?= $customer->getHausnummer() ?></p>
        <p><?= $customer->getPostleitzahl() ?> <?= $customer->getOrt() ?></p>
        <button class="btn-primary mt-2" data-binding="true" data-fun="addAltName">Alternativtext eingeben</button>

        <h4 class="mt-2 font-semibold">Adresse auswählen</h4>
        <?php if (empty($offerAddresses)): ?>
            <i>Keine Rechnungsadressen vorhanden oder unvollständig. Bei Bedarf <a href="<?= Link::getPageLink("kunde") . "?id=" . $customerId ?>" class="link-primary">beim Kunden</a> ergänzen.</i>
        <?php else: ?>
            <select id="addressId" class="input-primary w-72 mt-1" data-write="true" data-fun="selectAddress">
                <?php foreach ($offerAddresses as $i => $r): ?>
                    <option value="<?= $i ?>" <?= $offer->getAddressId() == $i ? "selected" : "" ?>><?= $r ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <h4 class="mt-2 font-semibold">Ansprechpartner auswählen</h4>
        <?php if (empty($offerContacts)): ?>
            <i>Keine Ansprechpartner vorhanden. Bei Bedarf <a href="<?= Link::getPageLink("kunde") . "?id=" . $customerId ?>" class="link-primary">beim Kunden</a> ergänzen.</i>
        <?php else: ?>
            <select id="contactId" class="input-primary w-72 mt-1" data-write="true" data-fun="selectContact">
                <option value="0">Kein Ansprechpartner</option>
                <?php foreach ($offerContacts as $i => $r): ?>
                    <option value="<?= $i ?>" <?= $offer->getContactId() == $i ? "selected" : "" ?>><?= $r ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
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
