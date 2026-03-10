<?php

use Src\Classes\Link;
use Src\Classes\Project\Settings;
use Src\Classes\Project\InvoiceNumberTracker;
use Src\Classes\Project\User;

$companyLogo = Src\Classes\Project\Image::getLogo();

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
    <div class="grid grid-cols-2 gap-4">
        <div class="mt-2">
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Firmenname</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.name") ?>" data-write="true" data-fun="changeSetting" data-setting="company.name">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Adresse</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.address") ?>" data-write="true" data-fun="changeSetting" data-setting="company.address">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">PLZ</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.zip") ?>" data-write="true" data-fun="changeSetting" data-setting="company.zip">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Ort</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.city") ?>" data-write="true" data-fun="changeSetting" data-setting="company.city">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Land</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.country") ?>" data-write="true" data-fun="changeSetting" data-setting="company.country">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Telefonnummer</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.phone") ?>" data-write="true" data-fun="changeSetting" data-setting="company.phone">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">E-Mail</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.email") ?>" data-write="true" data-fun="changeSetting" data-setting="company.email">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Website</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.website") ?>" data-write="true" data-fun="changeSetting" data-setting="company.website">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Rechnungsadresse</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.imprint") ?>" data-write="true" data-fun="changeSetting" data-setting="company.imprint">
            </div>
        </div>
        <div class="mt-2">
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Stundenlohn [€]</span>
                <input class="input-primary w-60" value="<?= Settings::get("invoice.defaultWage") ?>" data-write="true" data-fun="changeSetting" data-setting="invoice.defaultWage">
            </div>

            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Bank</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.bank") ?>" data-write="true" data-fun="changeSetting" data-setting="company.bank">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">IBAN</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.IBAN") ?>" data-write="true" data-fun="changeSetting" data-setting="company.IBAN">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">BIC</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.BIC") ?>" data-write="true" data-fun="changeSetting" data-setting="company.BIC">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">UstIdNr</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.UstIdNr") ?>" data-write="true" data-fun="changeSetting" data-setting="company.UstIdNr">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Fälligkeitsdauer [Tage]</span>
                <input class="input-primary w-60" value="<?= Settings::get("invoice.dueDate") ?>" data-write="true" data-fun="changeSetting" data-setting="invoice.dueDate">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Umsatzsteuersatz [%] <button class="ml-1 info-button" data-info="vat_info"></button></span>
                <input class="input-primary w-60" value="<?= Settings::get("invoice.vatRate") ?>" data-write="true" data-fun="changeSetting" data-setting="invoice.vatRate">
            </div>
            <div class="flex items-center mt-2">
                <span class="font-semibold w-60">Rechnungskopie senden an</span>
                <input class="input-primary w-60" value="<?= Settings::get("company.invoiceCopyTo") ?>" data-write="true" data-fun="changeSetting" data-setting="company.invoiceCopyTo">
            </div>
        </div>

        <div class="mt-4 col-span-2">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="font-semibold">Firmen-/ Rechnungslogo festlegen</p>
                    <?= \Src\Classes\Controller\TemplateController::getTemplate("uploadFile", [
                        "target" => "companyLogo",
                        "singleFile" => true,
                        "accept" => "image/*",
                    ]); ?>
                    <div class="bg-white p-3 my-3 rounded-lg<?= $companyLogo ? "" : " hidden" ?>" id="companyLogo">
                        <div class="img-prev bg-gray-100 p-2 rounded-md">
                            <div class="img-prev flex justify-center items-center">
                                <img src="<?= Link::getResourcesShortLink($companyLogo, "upload") ?>" width="50px" title="Firmenlogo" data-image-id="companyLogo">
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <p class="font-semibold">Favicon/ Logo festlegen</p>
                    <?= \Src\Classes\Controller\TemplateController::getTemplate("uploadFile", [
                        "target" => "favicon",
                        "singleFile" => true,
                        "accept" => "image/*",
                    ]); ?>
                    <div class="bg-white p-3 my-3 rounded-lg" id="favicon">
                        <div class="img-prev bg-gray-100 p-2 rounded-md">
                            <div class="img-prev flex justify-center items-center">
                                <img src="<?= Link::getResourcesShortLink("favicon.png", "img") ?>" width="50px" title="Favicon" data-image-id="favicon">
                            </div>
                        </div>
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
    <h2 class="font-bold">PDF Texte</h2>
    <div class="mt-2">
        <div id="pdfTextsCont"></div>
    </div>
</section>

<section class="defCont">
    <h2 class="font-bold">Kategorien festlegen</h2>
    <div id="categoryTree" class="mt-2 ml-3"></div>
    <div class="mt-2 p-2 bg-gray-300 rounded-lg">
        <input type="text" class="input-primary" id="newCategory">
        <select id="parentCategory" class="input-primary"></select>
        <button id="addCategory" class="btn-primary">Kategorie hinzufügen</button>
    </div>
</section>

<section class="defCont">
    <h2 class="font-bold">Dateienmanagement</h2>
    <a href="#" download="temp_file_name" id="download_db" class="hidden">Datenbank herunterladen</a>
    <a href="#" download="temp_file_name" id="download_files" class="hidden">Dateien herunterladen</a>
    <div class="mt-2 flex flex-row gap-2">
        <div class="bg-gray-50 rounded-lg p-3 flex-auto">
            <button class="btn-primary" data-binding="true" data-fun="downloadDatabase">
                <i data-lucide="download" class="w-5 h-5"></i>
                <span class="ml-0.5">Datenbank herunterladen</span>
            </button>
            <button class="btn-primary ml-1" data-binding="true" data-fun="downloadAllFiles">
                <i data-lucide="download" class="w-5 h-5"></i>
                <span class="ml-0.5">Alle Dateien herunterladen</span>
            </button>
        </div>
        <div class="bg-gray-50 rounded-lg p-3 flex-auto">
            <button id="clearFiles" data-binding="true" class="btn-primary">Dateien aufräumen</button>
            <button id="adjustFiles" data-binding="true" class="btn-primary ml-1">Dateinamen anpassen</button>
            <button data-binding="true" data-fun="deleteCache" class="btn-primary mt-2">Cache löschen</button>
        </div>
    </div>
    <p id="showFilesInfo" class="mt-2"></p>
</section>

<section class="defCont">
    <h2 class="font-bold">Zeiterfassung</h2>
    <div class="switchCont mt-2">
        <?= \Src\Classes\Controller\TemplateController::getTemplate("inputSwitch", [
            "id" => "showTimeTracking",
            "name" => "Aktuelle Arbeitszeit global anzeigen",
            "value" => Settings::get("showTimeTracking", User::getCurrentUserId()) ? "checked" : "",
            "binding" => "startStopTime",
        ]); ?>
    </div>
</section>

<section class="defCont">
    <h2 class="font-bold">Routineaufgaben</h2>
</section>