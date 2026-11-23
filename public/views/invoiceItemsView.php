<div id="auftragsPostenTable" class="mt-2"></div>
<div class="mt-2">
    <button class="btn-secondary" data-binding="true" data-fun="showItemsMenu" id="showItemsMenu"><?= \Src\Classes\Project\Icon::get("plus", 14) ?>Posten hinzufügen</button>
</div>
<div id="showPostenAdd" class="hidden mt-2 bg-white rounded-lg">
    <div class="flex rounded-t-lg" role="tablist">
        <button type="button" class="tab-button tab-active" role="tab" aria-selected="true" aria-controls="time" data-tab="time">Zeiterfassung</button>
        <button type="button" class="tab-button" role="tab" aria-selected="false" aria-controls="service" data-tab="service">Kostenerfassung</button>
        <button type="button" class="tab-button" role="tab" aria-selected="false" aria-controls="product" data-tab="product" disabled>Produkte</button>
    </div>
    <div class="tab-content grid grid-cols-3 gap-4" id="time" role="tabpanel" data-tab-panel>
        <div class="container">
            <div class="flex flex-col">
                <span>Zeit in Minuten:</span>
                <input class="input-primary mt-1" id="timeInput" type="number" min="0">
            </div>
            <div class="mt-1 flex flex-col">
                <span>Stundenlohn [€]:</span>
                <input class="input-primary mt-1" id="wage" type="number" min="0" step="0.01" value="<?= \Src\Classes\Project\Settings::get("invoice.defaultWage") ?>">
            </div>
            <div class="mt-1 flex flex-col">
                <span>Beschreibung:</span>
                <textarea id="timeDescription" class="input-primary mt-1"></textarea>
            </div>
        </div>
        <div class="container col-span-2">
            <p>Erweiterte Zeiterfassung:</p>
            <div id="extendedTimeInput"></div>
            <button class="btn-primary" data-binding="true" data-fun="createTimeInputRow">Hinzufügen</button>
        </div>
    </div>
    <div class="tab-content hidden" id="service" role="tabpanel" data-tab-panel>
        <div class="flex flex-col">
            <span>Leistung:</span>
            <select class="input-primary w-48 mt-1" id="selectLeistung" data-binding="true" data-fun="selectLeistung">
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['Nummer'] ?>" data-surcharge="<?= $service['Aufschlag'] ?>"><?= $service['Bezeichnung'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mt-1 flex flex-col">
            <span>Menge [<span id="showMeh"></span>]:</span>
            <input class="input-primary mt-1" id="anz" type="number" step="any" value="1">
        </div>
        <div class="mt-1 flex flex-col">
            <span>Mengeneinheit:</span>
            <input list="units" name="units" id="meh" class="input-primary w-48 mt-1" placeholder="Auwählen oder eingeben" data-fun="changeMeh" data-write="true" type="text" maxlength="9">
            <datalist id="units">
                <option value="Stück">
                <option value="m²">
                <option value="Meter">
                <option value="Stunden">
                <option value="lfm">
            </datalist>
        </div>
        <div class="mt-1 flex flex-col">
            <span>Beschreibung:</span>
            <textarea id="bes" class="input-primary mt-1"></textarea>
        </div>
        <div class="mt-1 flex flex-col">
            <span>Einkaufspreis [€]:</span>
            <input class="input-primary mt-1" type="number" step="0.01" id="ekp" value="0">
        </div>
        <div class="mt-1 grid grid-cols-2 gap-4">
            <div class="flex flex-col">
                <span>Verkaufspreis [€]:</span>
                <input class="input-primary mt-1" type="number" step="0.01" id="pre" value="0">
            </div>
            <div class="flex flex-col">
                <span>Aufschlag [%]:</span>
                <div>
                    <input class="input-primary mt-1" type="number" id="surcharge" value="0" disabled>
                    <button class="btn-primary mt-2" data-binding="true" data-fun="calculatePrice">Übernehmen</button>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-content hidden" id="product" role="tabpanel" data-tab-panel>
        <span>Produkt suchen:</span>
        <div>
            <input type="search" id="productSearch">
            <span class="lupeSpan searchProductEvent"><span class="searchProductEvent">&#9906;</span></span>
            <p><i>Zubehör, Montagematerial, Textilien...</i></p>
        </div>
        <div id="resultContainer"></div>
        <span>Menge: <input class="postenInput" id="posten_produkt_menge" type="number"></span>
        <br>
        <a href="<?= \Src\Classes\Link::getPageLink("neues-produkt"); ?>">Neues Produkt hinzufügen</a>
    </div>
    <div class="tab-footer bg-gray-200 rounded-b-lg p-3">
        <div>
            <div class="ml-2">
                <?= \Src\Classes\Controller\TemplateController::getTemplate("inputSwitch", [
                    "id" => "isFree",
                    "name" => "Ohne Berechnung",
                ]); ?>
            </div>
            <div class="ml-2 mt-2">
                <?= \Src\Classes\Controller\TemplateController::getTemplate("inputSwitch", [
                    "id" => "addToInvoice",
                    "name" => "Der Rechnung hinzufügen",
                ]); ?>
            </div>
            <div class="ml-2 mt-2">
                <input type="number" min="0" max="100" value="0" class="input-primary w-16" id="getDiscount">
                <span>Rabatt [%]</span>
            </div>
            <button class="btn-primary mt-2" data-binding="true" data-fun="addItem" id="addItem">Hinzufügen</button>
            <button class="btn-primary mt-2 hidden" data-binding="true" data-fun="saveEdit" id="saveItem">Speichern</button>
            <button class="btn-cancel mt-2" data-binding="true" data-fun="showItemsMenu">Abbrechen</button>
        </div>
    </div>
</div>
<template id="templateTimeInput">
    <p class="timeInputWrapper my-2"><span>von</span>
        <input class="timeInput input-primary w-24" type="time" min="05:00" max="23:00">
        <span>bis</span>
        <input class="timeInput input-primary w-24" type="time" min="05:00" max="23:00">
        <span>am</span>
        <input class="dateInput input-primary w-32" type="date">
        <button type="button" class="btn-ghost btn-icon btn-sm text-danger" data-remove-time-row title="Zeitraum entfernen" aria-label="Zeitraum entfernen"><?= \Src\Classes\Project\Icon::get("x") ?></button>
    </p>
</template>