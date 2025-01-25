<?php

use Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;

use Classes\Project\CacheManager;
use Classes\Project\Config;

$defaultWage = (int) Config::get("defaultWage");
$cacheStatus = CacheManager::getCacheStatus();
$minifyStatus = Config::get("minifyStatus");

?>
<script src="<?=Link::getResourcesShortLink("colorpicker.js", "js")?>"></script>
<section class="defCont">
    <h2 class="font-bold">Auftragstypen festlegen</h2>
    <div id="orderTypes" class="mt-2"></div>
</section>
<section class="defCont">
    <h2 class="font-bold">Einkaufsmöglichkeiten festlegen</h2>
    <div id="wholesalerTypes" class="mt-2"></div>
</section>
<section class="defCont">
    <h2 class="font-bold">Mitarbeiter festlegen</h2>
    <div id="userTable" class="mt-2"></div>
</section>
<section class="defCont">
    <h2 class="font-bold">Stundenlohn festlegen</h2>
    <input type="number" id="defaultWage" value="<?=$defaultWage?>" class="px-4 py-2 m-1 text-sm text-slate-600 rounded-lg">
</section>
<section class="defCont">
    <h2 class="font-bold">Cache</h2>
	<input data-write="true" data-fun="toggleCache" type="radio" data-value="on" name="cacheswitch" value="on" <?=$cacheStatus == "on" ? "checked" : "" ?>> Cache aktivieren<br>
	<input data-write="true" data-fun="toggleCache" type="radio" data-value="off" name="cacheswitch" value="off" <?=$cacheStatus == "off" ? "checked" : "" ?>> Cache deaktivieren<br>
    <button data-binding="true" data-fun="deleteCache" class="btn-primary-new mt-2">Cache löschen</button>
</section>
<section class="defCont">
    <h2 class="font-bold">CSS und JS komprimieren</h2>
	<input data-write="true" data-fun="toggleMinify" type="radio" data-value="on" name="minifyswitch" value="on" <?=$minifyStatus == "on" ? "checked" : "" ?>> Komprimierung aktivieren<br>
	<input data-write="true" data-fun="toggleMinify" type="radio" data-value="off" name="minifyswitch" value="off" <?=$minifyStatus == "off" ? "checked" : "" ?>> Komprimierung deaktivieren<br>
    <button data-binding="true" data-fun="minifyFiles" class="btn-primary-new mt-2">Neu komprimieren</button>
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
    <div id="categoryTree" class="mt-2 ml-3"></div>
    <div class="mt-2 p-2 bg-slate-300 rounded-lg">
        <input type="text" class="input-primary" id="newCategory">
        <select id="parentCategory" class="input-primary"></select>
        <button id="addCategory" class="btn-primary">Kategorie hinzufügen</button>
    </div>
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