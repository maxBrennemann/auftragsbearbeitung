<?php

use Src\Classes\Link;
use Src\Classes\Project\Address;
use Src\Classes\Project\Icon;
use Src\Classes\Project\Kunde;

$kundenlink = Link::getPageLink("kunde") . "?id=" . $customerId;
$customer = new Kunde($customerId);

$isFinal = $offer->getState()->isFinal();
$stateLabels = [
    "open" => "Offen",
    "accepted" => "Angenommen",
    "rejected" => "Abgelehnt",
    "expired" => "Abgelaufen",
];
$stateStamps = [
    "open" => "stamp-open",
    "accepted" => "stamp-ok",
    "rejected" => "stamp-danger",
    "expired" => "stamp-muted",
];
$validUntil = $offer->getValidUntil();

$offerAddresses = Address::getAllAdressesFormatted($customerId);
$offerContacts = Kunde::getContacts($customerId);

?>
<div class="mx-2.5 mt-2 mb-4 flex flex-wrap items-end justify-between gap-3">
    <div>
        <span class="group-label text-text-muted">Angebot für <?= $customer->getFirmenname() ?: trim($customer->getVorname() . " " . $customer->getNachname()) ?></span>
        <h2 class="page-title flex items-center gap-2">
            <?= $offer->getOfferNumber() > 0 ? "Angebot Nr. " . $offer->getOfferNumber() : "Neues Angebot" ?>
            <span class="stamp <?= $stateStamps[$offer->getState()->value] ?? "stamp-open" ?>"><?= $stateLabels[$offer->getState()->value] ?? $offer->getState()->value ?></span>
        </h2>
        <p class="text-xs text-text-muted">Gültig bis <?= $validUntil ? date('d.m.Y', strtotime($validUntil)) : "–" ?></p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <?php if (!$isFinal): ?>
            <button class="btn-secondary" data-fun="sendOffer" data-binding="true"><?= Icon::get("mail") ?>Per E-Mail senden</button>
            <button class="btn-primary" data-fun="storeOffer" data-binding="true"><?= Icon::get("check") ?>Angebot abschließen</button>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2">
    <section class="defCont">
        <div class="section-head">
            <h2 class="section-title">Empfänger und Daten</h2>
            <a href="<?= $kundenlink ?>" class="link-primary text-sm">Zum Kunden</a>
        </div>
        <div class="sheet mb-4">
            <p class="font-semibold"><?= $customer->getFirmenname() ?></p>
            <p><?= $customer->getVorname() ?> <?= $customer->getNachname() ?></p>
            <p><?= $customer->getStrasse() ?> <?= $customer->getHausnummer() ?></p>
            <p><?= $customer->getPostleitzahl() ?> <?= $customer->getOrt() ?></p>
            <button class="btn-secondary btn-sm mt-2" data-binding="true" data-fun="addAltName"><?= Icon::get("pencil", 14) ?>Kopfzeile anpassen</button>
        </div>

        <div class="field-grid">
            <div class="field col-span-full">
                <label class="field-label" for="addressId">Adresse</label>
                <?php if (empty($offerAddresses)): ?>
                    <p class="field-hint">Keine Adresse vorhanden oder unvollständig. Bei Bedarf <a href="<?= $kundenlink ?>" class="link-primary">beim Kunden</a> ergänzen.</p>
                <?php else: ?>
                    <select id="addressId" class="input-primary" data-write="true" data-fun="selectAddress">
                        <?php foreach ($offerAddresses as $i => $r): ?>
                            <option value="<?= $i ?>" <?= $offer->getAddressId() == $i ? "selected" : "" ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="field col-span-full">
                <label class="field-label" for="contactId">Ansprechpartner</label>
                <?php if (empty($offerContacts)): ?>
                    <p class="field-hint">Keine Ansprechpartner vorhanden. Bei Bedarf <a href="<?= $kundenlink ?>" class="link-primary">beim Kunden</a> ergänzen.</p>
                <?php else: ?>
                    <select id="contactId" class="input-primary" data-write="true" data-fun="selectContact">
                        <option value="0">Kein Ansprechpartner</option>
                        <?php foreach ($offerContacts as $i => $r): ?>
                            <option value="<?= $i ?>" <?= $offer->getContactId() == $i ? "selected" : "" ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <label class="field">
                <span class="field-label">Datum</span>
                <input id="angebotsdatum" type="date" class="input-primary" value="<?= date('Y-m-d') ?>">
            </label>
        </div>
    </section>

    <section class="defCont">
        <div class="section-head">
            <h2 class="section-title">Texte</h2>
        </div>
        <button type="button" class="disclosure" data-target=".predefinedOfferTexts, #offerTexts .toggle-up, #offerTexts .toggle-down" data-toggle="true" id="offerTexts">
            <span>Vordefinierte Texte</span>
            <span>
                <span class="toggle-up hidden"><?= Icon::get("chevron-up") ?></span>
                <span class="toggle-down"><?= Icon::get("chevron-down") ?></span>
            </span>
        </button>
        <div class="predefinedOfferTexts hidden pt-3">
            <?php if (empty($offer->getTexts())): ?>
                <p class="text-sm text-text-muted mb-2">Es gibt noch keine vordefinierten Texte. Eigene Texte lassen sich unten hinzufügen, feste Textbausteine (Art „offer_default_text“) in den Einstellungen unter „PDF-Texte“.</p>
            <?php else: ?>
                <p class="field-hint mb-2">Anklicken, um einen Text ins Angebot zu übernehmen oder wieder zu entfernen. Die Vorschau aktualisiert sich danach.</p>
            <?php endif; ?>
            <div class="defaultOfferTexts flex flex-col gap-2 max-h-96 overflow-y-auto pr-1">
                <?php foreach ($offer->getTexts() as $text): ?>
                    <div class="offerTexts choice-card" role="button" tabindex="0" aria-pressed="<?= $text["active"] == 1 ? "true" : "false" ?>" data-binding="true" data-fun="toggleText" data-active="<?= $text["active"] ?>" data-id="<?= $text["id"] ?>">
                        <span class="choice-mark" aria-hidden="true"><?= Icon::get("check", 12) ?></span>
                        <p class="max-h-20 overflow-auto flex-auto text-sm"><?= $text["text"] ?></p>
                        <?php if ($text["id"] != 0) : ?>
                            <div class="flex items-center gap-0.5">
                                <button type="button" class="btn-ghost btn-icon btn-sm" data-id="<?= $text["id"] ?>" data-binding="true" data-fun="editText" title="Text bearbeiten" aria-label="Text bearbeiten"><?= Icon::get("pencil") ?></button>
                                <button type="button" class="btn-ghost btn-icon btn-sm text-danger" data-id="<?= $text["id"] ?>" data-binding="true" data-fun="deleteText" title="Text löschen" aria-label="Text löschen"><?= Icon::get("trash-2") ?></button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="flex gap-2 mt-3">
                <input id="newOfferText" class="input-primary flex-1" placeholder="Eigener Text für dieses Angebot" aria-label="Neuer Text">
                <button data-binding="true" data-fun="addText" class="btn-secondary"><?= Icon::get("plus", 14) ?>Hinzufügen</button>
            </div>
        </div>
    </section>
</div>

<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Posten</h2>
        <button class="btn-secondary btn-sm" data-fun="changeItemsOrder" data-binding="true"><?= Icon::get("arrow-down-up", 14) ?>Reihenfolge</button>
    </div>
    <?= \Src\Classes\Controller\TemplateController::getTemplate("invoiceItems", [
        "services" => $services
    ]); ?>
</section>

<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Status</h2>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <?php if (!$isFinal): ?>
            <button class="btn-secondary" data-fun="acceptOffer" data-binding="true"><?= Icon::get("clipboard-plus") ?>Annehmen und Auftrag anlegen</button>
            <button class="btn-secondary" data-fun="rejectOffer" data-binding="true"><?= Icon::get("x") ?>Als abgelehnt markieren</button>
        <?php else: ?>
            <p class="text-sm text-text-muted">Das Angebot ist abgeschlossen.</p>
        <?php endif; ?>
        <?php if ($offer->getState()->value !== "accepted"): ?>
            <button class="btn-danger ml-auto" data-fun="deleteOffer" data-binding="true"><?= Icon::get("trash-2") ?>Angebot löschen</button>
        <?php endif; ?>
    </div>
    <span class="group-label mt-5 mb-2">Verlauf</span>
    <?= $history ?>
</section>

<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Vorschau</h2>
        <span class="section-meta">wird nach jeder Änderung neu erzeugt</span>
    </div>
    <iframe id="offerPDFPreview" loading="lazy" class="w-full h-lvh bg-sheet border border-line-soft rounded-control" title="Angebot als PDF" src="/api/v1/order/offer/<?= $offer->getId() ?>/pdf?customerId=<?= $customerId ?>"></iframe>
</section>
