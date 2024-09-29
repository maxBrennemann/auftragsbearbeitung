<?php

use Classes\Link;

use Classes\Project\Fahrzeug;

$id = isset($_GET['id']) ? (int) $_GET['id'] : -1;
$kunde = Fahrzeug::returnCustomer($id);

if ($id <= 0) :
    throw new Exception("Fahrzeug kann nicht angezeigt werden!");
else : ?>
    <div class="imageCont">
        <h4>Bilder des Fahrzeuges <i><?=Fahrzeug::getName($id)?></i> mit dem Kennzeichen <i><?=Fahrzeug::getKennzeichen($id)?></i></h4>
        <?=Fahrzeug::getImages($id)?>
    </div>
    <br>
    <div>
        <?=$kunde->getVorname()?> <?=$kunde->getNachname()?><br><?=$kunde->getFirmenname()?><br>Adresse: <br><?=$kunde->getStrasse()?> <?=$kunde->getHausnummer()?><br>
		<?=$kunde->getPostleitzahl()?> <?=$kunde->getOrt()?><br><a href="mailto:<?=$kunde->getEmail()?>"><?=$kunde->getEmail()?></a><br>
		<a href="<?=Link::getPageLink("kunde")?>?id=<?=$kunde->getKundennummer()?>">Kunde <span id="kundennummer"><?=$kunde->getKundennummer()?></span> zeigen</a>
    </div>
<?php endif; ?>