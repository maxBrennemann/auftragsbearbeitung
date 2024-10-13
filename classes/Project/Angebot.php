<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use Classes\Link;

/*
* Status bei Angeboten:
* 0 -- offen
* 1 -- übernommen
* 2 -- gelöscht
*/

/*
 * session variable structure:
 * offer_id is the number of the current offer
 * offer_x_pc is the pattern for the posten counter for offer x
 * offer_x_y is the pattern for a specific posten for offer x 
 * offer_is_order is the boolean for whether an offer is created or not
 * offer_order is the order id
*/

class Angebot
{

    private $kdnr = 0;
    private $kunde = null;
    private $angebotsnr = 0;

    private $leistungen = null;
    private $fahrzeuge = null;

    private $posten = array();

    function __construct($cid = null)
    {
        if (isset($_SESSION['offer_id'])) {
            $offerId = $_SESSION['offer_id'];
        } else {
            $offerId = -1;
        }

        if ($cid == null && $offerId == -1) {
            throw new \Exception("cannot fetch any data");
        } else if ($cid == null) {
            $cid = $offerId;
        } else if ($cid != $offerId) {
            $this->deleteOldSessionData();
            $_SESSION['offer_id'] = $cid;
        }

        $this->kdnr = $cid;
        $this->kunde = new Kunde($cid);
        $this->leistungen = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");
        $this->fahrzeuge = Fahrzeug::getSelection($cid);
    }

    public function PDFgenerieren($store = false)
    {
        $pdf = new \TCPDF('p', 'mm', 'A4');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetTitle('Angebot ' . $this->kunde->getKundennummer());
        $pdf->SetSubject('Angebot');
        $pdf->SetKeywords('pdf, angebot');

        $pdf->AddPage();

        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->setCellMargins(0, 0, 0, 0);

        $cAddress = "<p>{$this->kunde->getFirmenname()}<br>{$this->kunde->getName()}<br>{$this->kunde->getStrasse()} {$this->kunde->getHausnummer()}<br>{$this->kunde->getPostleitzahl()} {$this->kunde->getOrt()}</p>";
        $address = "<p>" . $_ENV["COMPANY_NAME"] . "<br>" . $_ENV["COMPANY_STREET"] . "<br>" . $_ENV["COMPANY_CITY"] . "</p>";

        $pdf->writeHTMLCell(85, 40, 20, 45, $cAddress);
        $pdf->writeHTMLCell(85, 40, 120, 35, $address);

        $pdf->setXY(20, 90);
        $pdf->Cell(20, 10, 'Menge', 'B');
        $pdf->Cell(20, 10, 'MEH', 'B');
        $pdf->Cell(80, 10, 'Bezeichnung', 'B');
        $pdf->Cell(20, 10, 'E-Preis', 'B');
        $pdf->Cell(20, 10, 'G-Preis', 'B');

        /* iterates over all posten and adds lines */
        $this->loadPostenFromSession();
        $offset = 10;
        if ($this->posten != null) {
            foreach ($this->posten as $p) {
                $pdf->setXY(20, 90 + $offset);
                $pdf->Cell(20, 10, $p->getQuantity());
                $pdf->Cell(20, 10, $p->getEinheit());
                $pdf->Cell(80, 10, $p->getDescription());
                $pdf->Cell(20, 10, $p->bekommeEinzelPreis_formatted());
                $pdf->Cell(20, 10, $p->bekommePreis_formatted());
                $offset += 10;
            }
        }

        /* generates a pdf when offer is converted to an order */
        if ($store == true) {
            $filename = "{$this->kunde->getKundennummer()}_{$this->angebotsnr}.pdf";
            $filelocation = "C:\\xampp\htdocs\\auftragsbearbeitung\\files\\generated\\offer";
            $fileNL = $filelocation . "\\" . $filename;
            $pdf->Output($fileNL, 'F');
        } else {
            $pdf->Output();
        }
    }

    private function getPc()
    {
        if (isset($_SESSION['offer_' . $this->kdnr . '_pc'])) {
            return (int) $_SESSION['offer_' . $this->kdnr . '_pc'];
        } else {
            $_SESSION['offer_' . $this->kdnr . '_pc'] = 0;
            return 0;
        }
    }

    private function incPc()
    {
        $newPc = $this->getPc() + 1;
        $_SESSION['offer_' . $this->kdnr . '_pc'] = $newPc;
        return $newPc;
    }

    private function decPc()
    {
        $newPc = $this->getPc() - 1;
        if ($newPc >= 0) {
            $_SESSION['offer_' . $this->kdnr . '_pc'] = $newPc;
        }
        return $newPc;
    }

    private function loadPostenFromSession()
    {
        $num = $this->getPc();
        if (is_numeric($num)) {
            for ($i = 1; $i <= $num; $i++) {
                if (isset($_SESSION['offer_' . $this->kdnr . '_' . $i])) {
                    $posten = unserialize($_SESSION['offer_' . $this->kdnr . '_' . $i]);
                    array_push($this->posten, $posten);
                }
            }
        }
    }

    private function deleteOldSessionData()
    {
        $num = $this->getPc();
        for ($i = 1; $i <= $num; $i++) {
            if (isset($_SESSION['offer_' . $this->kdnr . '_' . $i])) {
                $_SESSION['offer_' . $this->kdnr . '_' . $i] = null;
            }
        }
        $_SESSION['offer_' . $this->kdnr . '_pc'] = null;
    }

    private function postenSum()
    {
        $sum = 0;
        foreach ($this->posten as $p) {
            $sum += $p->bekommePreis();
        }
        return $sum;
    }

    public function addPosten($posten)
    {
        $postenId = $this->incPc();
        $_SESSION['offer_' . $this->kdnr . '_' . $postenId] = serialize($posten);

        echo $postenId;
        array_push($this->posten, $posten);
    }

    static function setIsOrder()
    {
        $_SESSION['offer_is_order'] = true;
    }

    /* function is called from createOrder page only if offer session data is available */
    public function storeOffer($orderId)
    {
        $this->angebotsnr = DBAccess::insertQuery("INSERT INTO angebot (kdnr, `status`) VALUES ({$this->kdnr}, 0)");
        $this->loadPostenFromSession();
        if ($this->posten != null) {
            foreach ($this->posten as $p) {
                $p->storeToDB($orderId);
            }
        }

        $this->deleteOldSessionData();
        $this->PDFgenerieren(true);
    }

    public function loadCachedPosten()
    {
        $this->loadPostenFromSession();
        if ($this->posten != null) {
            foreach ($this->posten as $p) {
                if ($p instanceof Zeit) {
                    echo "Zeit: {$p->getQuantity()} min, Stundenlohn: {$p->getWage()}€ für {$p->getDescription()}";
                    if ($p->getOhneBerechnung()) {
                        echo ", wird nicht berechnet";
                    }
                } else if ($p instanceof Leistung) {
                    echo "Leistung: {$p->getQuantity()}, Preis {$p->bekommeEinzelPreis()}€ EK Preis {$p->bekommeEKPreis()} für {$p->getDescription()}";
                    if ($p->getOhneBerechnung()) {
                        echo ", wird nicht berechnet";
                    }
                }
                echo "<br>";
            }
        }
    }

    public function loadAngebot() {}

    public function getHTMLTemplate()
    {
        $kundenlink = Link::getPageLink("kunde") . "?id=" . $this->kunde->getKundennummer();
        if (true) : ?>
            <div class="defCont">
                <div class="inlineC">
                    <span><a href="<?= $kundenlink ?>"><b><?= $this->kunde->getFirmenname() ?></b></a></span><br>
                    <span><?= $this->kunde->getVorname() ?> <?= $this->kunde->getNachname() ?></span><br>
                    <span><?= $this->kunde->getStrasse() ?> <?= $this->kunde->getHausnummer() ?></span><br>
                    <span><?= $this->kunde->getPostleitzahl() ?> <?= $this->kunde->getOrt() ?></span><br>
                </div>
                <div class="inlineC">
                    <span>Datum: <input id="angebotsdatum" type="date" value="<?= date('Y-m-d') ?>"></span><br>
                    <span>Angebotsnummer: <?= $this->angebotsnr ?></span>
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
                                    <option value="<?= $leistung['Nummer'] ?>" data-aufschlag="<?= $leistung['Aufschlag'] ?>"><?= $leistung['Bezeichnung'] ?></option>
                                <?php endforeach; ?>
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
                                <?php foreach ($this->fahrzeuge as $f): ?>
                                    <option value="<?= $f['Nummer'] ?>"><?= $f['Kennzeichen'] ?> <?= $f['Fahrzeug'] ?></option>
                                <?php endforeach; ?>
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
            <button onclick="showOffer();">Angebot anzeigen</button>
            <button onclick="storeOffer();">Angebot abschließen</button>
            <br>
            <iframe src="<?= Link::getPageLink('pdf') . "?type=angebot" ?>" id="showOffer"></iframe>
<?php endif;
    }
}
