<?php 

require "vendor/autoload.php";
use Masterminds\HTML5;

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

/*
 * funktionen.php nutzt DomDocument, um auf die Inhalte der anderen Seiten zuzugreifen.
 * Besser wäre es wahrscheinlich, wenn diese modular wären und die Funktionen für die Inhalte
 * den passenden Code liefern.
 * Problem bei der jetzigen Lösung: wechselnde Ids oder veränderte Strukturen.
 */
function loadExternalById($page, $id) {
    $html5 = new HTML5();
    $page = Login::curlLogin($page);
    $dom1 = $html5->loadHTML($page);

    $idContent = $dom1->getElementById($id);
    if ($idContent != null)
        echo $html5->saveHTML($idContent);
}

function loadById($page, $id) {
    $html5 = new HTML5();
    $page = "files/" . $page . ".php";
    $dom1 = $html5->loadHTML($page);

    $idContent = $dom1->getElementById($id);
    if ($idContent != null)
        echo $html5->saveHTML($idContent);
}

/* 
* https://stackoverflow.com/questions/6366351/getting-dom-elements-by-classname 
*/
function loadExternalByClassName($page, $classname) {
    $page = 'http://' . $_SERVER['HTTP_HOST'] . Link::getPageLink($page);

    $external = new DOMDocument();
    $external->validateOnParse = true;
    $external->loadHtml(file_get_contents($page));

    $finder = new DomXPath($external);
    $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

    return var_dump($nodes);
}

?>

<div class="defCont">
    <h2>Funktionen der Auftragsbearbeitung</h2>
    <ul>
        <li>Kundenverwaltung</li>
            <ul>
                <li class="clickable" data-name="neuerKunde" data-intent="create">Anlegen eines neuen Kunden <a href="#" class="extLinks">➦</a></li>
                <li class="clickable" data-name="kunde" data-intent="change-data">Kundendaten ändern</li>
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

<div class="manualNavigator">
    <button>◀</button>
    <button>▶</button>
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

    .clickable {
        cursor: pointer;
    }

    .manualNavigator {
        display: none;

        border-radius: 6px;
        border: 1px solid grey;
        padding: 7px;
        margin: 5px;
    }

    .manualNavigator button {
        border: none;
        margin: 5px;
        background: lightgray;
        padding: 5px;
    }
</style>