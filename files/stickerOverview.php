<?php

$query = "SELECT id, `name`, directory_name, 
        IF(is_plotted = 1, '✓', 'X') AS is_plotted, 
        IF(is_short_time = 1, '✓', 'X') AS is_short_time, 
        IF(is_long_time = 1, '✓', 'X') AS is_long_time, 
        IF(is_multipart = 1, '✓', 'X') AS is_multipart, 
        IF(is_walldecal = 1, '✓', 'X') AS is_walldecal, 
        IF(is_shirtcollection = 1, '✓', 'X') AS is_shirtcollection, 
        IF(is_revised = 1, '✓', '') AS is_revised, 
        IF(is_marked = 1, '★', '') AS is_marked
    FROM `module_sticker_sticker_data`";
$data = DBAccess::selectQuery($query);

$column_names = array(
    0 => array("COLUMN_NAME" => "id", "ALT" => "Nummer"),
    1 => array("COLUMN_NAME" => "name", "ALT" => "Name"),
    2 => array("COLUMN_NAME" => "directory_name", "ALT" => "Verzeichnis"),
    3 => array("COLUMN_NAME" => "is_plotted", "ALT" => "geplottet"),
    4 => array("COLUMN_NAME" => "is_short_time", "ALT" => "Werbeaufkleber"),
    5 => array("COLUMN_NAME" => "is_long_time", "ALT" => "Hochleistungsfolie"),
    6 => array("COLUMN_NAME" => "is_multipart", "ALT" => "mehrteilig"),
    7 => array("COLUMN_NAME" => "is_walldecal", "ALT" => "Wandtattoo"),
    8 => array("COLUMN_NAME" => "is_shirtcollection", "ALT" => "Textil"),
    9 => array("COLUMN_NAME" => "is_revised", "ALT" => "Überarbeitet"),
    10 => array("COLUMN_NAME" => "is_marked", "ALT" => "Gemerkt"),
);

$linker = new Link();
$linker->addBaseLink("sticker");
$linker->setIterator("id", $data, "id");

$t = new Table();
$t->createByData($data, $column_names);
$t->setType("module_sticker_sticker_data");
$t->addLink($linker);
?>
<div class="defCont">
    <div class="productLoader" id="crawlAll">
        <div class="lds-ring" id="loaderCrawlAll"><div></div><div></div><div></div><div></div></div>
        <div>
            <progress max="1000" value="0" id="productProgress"></progress>
            <p><span id="currentProgress"></span> von <span id="maxProgress"></span></p>
            <p id="statusProgress"></p>
        </div>
    </div>
    <a href="#" onclick="crawlAll()">Alle Produtke vom Shop crawlen</a>
</div>
<div class="defCont">
    <div>
        <p><button class="showBox" id="yellow"></button> Diese Motivvariante ist im Shop, aber die Daten aus der Auftragsbearbeitung wurde nicht hochgeladen</p>
    </div>
    <div>
        <p><button class="showBox" id="green"></button> Diese Motivvariante ist im Shop und aktuell</p>
    </div>
</div>
<div class="defCont">
    <p class="pHeading">Neues Motiv hinzufügen</p>
    <input type="text" id="newTitle">
    <button type="submit" onclick="createNewSticker()">Neues Motiv erstellen</button>
</div>
<div class="defCont">
    <p class="pHeading">Motivexporte</p>
    <button id="createFbExport" data-binding="true">Facebook Export generieren</button>
</div>
<?=$t->getTable()?>