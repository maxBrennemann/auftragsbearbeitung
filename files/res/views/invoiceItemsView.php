<div id="auftragsPostenTable" class="mt-2"></div>
<div class="mt-2">
    <button class="btn-primary-new" data-binding="true" data-fun="showPostenAdd">Hinzufügen</button>
</div>
<div id="showPostenAdd" class="hidden mt-2 bg-white rounded-lg">
    <div class="flex rounded-t-lg">
        <button class="tab-button tablinks tab-active" data-target="tabZeit">Zeiterfassung</button>
        <button class="tab-button tablinks" data-target="tabLeistung">Kostenerfassung</button>
        <button class="tab-button tablinks" data-target="tabProdukte" disabled>Produkte</button>
    </div>
    <div class="tab-content" id="tabZeit">
        <div id="addPostenZeit" class="grid grid-cols-2 gap-4">
            <div class="container">
                <div class="flex flex-col">
                    <span>Zeit in Minuten:</span>
                    <input class="input-primary-new" id="time" type="number" min="0">
                </div>
                <div class="mt-1 flex flex-col">
                    <span>Stundenlohn [€]:</span>
                    <input class="input-primary-new" id="wage" type="number" value="<?= $auftrag->getDefaultWage() ?>">
                </div>
                <div class="mt-1 flex flex-col">
                    <span>Beschreibung:</span>
                    <textarea id="descr" class="input-primary-new" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea>
                </div>
            </div>
            <div class="container">
                <span>Erweiterte Zeiterfassung:</span>
                <br>
                <span>Arbeitszeit(en)</span>
                <div id="extendedTimeInput"></div>
                <button class="btn-primary-new" data-binding="true" data-fun="createTimeInputRow">Hinzufügen</button>
                <p id="showTimeSummary"></p>
            </div>
        </div>
    </div>
    <div class="tab-content hidden" id="tabLeistung">
        <div id="addPostenLeistung">
            <div class="flex flex-col">
                <span>Leistung:</span>
                <select class="input-primary-new w-48" id="selectLeistung" data-write="true" data-fun="selectLeistung">
                    <?php foreach ($leistungen as $leistung): ?>
                        <option value="<?= $leistung['Nummer'] ?>" data-aufschlag="<?= $leistung['Aufschlag'] ?>"><?= $leistung['Bezeichnung'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mt-1 flex flex-col">
                <span>Menge:</span>
                <input class="input-primary-new" id="anz" type="number" value="1">
            </div>
            <div class="mt-1 flex flex-col">
                <span>Mengeneinheit:</span>
                <input list="units" name="units" id="meh" class="input-primary-new w-48" placeholder="Auwählen oder eingeben" data-binding="true" data-fun="mehListener">
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
                <span>Einkaufspreis:</span>
                <input class="input-primary-new" type="number" id="ekp" value="0">
            </div>
            <div class="mt-1 flex flex-col">
                <span>Verkaufspreis:</span>
                <input class="input-primary-new" type="number" id="pre" value="0">
            </div>
        </div>
    </div>
    <div class="tab-content hidden" id="tabProdukte">
        <div id="addPostenProdukt">
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
    </div>
    <div class="tab-footer bg-gray-200 rounded-b-lg p-3">
        <div>
            <div id="showOhneBerechnung" class="ml-2">
                <input id="ohneBerechnung" type="checkbox">
                <span class="ml-2">Ohne Berechnung</span>
            </div>
            <div id="showAddToInvoice" class="ml-2">
                <input id="addToInvoice" type="checkbox">
                <span class="ml-2">Der Rechnung hinzufügen</span>
            </div>
            <div class="ml-2 mt-2">
                <input type="number" min="0" max="100" value="0" class="input-primary-new w-16" id="getDiscount">
                <span>Rabatt [%]</span>
            </div>
            <button id="addTimeButton" data-binding="true" data-fun="addTime" class="btn-primary-new mt-2">Hinzufügen</button>
            <button data-binding="true" data-fun="addProductCompact" class="btn-primary">Hinzufügen</button>
            <button data-binding="true" data-fun="addLeistung" id="addLeistungButton" class="btn-primary">Hinzufügen</button>
        </div>
    </div>
</div>