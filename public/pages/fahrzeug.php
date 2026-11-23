<?php

use Src\Classes\Link;
use Src\Classes\Project\Fahrzeug;
use Src\Classes\Project\Icon;
use MaxBrennemann\PhpUtilities\Tools;

$id = max(0, (int) Tools::get("id"));
$kunde = $id > 0 ? Fahrzeug::returnCustomer($id) : null;

if ($kunde == null) : ?>
    <div class="note note-warn m-2.5">
        <p class="note-head"><?= Icon::get("triangle-alert") ?>Kein Fahrzeug ausgewählt</p>
        <p>Fahrzeuge werden beim jeweiligen Kunden oder Auftrag verwaltet und von dort aus geöffnet.</p>
    </div>
<?php else :
    $vehicleImages = Fahrzeug::getImages($id);
?>
    <input type="hidden" value="<?= $id ?>" id="vehicleId">

    <div class="mx-2.5 mt-2 mb-4">
        <span class="group-label text-text-muted">Fahrzeug von <?= $kunde->getFirmenname() ?: trim($kunde->getVorname() . " " . $kunde->getNachname()) ?></span>
        <h2 class="page-title"><?= Fahrzeug::getKennzeichen($id) ?: "Ohne Kennzeichen" ?></h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3">
        <section class="defCont lg:col-span-2">
            <div class="section-head">
                <h2 class="section-title">Fahrzeugdaten</h2>
                <span class="section-meta">Änderungen werden sofort gespeichert</span>
            </div>
            <div class="field-grid">
                <label class="field">
                    <span class="field-label">Fahrzeug</span>
                    <input class="input-primary" value="<?= Fahrzeug::getName($id) ?>" data-write="true" data-fun="updateName">
                </label>
                <label class="field">
                    <span class="field-label">Kennzeichen</span>
                    <input class="input-primary" value="<?= Fahrzeug::getKennzeichen($id) ?>" data-write="true" data-fun="updateLicensePlate">
                </label>
            </div>

            <span class="group-label mt-6 mb-2">Bilder</span>
            <?php if (empty($vehicleImages)) : ?>
                <p class="text-sm text-text-muted">Zu diesem Fahrzeug gibt es noch keine Bilder.</p>
            <?php else : ?>
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
                    <?php foreach ($vehicleImages as $vehicle) : ?>
                        <figure class="sheet m-0 p-2">
                            <div class="grid place-items-center bg-[#f6f7f8] rounded-control aspect-[4/3] overflow-hidden">
                                <img src="<?= Link::getResourcesShortLink($vehicle["file"], "upload") ?>" class="max-w-full max-h-full object-contain" alt="<?= htmlspecialchars($vehicle["originalname"]) ?>" title="<?= htmlspecialchars($vehicle["originalname"]) ?>">
                            </div>
                            <figcaption class="text-xs text-text-muted mt-1.5">Bild vom <?= $vehicle["date"] ?></figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="defCont">
            <div class="section-head">
                <h2 class="section-title">Kunde</h2>
                <a href="<?= Link::getPageLink("kunde") ?>?id=<?= $kunde->getKundennummer() ?>" class="link-primary text-sm">Kunde <span id="kundennummer"><?= $kunde->getKundennummer() ?></span> anzeigen</a>
            </div>
            <div class="sheet">
                <p class="font-semibold"><?= $kunde->getFirmenname() ?></p>
                <p><?= $kunde->getVorname() ?> <?= $kunde->getNachname() ?></p>
                <p class="mt-2"><?= $kunde->getStrasse() ?> <?= $kunde->getHausnummer() ?></p>
                <p><?= $kunde->getPostleitzahl() ?> <?= $kunde->getOrt() ?></p>
                <?php if ($kunde->getEmail()) : ?>
                    <p class="mt-2 flex items-center gap-1.5 text-sm text-text-muted"><?= Icon::get("mail", 14) ?><a class="link-primary font-normal" href="mailto:<?= $kunde->getEmail() ?>"><?= $kunde->getEmail() ?></a></p>
                <?php endif; ?>
            </div>
        </section>
    </div>
<?php endif; ?>
