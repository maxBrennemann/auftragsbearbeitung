<?php

use Src\Classes\Controller\TemplateController;
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
    <h2 class="text-xl font-bold mb-6 border-b pb-2">Standardeinstellungen</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <div class="space-y-4">
            <h3 class="font-semibold text-blue-600 mb-2 uppercase text-xs tracking-wider">Unternehmensdaten</h3>

            <div class="grid grid-cols-1 gap-4">
                <?php
                $fieldsLeft = [
                    "company.name" => "Firmenname",
                    "company.address" => "Adresse",
                    "company.zip" => "PLZ",
                    "company.city" => "Ort",
                    "company.country" => "Land",
                    "company.phone" => "Telefonnummer",
                    "company.email" => "E-Mail",
                    "company.website" => "Website",
                    "company.imprint" => "Rechnungsadresse"
                ];
                foreach ($fieldsLeft as $key => $label): ?>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-600 mb-1"><?= $label ?></label>
                        <?php if ($key == "company.imprint"): ?>
                            <textarea class="input-primary w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500"
                                data-write="true" data-fun="changeSetting" data-setting="<?= $key ?>"><?= Settings::get($key) ?></textarea>
                        <?php else: ?>
                            <input class="input-primary w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500"
                                value="<?= Settings::get($key) ?>"
                                data-write="true" data-fun="changeSetting" data-setting="<?= $key ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="font-semibold text-blue-600 mb-2 uppercase text-xs tracking-wider">Finanzen & Rechnung</h3>

            <div class="grid grid-cols-1 gap-4">
                <?php
                $fieldsRight = [
                    "invoice.defaultWage" => "Stundenlohn [€]",
                    "company.bank" => "Bank",
                    "company.IBAN" => "IBAN",
                    "company.BIC" => "BIC",
                    "company.UstIdNr" => "UstIdNr",
                    "invoice.dueDate" => "Fälligkeitsdauer [Tage]",
                    "invoice.vatRate" => "Umsatzsteuersatz [%]",
                    "company.invoiceCopyTo" => "Rechnungskopie senden an"
                ];
                foreach ($fieldsRight as $key => $label): ?>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-600 mb-1">
                            <?= $label ?>
                            <?php if ($key == 'invoice.vatRate'): ?>
                                <?= TemplateController::getTemplate("infoButton", [
                                    "infoKey" => "vat_info",
                                ]) ?>
                            <?php endif; ?>
                        </label>
                        <input class="input-primary w-full border-gray-300 rounded-md shadow-sm"
                            value="<?= Settings::get($key) ?>"
                            data-write="true" data-fun="changeSetting" data-setting="<?= $key ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-8 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                <label class="block text-sm font-bold text-orange-800 mb-2">Aktuelle Rechnungsnummer</label>
                <div class="flex gap-2">
                    <input type="number" id="newInvoiceNumber" class="input-primary flex-1" value="<?= InvoiceNumberTracker::getMaxInvoiceNumber() ?>">
                    <button class="btn-primary whitespace-nowrap" data-fun="sendNewInvoiceNumber" data-binding="true">Speichern</button>
                </div>
                <p class="text-xs text-orange-600 mt-2 font-medium flex items-center gap-1">
                    <i data-lucide="triangle-alert" class="w-4 h-4"></i>
                    <span>Überschreibt den aktuellen Zählerstand.</span>
                </p>
            </div>
        </div>
    </div>

    <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-8 border-t pt-8">

        <div class="bg-gray-50 p-6 rounded-xl border border-dashed border-gray-300">
            <p class="font-semibold mb-4 flex items-center gap-2">
                <i data-lucide="image" class="w-4 h-4"></i> Firmen-/ Rechnungslogo
            </p>
            <?= TemplateController::getTemplate("uploadFile", [
                "target" => "companyLogo",
                "singleFile" => true,
                "accept" => "image/*"
            ]); ?>

            <div class="relative bg-white p-3 my-3 rounded-lg border border-gray-200 <?= $companyLogo ? "" : " hidden" ?>" id="companyLogo">
                <div class="bg-gray-100 p-2 rounded-md flex justify-center items-center">
                    <img src="<?= $companyLogo ? Src\Classes\Link::getResourcesShortLink($companyLogo, "upload") : "" ?>"
                        class="max-h-20 object-contain" title="Firmenlogo">
                </div>
                <button class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600 transition-colors"
                    data-fun="deleteLogo" data-binding="true" title="Logo löschen">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <div class="bg-gray-50 p-6 rounded-xl border border-dashed border-gray-300">
            <p class="font-semibold mb-4 flex items-center gap-2">
                <i data-lucide="layout" class="w-4 h-4"></i> Favicon / Browser-Icon
            </p>
            <?= TemplateController::getTemplate("uploadFile", [
                "target" => "favicon",
                "singleFile" => true,
                "accept" => "image/*"
            ]); ?>

            <div class="relative bg-white p-3 my-3 rounded-lg border border-gray-200" id="favicon">
                <div class="bg-gray-100 p-2 rounded-md flex justify-center items-center">
                    <img src="<?= Src\Classes\Link::getResourcesShortLink("favicon.png", "img") ?>"
                        class="w-10 h-10 object-contain" title="Favicon">
                </div>
                <button class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600 transition-colors"
                    data-fun="deleteFavicon" data-binding="true" title="Favicon zurücksetzen">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
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
    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4 hidden">
        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex flex-col gap-2">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-tight">Backups</h4>
            <button class="btn-primary flex items-center justify-center gap-2" data-binding="true" data-fun="downloadDatabase">
                <i data-lucide="database" class="w-4 h-4"></i> Datenbank exportieren
            </button>
            <button class="btn-primary flex items-center justify-center gap-2" data-binding="true" data-fun="downloadAllFiles">
                <i data-lucide="files" class="w-4 h-4"></i> Alle Dateien (ZIP)
            </button>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex flex-col gap-2">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-tight">System-Pflege</h4>
            <div class="flex gap-2">
                <button id="clearFiles" data-binding="true" class="btn-secondary flex-1">Aufräumen</button>
                <button id="adjustFiles" data-binding="true" class="btn-secondary flex-1">Namen korrigieren</button>
            </div>
            <button data-binding="true" data-fun="deleteCache" class="btn-danger w-full mt-auto">Cache leeren</button>
        </div>
    </div>
</section>

<section class="defCont">
    <h2 class="font-bold">Zeiterfassung</h2>
    <div class="switchCont mt-2">
        <?= TemplateController::getTemplate("inputSwitch", [
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