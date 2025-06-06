<?php

use Classes\Link;

use Classes\Project\Config;
use Classes\Project\InvoiceNumberTracker;

$companyLogo = \Classes\Project\ClientSettings::getLogo();

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

<section class="defCont" id="invoiceSettings">
    <h2 class="font-bold">Standardeinstellungen festlegen</h2>
    <div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">Stundenlohn</span>
            <input class="input-primary" value="<?= Config::get("defaultWage") ?>" data-write="true" data-fun="changeSetting" data-setting="defaultWage">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">Firmenname</span>
            <input class="input-primary" value="<?= Config::get("companyName") ?>" data-write="true" data-fun="changeSetting" data-setting="companyName">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">Adresse</span>
            <input class="input-primary" value="<?= Config::get("companyAddress") ?>" data-write="true" data-fun="changeSetting" data-setting="companyAddress">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">PLZ</span>
            <input class="input-primary" value="<?= Config::get("companyZip") ?>" data-write="true" data-fun="changeSetting" data-setting="companyZip">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">Ort</span>
            <input class="input-primary" value="<?= Config::get("companyCity") ?>" data-write="true" data-fun="changeSetting" data-setting="companyCity">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">Land</span>
            <input class="input-primary" value="<?= Config::get("companyCountry") ?>" data-write="true" data-fun="changeSetting" data-setting="companyCountry">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">Telefonnummer</span>
            <input class="input-primary" value="<?= Config::get("companyPhone") ?>" data-write="true" data-fun="changeSetting" data-setting="companyPhone">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">Email</span>
            <input class="input-primary" value="<?= Config::get("companyEmail") ?>" data-write="true" data-fun="changeSetting" data-setting="companyEmail">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">Website</span>
            <input class="input-primary" value="<?= Config::get("companyWebsite") ?>" data-write="true" data-fun="changeSetting" data-setting="companyWebsite">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">Rechnungsadresse</span>
            <input class="input-primary" value="<?= Config::get("companyImprint") ?>" data-write="true" data-fun="changeSetting" data-setting="companyImprint">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">Bank</span>
            <input class="input-primary" value="<?= Config::get("companyBank") ?>" data-write="true" data-fun="changeSetting" data-setting="companyBank">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">IBAN</span>
            <input class="input-primary" value="<?= Config::get("companyIban") ?>" data-write="true" data-fun="changeSetting" data-setting="companyIban">
        </div>
        <div class="flex items-center mt-2">
            <span class="font-semibold w-60">UstIdNr</span>
            <input class="input-primary" value="<?= Config::get("companyUstIdNr") ?>" data-write="true" data-fun="changeSetting" data-setting="companyUstIdNr">
        </div>
        <div class="w-96 mt-2">
            <p class="font-semibold">Firmen-/ Rechnungslogo festlegen</p>
            <?= \Classes\Controller\TemplateController::getTemplate("uploadFile", [
				"target" => "companyLogo",
                "singleFile" => true,
                "accept" => "image/*",
			]); ?>
            <div class="bg-white p-3 my-3 rounded-lg<?= $companyLogo ? "" : " hidden" ?>" id="companyLogo">
                <div class="bg-gray-100 p-2 rounded-md">
                    <div class="flex justify-center items-center">
                        <img src="<?= Link::getResourcesShortLink($companyLogo, "upload") ?>" width="50px" title="Firmenlogo">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <p>Aktuelle Rechnungsnummer festlegen:</p>
        <div class="mt-1">
            <input type="number" id="newInvoiceNumber" class="input-primary" value="<?= InvoiceNumberTracker::getMaxInvoiceNumber() ?>">
            <button class="btn-primary ml-1" data-fun="sendNewInvoiceNumber" data-binding="true">Abschicken</button>
        </div>
        <p class="text-sm">Achtung: überschreibt die aktuelle Rechnungsnummer.</p>
    </div>
</section>

<section class="defCont">
    <h2 class="font-bold">Cache und Komprimierung</h2>
    <div class="mt-2">
        <?= \Classes\Controller\TemplateController::getTemplate("inputSwitch", [
            "id" => "minifyStatusSwitch",
            "name" => "CSS und JS komprimieren",
            "value" => MINIFY_STATUS == true ? "checked" : "",
            "binding" => "toggleMinify",
        ]); ?>
    </div>
    <div class="mt-2">
        <?= \Classes\Controller\TemplateController::getTemplate("inputSwitch", [
            "id" => "cacheStatusSwitch",
            "name" => "Cache",
            "value" => CACHE_STATUS == "on" ? "checked" : "",
            "binding" => "toggleCache",
        ]); ?>
    </div>
    <button data-binding="true" data-fun="deleteCache" class="btn-primary mt-2">Cache löschen</button>
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
            <button class="btn-primary" data-binding="true" data-fun="downloadDatabase">Datenbank herunterladen</button>
            <button class="btn-primary ml-1" data-binding="true" data-fun="downloadAllFiles">Alle Dateien herunterladen</button>
        </div>
        <div class="mt-3">
            <button id="clearFiles" data-binding="true" class="btn-primary">Dateien aufräumen</button>
            <button id="adjustFiles" data-binding="true" class="btn-primary ml-1">Dateinamen anpassen</button>
        </div>
        <p id="showFilesInfo" class="mt-2"></p>
    </div>
</section>

<section class="defCont">
    <h2 class="font-bold">Zeiterfassung</h2>
    <div class="switchCont mt-2">
        <?= \Classes\Controller\TemplateController::getTemplate("inputSwitch", [
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