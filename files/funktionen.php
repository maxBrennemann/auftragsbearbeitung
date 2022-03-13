<?php 

 $links = [
    Link::getPageLink("wiki"),
    Link::getPageLink("payments"),
    Link::getPageLink("diagramme"),
    Link::getPageLink("produkt"),
    Link::getPageLink("leistungen"),
    Link::getPageLink("diagramme"),
    Link::getPageLink("diagramme"),
    Link::getPageLink("diagramme"),
    Link::getPageLink("diagramme"),

 ];

?>

<div class="defCont">
    <h2>Funktionen der Auftragsbearbeitung</h2>
    <ul>
        <li>Kundenverwaltung</li>
            <ul>
                <li>Anlegen eines neuen Kunden</li>
                <li>Kundendaten ändern</li>
            </ul>
        <li>Auftragsverwaltung</li>
            <ul>
                <li>Anlegen eines neuen Auftrags</li>
                <li>Posten hinzufügen und bearbeiten</li>
                <li>Bearbeitungsschritte und Notizen</li>
                <li>Rechnungsstellung</li>
            </ul>
        <li>Angebote</li>
            <ul>
                <li>Anlegen eines neuen Angebots</li>
                <li>Posten hinzufügen</li>
                <li>Angebot in Auftrag übernehmen</li>
            </ul>
        <li>Rechnungen</li>
        <li><a href="<?=$links[4]?>">Leistungen</a></li>
        <li><a href="<?=$links[3]?>">Produkte</a></li>
        <li><a href="<?=$links[2]?>">Analyse und Statistik</a></li>
        <li><a href="<?=$links[1]?>">Finanzmanager</a></li>
        <li><a href="<?=$links[0]?>">Firmenwiki</a></li>
    </ul>
    <h2>Funktionen der Besucherseite</h2>
    <ul>
        <li>Startseite</li>
        <li>Produkte</li>
        <li>Warenkorb</li>
        <li>Bestellvorgang</li>
    </ul>
</div>

<style>
    ul {
        padding-left: inherit;
    }

    li {
        list-style: disc;
    }

    .defCont {
        width: 80vw;
    }
</style>