<div id="auftragsPostenTable" class="mt-2"></div>
<div class="mt-2">
    <button class="btn-primary-new" data-binding="true" data-fun="showPostenAdd">Hinzufügen</button>
</div>
<div id="showPostenAdd" style="display: none;">
    <div class="tabcontainer">
        <button class="tablinks activetab" onclick="openTab(event, 0)">Zeiterfassung</button>
        <button class="tablinks" onclick="openTab(event, 1)">Kostenerfassung</button>
        <button class="tablinks" onclick="openTab(event, 2)">Produkte</button>
    </div>
    <div class="tabcontent" id="tabZeit" style="display: block;">
        <div id="addPostenZeit">
            <div class="container">
                <span>Zeit in Minuten<br><input class="postenInput" id="time" type="number" min="0"></span>
                <br>
                <span>Stundenlohn in €<br><input class="postenInput" id="wage" type="number" value="<?= $auftrag->getDefaultWage() ?>"></span>
                <br>
                <span>Beschreibung<br>
                    <textarea id="descr" class="border-2 rounded-md p-2" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea>
                </span>
                <br>
                <button id="addTimeButton" data-binding="true" data-fun="addTime" class="btn-primary">Hinzufügen</button>
            </div>
            <div class="container">
                <span>Erweiterte Zeiterfassung:</span>
                <br>
                <span>Arbeitszeit(en)</span>
                <div id="extendedTimeInput">
                </div>
                <button class="btn-primary-new" data-binding="true" data-fun="createTimeInputRow">Hinzufügen</button>
                <p id="showTimeSummary"></p>
            </div>
        </div>
    </div>
    <div class="tabcontent" id="tabLeistung">
        <div id="addPostenLeistung">
            <select id="selectLeistung" data-write="true" data-fun="selectLeistung">
                <?php foreach ($leistungen as $leistung): ?>
                    <option value="<?= $leistung['Nummer'] ?>" data-aufschlag="<?= $leistung['Aufschlag'] ?>"><?= $leistung['Bezeichnung'] ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <span>Menge:<br><input class="postenInput" id="anz" type="number" value="1"></span><br>
            <span>Mengeneinheit:<br>
                <input class="postenInput" id="meh" data-binding="true" data-fun="mehListener">
                <span id="meh_dropdown" data-binding="true" data-fun="mehListener">▼</span>
                <div class="selectReplacer" id="selectReplacerMEH">
                    <p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Stück</p>
                    <p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">m²</p>
                    <p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Meter</p>
                    <p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">Stunden</p>
                    <p class="optionReplacer" onclick="document.getElementById('meh').value = this.innerHTML;">lfm</p>
                </div>
            </span><br>
            <span>Beschreibung:
                <br>
                <textarea id="bes" oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'" class="border-2 rounded-md p-2"></textarea>
            </span>
            <br>
            <span>Einkaufspreis:<br><input class="postenInput" type="number" id="ekp" value="0"></span><br>
            <span>Verkaufspreis:<br><input class="postenInput" type="number" id="pre" value="0"></span><br>
            <button data-binding="true" data-fun="addLeistung" id="addLeistungButton" class="btn-primary">Hinzufügen</button>
        </div>
    </div>
    <div class="tabcontent" id="tabProdukte">
        <div id="addPostenProdukt">
            <span>Produkt suchen:</span>
            <div>
                <input type="search" id="productSearch">
                <span class="lupeSpan searchProductEvent"><span class="lupe searchProductEvent">&#9906;</span></span>
                <p><i>Zubehör, Montagematerial, Textilien...</i></p>
            </div>
            <div id="resultContainer"></div>
            <span>Menge: <input class="postenInput" id="posten_produkt_menge" type="number"></span>
            <button data-binding="true" data-fun="addProductCompact" class="btn-primary">Hinzufügen</button>
            <br>
            <a href="<?= \Classes\Link::getPageLink("neues-produkt"); ?>">Neues Produkt hinzufügen</a>
        </div>
    </div>
    <div class="tabcontentEnd">
        <div>
            <span id="showOhneBerechnung" class="ml-2">
                <input id="ohneBerechnung" type="checkbox">
                <span class="ml-2">Ohne Berechnung</span>
            </span>
            <br>
            <span id="showAddToInvoice" class="ml-2">
                <input id="addToInvoice" type="checkbox">
                <span class="ml-2">Der Rechnung hinzufügen</span>
            </span>
            <br>
            <span id="showDiscount" class="ml-2 mt-2">
                <input type="range" min="0" max="100" value="0" name="discountInput" id="discountInput" oninput="showDiscountValue.value = discountInput.value + '%'">
                <output id="showDiscountValue" name="showDiscountValue" for="discountInput">0%</output> Rabatt
            </span>
            <div id="generalPosten"></div>
        </div>
    </div>
</div>