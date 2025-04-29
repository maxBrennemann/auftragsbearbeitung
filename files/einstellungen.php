<?php

use Classes\Project\Config;

?>
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
    <h2 class="font-bold">Standardeinstellungen festlegen</h2>
    <div>
        <div class="flex items-center">
            <span class="font-semibold w-64">Stundenlohn</span>
            <input class="input-primary" value="<?= Config::get("defaultWage") ?>" data-write="true" data-fun="changeSetting" data-setting="defaultWage">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">Firmenname</span>
            <input class="input-primary" value="<?= Config::get("companyName") ?>" data-write="true" data-fun="changeSetting" data-setting="companyName">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">Adresse</span>
            <input class="input-primary" value="<?= Config::get("companyAddress") ?>" data-write="true" data-fun="changeSetting" data-setting="companyAddress">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">PLZ</span>
            <input class="input-primary" value="<?= Config::get("companyZip") ?>" data-write="true" data-fun="changeSetting" data-setting="companyZip">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">Stadt</span>
            <input class="input-primary" value="<?= Config::get("companyCity") ?>" data-write="true" data-fun="changeSetting" data-setting="companyCity">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">Land</span>
            <input class="input-primary" value="<?= Config::get("companyCountry") ?>" data-write="true" data-fun="changeSetting" data-setting="companyCountry">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">Telefonnummer</span>
            <input class="input-primary" value="<?= Config::get("companyPhone") ?>" data-write="true" data-fun="changeSetting" data-setting="companyPhone">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">Email</span>
            <input class="input-primary" value="<?= Config::get("companyEmail") ?>" data-write="true" data-fun="changeSetting" data-setting="companyEmail">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">Website</span>
            <input class="input-primary" value="<?= Config::get("companyWebsite") ?>" data-write="true" data-fun="changeSetting" data-setting="companyWebsite">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">Impressum</span>
            <input class="input-primary" value="<?= Config::get("companyImprint") ?>" data-write="true" data-fun="changeSetting" data-setting="companyImprint">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">Bank</span>
            <input class="input-primary" value="<?= Config::get("companyBank") ?>" data-write="true" data-fun="changeSetting" data-setting="companyBank">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">IBAN</span>
            <input class="input-primary" value="<?= Config::get("companyIban") ?>" data-write="true" data-fun="changeSetting" data-setting="companyIban">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-64">UstIdNr</span>
            <input class="input-primary" value="<?= Config::get("companyUstIdNr") ?>" data-write="true" data-fun="changeSetting" data-setting="companyUstIdNr">
        </div>
    </div>
</section>
<section class="defCont">
    <h2 class="font-bold">Cache</h2>
    <input data-write="true" data-fun="toggleCache" type="radio" data-value="on" name="cacheswitch" value="on" <?= CACHE_STATUS == "on" ? "checked" : "" ?>> Cache aktivieren<br>
    <input data-write="true" data-fun="toggleCache" type="radio" data-value="off" name="cacheswitch" value="off" <?= CACHE_STATUS == "off" ? "checked" : "" ?>> Cache deaktivieren<br>
    <button data-binding="true" data-fun="deleteCache" class="btn-primary-new mt-2">Cache löschen</button>
</section>
<section class="defCont">
    <h2 class="font-bold">CSS und JS komprimieren</h2>
    <input data-write="true" data-fun="toggleMinify" type="radio" data-value="on" name="minifyswitch" value="on" <?= MINIFY_STATUS == true ? "checked" : "" ?>> Komprimierung aktivieren<br>
    <input data-write="true" data-fun="toggleMinify" type="radio" data-value="off" name="minifyswitch" value="off" <?= MINIFY_STATUS == false ? "checked" : "" ?>> Komprimierung deaktivieren
</section>
<section class="defCont">
    <h2 class="font-bold">Suche</h2>
    <button class="btn-primary" id="addDocs">Neu indizieren</button>
    <button class="btn-primary" id="test">Neu test</button>
</section>
<section class="defCont">
    <h2 class="font-bold">Persönliche Einstellungen</h2>
    <div id="farbe">
        <h4>Farbtöne festlegen</h4>
        <select class="input-primary" id="selectTableColorType">
            <option value="1">Tabellenfarbe</option>
            <option value="2">Äußere Rahmen</option>
            <option value="3">Innere Rahmen</option>
        </select>
        <button data-fun="setColor" data-binding="true" class="btn-primary-new">Diese Farbe übernehmen</button>
        <button data-fun="resetColor" data-binding="true" class="btn-primary-new">Auf Standard zurücksetzen</button>
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
    <h2 class="font-bold">Dateien, Datensicherung und Backups</h2>
    <div class="mt-2">
        <a href="#" download="temp_file_name" id="download_db" class="hidden">Datenbank herunterladen</a>
        <a href="#" download="temp_file_name" id="download_files" class="hidden">Dateien herunterladen</a>
        <div>
            <button class="btn-primary-new" data-binding="true" data-fun="downloadDatabase">Datenbank herunterladen</button>
            <button class="btn-primary-new ml-1" data-binding="true" data-fun="downloadAllFiles">Alle Dateien herunterladen</button>
        </div>
        <div class="mt-3">
            <button id="clearFiles" data-binding="true" class="btn-primary-new">Dateien aufräumen</button>
            <button id="adjustFiles" data-binding="true" class="btn-primary-new ml-1">Dateinamen anpassen</button>
        </div>
        <p id="showFilesInfo" class="mt-2"></p>
    </div>
</section>
<section class="defCont">
    <h2 class="font-bold">Zeiterfassung</h2>
    <div class="switchCont mt-2">
        <?= \Classes\Project\TemplateController::getTemplate("inputSwitch", [
            "id" => "showTimeTracking",
            "name" => "Aktuelle Arbeitszeit global anzeigen",
            "value" => Config::get("showTimeGlobal") == "true" ? "checked" : "",
            "binding" => "startStopTime",
        ]); ?>
    </div>
</section>
<section class="defCont">
    <h2 class="font-bold">Routineaufgaben</h2>
</section>