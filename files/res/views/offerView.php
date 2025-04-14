<?php

use Classes\Link;
use Classes\Project\Kunde;

$kundenlink = $kundenlink = Link::getPageLink("kunde") . "?id=" . $customerId;
$customer = new Kunde($customerId);

?>
<div class="defCont">
    <div class="inlineC">
        <span><a href="<?= $kundenlink ?>"><b><?= $customer->getFirmenname() ?></b></a></span><br>
        <span><?= $customer->getVorname() ?> <?= $customer->getNachname() ?></span><br>
        <span><?= $customer->getStrasse() ?> <?= $customer->getHausnummer() ?></span><br>
        <span><?= $customer->getPostleitzahl() ?> <?= $customer->getOrt() ?></span><br>
    </div>
    <div class="inlineC">
        <span>Datum: <input id="angebotsdatum" type="date" value="<?= date('Y-m-d') ?>"></span><br>
        <span>Angebotsnummer: </span>
    </div>
</div>

<div class="defCont postenadd" id="newPosten">
    <select id="selectPosten" class="input-primary-new">
        <option value="zeit">Zeit</option>
        <option value="leistung">Leistung</option>
        <option value="produkt">Produkt</option>
    </select>
    <button onclick="getSelections()" class="btn-primary-new">Posten hinzufügen</button>
    <div id="addPosten">
        <div id="addPostenZeit" style="display: none">
            <span><input id="time" type="number" min="0">Zeit in Minuten</span><br>
            <span><input id="wage" type="number" value="44">Stundenlohn in €</span>
            <span><input id="descr" type="text">Beschreibung</span>
            <button onclick="addTime()">Hinzufügen</button>
        </div>
        <div id="addPostenLeistung" style="display: none">
            <div class="columnLeistung">
                <select id="selectLeistung" onchange="selectLeistung(event);">
                </select>
                <br>
                <span>Menge:<br><input class="postenInput" id="anz" value="1"></span><br>
                <span>Mengeneinheit:<br><input class="postenInput" id="meh"></span><br>
                <span>Beschreibung:<br><input id="bes"></span><br>
                <span>Einkaufspreis:<br><input id="ekp" value="0"></span><br>
                <span>Speziefischer Preis:<br><input id="pre" value="0"></span><br>
                <button onclick="addLeistung()">Hinzufügen</button>
            </div>
            <div class="columnLeistung" id="addKfz" style="display: none;">
                <span>Kfz-Kennzeichen:<br><input id="kfz"></span><br>
                <span>Fahrzeug:<br><input id="fahrzeug"></span><br>
                <button onclick="addFahrzeug()">Neues Fahrzeug hinzufügen</button>
                <hr>
                <select id="selectVehicle" onchange="selectVehicle(event);">
                    <option value="0" selected disabled>Bitte auswählen</option>
                </select>
                <button onclick="addFahrzeug(true)">Für diesen Auftrag übernehmen</button>
            </div>
        </div>
        <div id="addPostenProdukt" style="display: none">
        </div>
        <span id="showOhneBerechnung" style="display: none;"><input id="ohneBerechnung" type="checkbox">Ohne Berechnung</span>
    </div>
</div>

<div class="defCont" id="allePosten">
    <p>Alle Posten:</p>
</div>
<div class="defCont">
    <p>Text hinzufügen</p>
    <textarea>Hier Fließtext eingeben</textarea>
</div>
<button onclick="showOffer();" class="btn-primary-new">Angebot anzeigen</button>
<button onclick="storeOffer();" class="btn-primary-new">Angebot abschließen</button>
<br>
<iframe src="<?= Link::getPageLink('pdf') . "?type=angebot" ?>" id="showOffer"></iframe>