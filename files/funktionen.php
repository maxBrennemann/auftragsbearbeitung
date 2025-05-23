<?php

use Classes\Link;

?>
<?php

$links = [
    Link::getPageLink("wiki"),
    Link::getPageLink("payments"),
    Link::getPageLink("diagramme"),
    Link::getPageLink("produkt"),
    Link::getPageLink("leistungen"),
    Link::getPageLink("neuer-kunde"),
    Link::getPageLink("kunde"),
    Link::getPageLink("neuer-auftrag"),
    Link::getPageLink("angebot"),
    Link::getPageLink("mitarbeiter"),
];

?>

<div class="defCont">
    <h2 class="font-bold mt-2">Funktionen der Auftragsbearbeitung</h2>
    <ul>
        <li>Kundenverwaltung</li>
            <ul>
                <li class="clickable" data-name="neuerKunde" data-intent="create">Anlegen eines neuen Kunden <a class="link-primary" href="<?=$links[5]?>" class="extLinks">➦</a></li>
                <li class="clickable" data-name="kunde" data-intent="change-data">Kundendaten ändern <a class="link-primary" href="<?=$links[6]?>?id=37" class="extLinks">➦</a></li>
            </ul>
        <li>Auftragsverwaltung</li>
            <ul>
                <li class="clickable" data-name="neuer-auftrag" data-intent="change-data">Anlegen eines neuen Auftrags <a class="link-primary" href="<?=$links[7]?>?id=37" class="extLinks">➦</a></li>
                <li>Posten hinzufügen und bearbeiten</li>
                <li>Bearbeitungsschritte und Notizen</li>
                <li>Rechnungsstellung</li>
            </ul>
        <li>Angebote</li>
            <ul>
                <li class="clickable" data-name="angebot" data-intent="change-data">Anlegen eines neuen Angebots <a class="link-primary" href="<?=$links[8]?>?id=37" class="extLinks">➦</a></li>
                <li>Posten hinzufügen</li>
                <li>Angebot in Auftrag übernehmen</li>
            </ul>
        <li>Rechnungen</li>
        <li><a class="link-primary" href="<?=$links[4]?>">Leistungen</a></li>
        <li><a class="link-primary" href="<?=$links[3]?>">Produkte</a></li>
        <li><a class="link-primary" href="<?=$links[2]?>">Analyse und Statistik</a></li>
        <li><a class="link-primary" href="<?=$links[1]?>">Finanzmanager</a></li>
        <li><a class="link-primary" href="<?=$links[0]?>">Firmenwiki</a></li>
        <li><a class="link-primary" href="<?=$links[9]?>">Mitarbeiter</a></li>
    </ul>
    <h2 class="font-bold mt-5">Funktionen der Besucherseite</h2>
    <ul>
        <li>Startseite</li>
        <li>Produkte</li>
        <li>Warenkorb</li>
        <li>Bestellvorgang</li>
    </ul>
</div>

<div class="manualNavigator">
    <button>◀</button>
    <button>▶</button>
    <p></p>
</div>