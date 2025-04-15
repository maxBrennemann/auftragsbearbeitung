<div id="auftragsPostenTable" class="mt-2"></div>
<div class="mt-2">
    <button class="btn-primary-new" data-binding="true" data-fun="showItemsMenu" id="showItemsMenu">Hinzufügen</button>
</div>
<div id="showPostenAdd" class="hidden mt-2 bg-white rounded-lg">
    <div class="flex rounded-t-lg">
        <button class="tab-button tablinks tab-active" data-target="time">Zeiterfassung</button>
        <button class="tab-button tablinks" data-target="service">Kostenerfassung</button>
        <button class="tab-button tablinks" data-target="product" disabled>Produkte</button>
    </div>
    <div class="tab-content" id="time" class="grid grid-cols-3 gap-4">
            <div class="container">
                <div class="flex flex-col">
                    <span>Zeit in Minuten:</span>
                    <input class="input-primary-new" id="time" type="number" min="0">
                </div>
                <div class="mt-1 flex flex-col">
                    <span>Stundenlohn [€]:</span>
                    <input class="input-primary-new" id="wage" type="number" value="<?= \Classes\Project\Config::get("defaultWage") ?>">
                </div>
                <div class="mt-1 flex flex-col">
                    <span>Beschreibung:</span>
                    <textarea id="timeDescription" class="input-primary-new" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea>
                </div>
            </div>
            <div class="container col-span-2">
                <p>Erweiterte Zeiterfassung:</p>
                <div id="extendedTimeInput"></div>
                <button class="btn-primary-new" data-binding="true" data-fun="createTimeInputRow">Hinzufügen</button>
            </div>
    </div>
    <div class="tab-content hidden" id="service">
            <div class="flex flex-col">
                <span>Leistung:</span>
                <select class="input-primary-new w-48" id="selectLeistung" data-binding="true" data-fun="selectLeistung">
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['Nummer'] ?>" data-surcharge="<?= $service['Aufschlag'] ?>"><?= $service['Bezeichnung'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mt-1 flex flex-col">
                <span>Menge:</span>
                <input class="input-primary-new" id="anz" type="number" value="1">
            </div>
            <div class="mt-1 flex flex-col">
                <span>Mengeneinheit:</span>
                <input list="units" name="units" id="meh" class="input-primary-new w-48" placeholder="Auwählen oder eingeben">
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
                <textarea id="bes" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'" class="input-primary-new"></textarea>
            </div>
            <div class="mt-1 flex flex-col">
                <span>Einkaufspreis [€]:</span>
                <input class="input-primary-new" type="number" id="ekp" value="0">
            </div>
            <div class="mt-1 grid grid-cols-2 gap-4">
                <div class="flex flex-col">
                    <span>Verkaufspreis [€]:</span>
                    <input class="input-primary-new" type="number" id="pre" value="0">
                </div>
                <div>
                    <span>Aufschlag [%]:</span>
                    <input class="input-primary-new" type="number" id="surcharge" value="0" disabled>
                    <button class="btn-primary-new mt-2" data-binding="true" data-fun="calculatePrice">Übernehmen</button>
                </div>
            </div>
    </div>
    <div class="tab-content hidden" id="product">
            <span>Produkt suchen:</span>
            <div>
                <input type="search" id="productSearch">
                <span class="lupeSpan searchProductEvent"><span class="lupe searchProductEvent">&#9906;</span></span>
                <p><i>Zubehör, Montagematerial, Textilien...</i></p>
            </div>
            <div id="resultContainer"></div>
            <span>Menge: <input class="postenInput" id="posten_produkt_menge" type="number"></span>
            <br>
            <a href="<?= \Classes\Link::getPageLink("neues-produkt"); ?>">Neues Produkt hinzufügen</a>
    </div>
    <div class="tab-footer bg-gray-200 rounded-b-lg p-3">
        <div>
            <div class="ml-2">
                <?= \Classes\Project\TemplateController::getTemplate("inputSwitch", [
                    "id" => "isFree",
                    "name" => "Ohne Berechnung",
                ]); ?>
            </div>
            <div class="ml-2 mt-2">
                <?= \Classes\Project\TemplateController::getTemplate("inputSwitch", [
                    "id" => "addToInvoice",
                    "name" => "Der Rechnung hinzufügen",
                ]); ?>
            </div>
            <div class="ml-2 mt-2">
                <input type="number" min="0" max="100" value="0" class="input-primary-new w-16" id="getDiscount">
                <span>Rabatt [%]</span>
            </div>
            <button class="btn-primary-new mt-2" data-binding="true" data-fun="addItem">Hinzufügen</button>
            <button class="btn-cancel mt-2" data-binding="true" data-fun="showItemsMenu">Abbrechen</button>
        </div>
    </div>
</div>
<template id="templateTimeInput">
    <p class="timeInputWrapper my-2"><span>von</span>
        <input class="timeInput input-primary-new w-24" type="time" min="05:00" max="23:00">
        <span>bis</span>
        <input class="timeInput input-primary-new w-24" type="time" min="05:00" max="23:00">
        <span>am</span>
        <input class="dateInput input-primary-new w-32" type="date">
        <button class="btn-delete">X</button>
    </p>
</template>