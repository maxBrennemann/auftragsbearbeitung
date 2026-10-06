<?php

use Src\Classes\Link;
use Src\Classes\Project\Address;
use Src\Classes\Project\Icon;
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
    <h4 class="font-semibold inline-flex items-center" data-target=".predefinedOfferTexts, #offerTexts .toggle-up, #offerTexts .toggle-down" data-toggle="true" id="offerTexts">
        <p class="py-2 cursor-pointer select-none">Vordefinierte Texte</p>
        <span class="cursor-pointer">
            <span class="toggle-up hidden"><?= Icon::getDefault("iconChevronUp") ?></span>
            <span class="toggle-down"><?= Icon::getDefault("iconChevronDown") ?></span>
        </span>
    </h4>
    <div class="predefinedOfferTexts hidden bg-white p-2 rounded-md">
        <p>Den Text zum (ab)wählen einmal anklicken. Die Angebotsvorschau wird dann neu generiert.</p>
        <div class="defaultOfferTexts grid grid-flow-row gap-4 mt-2 max-h-80 overflow-y-scroll">
            <?php foreach ($offer->getTexts() as $text): ?>
                <div class="offerTexts bg-gray-100 rounded-xl cursor-pointer p-3 mr-1 select-none flex" title="Übernehmen" data-binding="true" data-fun="toggleText" data-active="<?= $text["active"] ?>" data-id="<?= $text["id"] ?>">
                    <p class="max-h-20 overflow-auto flex-auto"><?= $text["text"] ?></p>
                    <div class="pl-3 flex items-center">
                        <?php if ($text["id"] != 0) : ?>
                            <button class="btn-edit" data-id="<?= $text["id"] ?>" data-binding="true" data-fun="editText"><?= Icon::getDefault("iconEdit") ?></button>
                            <button class="btn-delete ml-1" data-id="<?= $text["id"] ?>" data-binding="true" data-fun="deleteText"><?= Icon::getDefault("iconDelete") ?></button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-2">
            <input id="newOfferText" class="input-primary">
            <button data-binding="true" data-fun="addText" class="btn-primary">Hinzufügen</button>
        </div>
    </div>
</div>

<div class="defCont">
    <?php if (!$isFinal): ?>
        <button class="btn-primary" data-fun="storeOffer" data-binding="true">Angebot abschließen</button>
        <button class="btn-primary" data-fun="sendOffer" data-binding="true">Angebot per E-Mail senden</button>
        <button class="btn-primary" data-fun="acceptOffer" data-binding="true">Angebot annehmen</button>
        <button class="btn-cancel" data-fun="rejectOffer" data-binding="true">Angebot ablehnen</button>
    <?php endif; ?>
    <button class="btn-primary" data-fun="changeItemsOrder" data-binding="true">Reihenfolge</button>
    <?php if ($offer->getState()->value !== "accepted"): ?>
        <button class="btn-cancel" data-fun="deleteOffer" data-binding="true">Angebot löschen</button>
    <?php endif; ?>
</div>

<div class="defCont">
    <p class="font-semibold">Angebotsverlauf</p>
    <?= $history ?>
</div>

<iframe class="mt-2" id="offerPDFPreview" loading="lazy" src="/api/v1/order/offer/<?=$offer->getId()?>/pdf?customerId=<?=$customerId?>"></iframe>
