<?php

require_once('.res/tcpdf/tcpdf.php');
require_once('Kunde.php');
require_once('classes/DBAccess.php');
require_once('classes/project/Fahrzeug.php');

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('Auftrag.php');

class Angebot {
    
    private $kdnr = 0;
    private $kunde = null;
    private $angebotsnr = 0;

    private $leistungen = null;
	private $fahrzeuge = null;

    function __construct($cid) {
        $this->kdnr = $cid;
        $this->kunde = new Kunde($cid);
        $this->leistungen = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");
        $this->fahrzeuge = Fahrzeug::getSelection($cid);
    }

    public function PDFgenerieren() {
        $pdf = new TCPDF('p', 'mm', 'A4');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetTitle('Angebot ' . $this->kunde->getKundennummer());
        $pdf->SetSubject('Angebot');
        $pdf->SetKeywords('pdf, angebot');

        $pdf->AddPage();

        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->setCellMargins(0, 0, 0, 0);

        $cAdress = "<p>{$this->kunde->getFirmenname()}<br>{$this->kunde->getName()}<br>{$this->kunde->getStrasse()} {$this->kunde->getHausnummer()}<br>{$this->kunde->getPostleitzahl()} {$this->kunde->getOrt()}</p>";
        $adress = "<p>b-schriftung Brennemann Dietmar<br>Huberweg 31<br>94522 Wallersdorf</p>";

        $pdf->writeHTMLCell(85, 40, 20, 45, $cAdress);
        $pdf->writeHTMLCell(85, 40, 120, 35, $adress);

        $pdf->setXY(20, 90);
        $pdf->Cell(20, 10, 'Menge', 'B');
        $pdf->Cell(20, 10, 'MEH', 'B');
        $pdf->Cell(80, 10, 'Bezeichnung', 'B');
        $pdf->Cell(20, 10, 'E-Preis', 'B');
        $pdf->Cell(20, 10, 'G-Preis', 'B');

        $pdf->Output();
    }

    public function getHTMLTemplate() {
        if (true) : ?>
            <div class="defCont">
                <div class="inlineC">
                    <span><b><?=$this->kunde->getFirmenname()?></b></span><br>
                    <span><?=$this->kunde->getVorname()?> <?=$this->kunde->getNachname()?></span><br>
                    <span><?=$this->kunde->getStrasse()?> <?=$this->kunde->getHausnummer()?></span><br>
                    <span><?=$this->kunde->getPostleitzahl()?> <?=$this->kunde->getOrt()?></span><br>
                </div>
                <div class="inlineC">
                    <span>Datum: <input id="angebotsdatum" type="date" value="<?=date('Y-m-d')?>"></span><br>
                    <span>Angebotsnummer: <?=$this->angebotsnr?></span>
                </div>
            </div>

            <div class="defCont postenadd" id="newPosten">
                <select id="selectPosten">
                    <option value="zeit">Zeit</option>
                    <option value="leistung">Leistung</option>
                    <option value="produkt">Produkt</option>
                </select>
                <button onclick="getSelections()">Posten hinzufügen</button>
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
                                <?php foreach ($this->leistungen as $leistung): ?>
                                    <option value="<?=$leistung['Nummer']?>" data-aufschlag="<?=$leistung['Aufschlag']?>"><?=$leistung['Bezeichnung']?></option>
                                <?php endforeach; ?>
                            </select>
                            <br>
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
                                <?php foreach ($this->fahrzeuge as $f): ?>
                                    <option value="<?=$f['Nummer']?>"><?=$f['Kennzeichen']?> <?=$f['Fahrzeug']?></option>
                                <?php endforeach; ?>
                            </select>
                            <button onclick="addFahrzeug(true)">Für diesen Auftrag übernehmen</button>
                        </div>
                    </div>
                    <span id="showOhneBerechnung" style="display: none;"><input id="ohneBerechnung" type="checkbox">Ohne Berechnung</span>
                </div>
            </div>
            <div class="defCont" id="allePosten">
                <p>Alle Posten:</p>
            </div>
        <?php endif;
        
    }

}

?>