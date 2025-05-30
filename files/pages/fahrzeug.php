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
                <input class="input-primary" value="<?= Fahrzeug::getName($id) ?>" data-write="true" data-fun="updateName">
                mit dem Kennzeichen
                <input class="input-primary" value="<?= Fahrzeug::getKennzeichen($id) ?>" data-write="true" data-fun="updateLicensePlate">
            </h4>
            <div class="grid grid-cols-4 gap-2 bg-white p-3 my-3 rounded-lg">
                <?php foreach (Fahrzeug::getImages($id) as $vehicle) : ?>
                    <div class="bg-gray-100 p-2 rounded-md">
                        <p>Bild vom <?= $vehicle["date"] ?></p>
                        <div class="flex justify-center items-center">
                            <img src="<?= Link::getResourcesShortLink($vehicle["file"], "upload") ?>" width="150px" title="<?= $vehicle["originalname"] ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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