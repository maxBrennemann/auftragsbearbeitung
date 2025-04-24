<?php

use Classes\Link;
use Classes\Project\Kunde;

$kundenlink = $kundenlink = Link::getPageLink("kunde") . "?id=" . $customerId;
$customer = new Kunde($customerId);

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
        <span>Angebotsnummer: </span>
    </div>
</div>

<div class="defCont">
    <?= \Classes\Project\TemplateController::getTemplate("invoiceItems", [
        "services" => $services
    ]); ?>
</div>

<div class="defCont">
    <p>Text hinzufügen</p>
    <textarea class="input-primary"></textarea>
</div>

<div class="defCont">
    <button class="btn-primary-new" data-fun="storeOffer" data-binding="true">Angebot abschließen</button>
    <button class="btn-cancel" data-fun="deleteOffer" data-binding="true">Angebot abbrechen</button>
</div>

<iframe class="mt-2" id="offerPDFPreview" loading="lazy" src="/pdf?type=offer&offerId=<?=$offer->getId()?>&customerId=<?=$customerId?>"></iframe>