<?php 

require_once('classes/project/Table.php');
require_once('classes/front/CategoryTree.php');

$categoryitems = CategoryTree::getOneLayerArray();

$cacheOn = "";
$cacheOff = "checked";

$cacheStatus = CacheManager::getCacheStatus();

if ($cacheStatus == "on") {
    $cacheOn = "checked";
    $cacheOff = "";
}

$minifyOn = "";
$minifyOff = "checked";

$query = "SELECT content FROM settings WHERE title = 'minifyStatus' LIMIT 1";
$result = DBAccess::selectQuery($query);
$minifyStatus = $result[0]["content"];

if ($minifyStatus == "on") {
    $minifyOn = "checked";
    $minifyOff = "";
}

/* Auftragstypen Tabelle */
$tableOrderType = new Table("auftragstyp", -1);
$tableOrderType->setType("auftragstyp");
$tableOrderType->addNewLineButton();

$patternOrderType = [
    "Auftragstyp" => [
        "status" => "unset",
        "value" => 1,
    ],
];

$tableOrderType->defineUpdateSchedule(new UpdateSchedule("auftragstyp", $patternOrderType));


$_SESSION[$tableOrderType->getTableKey()] = serialize($tableOrderType);

?>
<script src="<?=Link::getResourcesShortLink("tableeditor.js", "js")?>"></script>
<section class="defCont">
    <h2>Auftragstypen festlegen</h2>
    <?=$tableOrderType->getTable()?>
</section>
<section class="defCont">
    <h2>Einkaufsmöglichkeiten festlegen</h2>
    <?php echo (new Table("einkauf"))->getTable(); ?>
</section>
<section class="defCont">
    <h2>Mitarbeiter festlegen</h2>
    <?php echo (new Table("mitarbeiter"))->getTable(); ?>
</section>
<section class="defCont">
    <h2>Cache</h2>
	<input onchange="toggleCache('on')" type="radio" name="cacheswitch" value="on" <?=$cacheOn?>> Cache aktivieren<br>
	<input onchange="toggleCache('off')" type="radio" name="cacheswitch" value="off" <?=$cacheOff?>> Cache deaktivieren<br>
    <button id="deleteCache">Cache löschen</button>
</section>
<section class="defCont">
    <h2>CSS und JS komprimieren</h2>
	<input onchange="toggleMinify('on')" type="radio" name="minifyswitch" value="on" <?=$minifyOn?>> Komprimierung aktivieren<br>
	<input onchange="toggleMinify('off')" type="radio" name="minifyswitch" value="off" <?=$minifyOff?>> Komprimierung deaktivieren<br>
    <button>Neu komprimieren</button>
    <!-- TODO: neu komprimieren btn ohne Funktion -->
</section>
<section class="defCont">
    <h2>Persönliche Einstellungen</h2>
    <div class="defCont" id="farbe">
        <h4>Farbtöne festlegen</h4>
        <select>
            <option value="1">Tabellenfarbe</option>
            <option value="2">Äußere Rahmen</option>
            <option value="3">Innere Rahmen</option>
        </select>
        <script>var cp = new Colorpicker(document.getElementById("farbe"));</script>
        <button onclick="setCustomColor();">Diese Farbe übernehmen</button>
        <button onclick="setCustomColor(0);">Auf Standard zurücksetzen</button>
    </div>
</section>
<section class="defCont">
    <h2>Kategorien festlegen</h2>
    <?=CategoryTree::getHTMLRepresentation()?>
    <select name="categories" id="select-category">
        <?php foreach ($categoryitems as $c): ?>
        <option value="<?=$c->id?>"><?=$c->title?></option>
        <?php endforeach; ?>
    </select>
</section>
<section class="defCont">
    <h2>Backups und Datensicherung</h2>
    <a href="#" download="temp_file_name" id="download_db">Datenbank herunterladen</a>
    <button>Alle Dateien herunterladen</button>
</section>
<section class="defCont">
    <h2>Zeiterfassung</h2>
    <div class="switchCont">
        <label class="switch">
            <input type="checkbox" id="showTimeTracking" <?=Envs::get("showTimeGlobal") == "true" ? "checked" : "" ?>>
            <span class="sliderTime round" id="startStopTime" data-binding="true"></span>
        </label>
        Aktuelle Arbeitszeit global anzeigen
    </div>
</section>