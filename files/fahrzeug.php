<?php

use MaxBrennemann\PhpUtilities\Tools;

use Classes\Link;
use Classes\Project\Fahrzeug;

$id = (int) Tools::get("id");
if ($id == null || $id <= 0) {
    $id = 0;
}

$kunde = Fahrzeug::returnCustomer($id);

if ($id <= 0) :
    throw new Exception("Fahrzeug kann nicht angezeigt werden!");
else : ?>
    <input type="hidden" value="<?= $id ?>" id="vehicleId">
    <div class="defCont">
        <div class="imageCont">
            <h4>Bilder des Fahrzeuges
                <input class="input-primary-new" value="<?= Fahrzeug::getName($id) ?>" data-write="true" data-fun="updateName">
                mit dem Kennzeichen
                <input class="input-primary-new" value="<?= Fahrzeug::getKennzeichen($id) ?>" data-write="true" data-fun="updateLicensePlate">
            </h4>
            <?= Fahrzeug::getImages($id) ?>
        </div>
        <div>
            <p class="font-bold"><?= $kunde->getVorname() ?> <?= $kunde->getNachname() ?><br><?= $kunde->getFirmenname() ?></p>
            <p class="font-semibold mt-2">Adresse:</p>
            <p><?= $kunde->getStrasse() ?> <?= $kunde->getHausnummer() ?></p>
            <p><?= $kunde->getPostleitzahl() ?> <?= $kunde->getOrt() ?></p>
            <p><a href="mailto:<?= $kunde->getEmail() ?>"><?= $kunde->getEmail() ?></a></p>
            <p><a href="<?= Link::getPageLink("kunde") ?>?id=<?= $kunde->getKundennummer() ?>" class="link-primary">Kunde <span id="kundennummer"><?= $kunde->getKundennummer() ?></span> zeigen</a></p>
        </div>
    </div>
<?php endif; ?>