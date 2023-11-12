<script src="<?=Link::getResourcesShortLink("colorpicker.js", "js")?>"></script>
<?php 

require_once('classes/project/Table.php');
require_once('classes/front/CategoryTree.php');

/* get default wage */
$defaultWage = Config::get("defaultWage");
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

function getUserTable() {
    $data = DBAccess::selectQuery("SELECT * FROM user");
    $column_names = array(
        0 => array("COLUMN_NAME" => "id", "ALT" => "Nummer"),
        1 => array("COLUMN_NAME" => "lastname", "ALT" => "Nachname"),
        2 => array("COLUMN_NAME" => "prename", "ALT" => "Vorname"),
        3 => array("COLUMN_NAME" => "username", "ALT" => "Username"),
        4 => array("COLUMN_NAME" => "email", "ALT" => "Mail"),
        5 => array("COLUMN_NAME" => "role", "ALT" => "Rolle"),
        6 => array("COLUMN_NAME" => "max_working_hours", "ALT" => "Arbeitsstunden"),
    );

    $link = new Link();
    $link->addBaseLink("mitarbeiter");
    $link->setIterator("id", $data, "id");

    $t = new Table();
    $t->createByData($data, $column_names);
    $t->addLink($link);
    return $t->getTable();
}

$userTable = getUserTable();

$tableOrderType->defineUpdateSchedule(new UpdateSchedule("auftragstyp", $patternOrderType));

$_SESSION[$tableOrderType->getTableKey()] = serialize($tableOrderType);

?>
<script src="<?=Link::getResourcesShortLink("tableeditor.js", "js")?>"></script>
<section class="defCont">
    <h2 class="font-bold">Auftragstypen festlegen</h2>
    <?=$tableOrderType->getTable()?>
</section>
<section class="defCont">
    <h2 class="font-bold">Einkaufsmöglichkeiten festlegen</h2>
    <?php echo (new Table("einkauf"))->getTable(); ?>
</section>
<section class="defCont">
    <h2 class="font-bold">Mitarbeiter festlegen</h2>
    <?=$userTable?>
</section>
<section class="defCont">
    <h2 class="font-bold">Stundenlohn festlegen</h2>
    <input type="number" id="defaultWage" value="<?=$defaultWage?>" class="px-4 py-2 m-1 text-sm text-slate-600 rounded-lg">
</section>
<section class="defCont">
    <h2 class="font-bold">Cache</h2>
	<input onchange="toggleCache('on')" type="radio" name="cacheswitch" value="on" <?=$cacheOn?>> Cache aktivieren<br>
	<input onchange="toggleCache('off')" type="radio" name="cacheswitch" value="off" <?=$cacheOff?>> Cache deaktivieren<br>
    <button id="deleteCache" class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none">Cache löschen</button>
</section>
<section class="defCont">
    <h2 class="font-bold">CSS und JS komprimieren</h2>
	<input onchange="toggleMinify('on')" type="radio" name="minifyswitch" value="on" <?=$minifyOn?>> Komprimierung aktivieren<br>
	<input onchange="toggleMinify('off')" type="radio" name="minifyswitch" value="off" <?=$minifyOff?>> Komprimierung deaktivieren<br>
    <button onclick="minifyFiles()" class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none">Neu komprimieren</button>
</section>
<section class="defCont">
    <h2 class="font-bold">Suche</h2>
	<button class="btn-primary" id="addDocs">Neu indizieren</button>
    <button class="btn-primary" id="test">Neu test</button>
</section>
<section class="defCont">
    <h2 class="font-bold">Persönliche Einstellungen</h2>
    <div class="defCont" id="farbe">
        <h4>Farbtöne festlegen</h4>
        <select>
            <option value="1">Tabellenfarbe</option>
            <option value="2">Äußere Rahmen</option>
            <option value="3">Innere Rahmen</option>
        </select>
        <script>var cp = new Colorpicker(document.getElementById("farbe"));</script>
        <button onclick="setCustomColor();" class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none">Diese Farbe übernehmen</button>
        <button onclick="setCustomColor(0);" class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none">Auf Standard zurücksetzen</button>
    </div>
</section>
<section class="defCont">
    <h2 class="font-bold">Kategorien festlegen</h2>
    <?=CategoryTree::getHTMLRepresentation()?>
    <select name="categories" id="select-category">
        <?php foreach ($categoryitems as $c): ?>
        <option value="<?=$c->id?>"><?=$c->title?></option>
        <?php endforeach; ?>
    </select>
</section>
<section class="defCont">
    <h2 class="font-bold">Backups und Datensicherung</h2>
    <a href="#" download="temp_file_name" id="download_db">Datenbank herunterladen</a>
    <button class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none">Alle Dateien herunterladen</button>
</section>
<section class="defCont">
    <h2 class="font-bold">Zeiterfassung</h2>
    <div class="switchCont">
        <label class="switch">
            <input type="checkbox" id="showTimeTracking" <?=Config::get("showTimeGlobal") == "true" ? "checked" : "" ?>>
            <span class="sliderTime round" id="startStopTime" data-binding="true"></span>
        </label>
        Aktuelle Arbeitszeit global anzeigen
    </div>
</section>
<section class="defCont">
    <h2 class="font-bold">Dateien</h2>
    <button id="clearFiles" class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none">Dateien aufräumen</button>
    <button id="adjustFiles" class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none">Dateinamen anpassen</button>
</section>
<section class="defCont">
    <h2 class="font-bold">Routineaufgaben</h2>
</section>