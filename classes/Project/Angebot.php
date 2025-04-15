<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

class Angebot
{

    private $customerId = 0;
    private $customer = null;
    private $offerId = 0;

    private $leistungen = null;
    private $fahrzeuge = null;

    private $posten = [];

    public function __construct(int $offerId, int $customerId)
    {
        try {
            $this->customer = new Kunde($customerId);
        } catch (\Exception $e) {
            throw new \Exception("Kunde nicht gefunden");
        }
        
        $this->offerId = $offerId;
        $this->customerId = $customerId;
        $this->leistungen = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");
        $this->fahrzeuge = Fahrzeug::getSelection($customerId);
    }

    public static function createNewOffer(int $customerId): Angebot
    {
        $query = "INSERT INTO angebot (id_customer, `status`, creation_date) VALUES (:idCustomer, 'open', NOW())";
        $idOffer = DBAccess::insertQuery($query, [
            "idCustomer" => $customerId,
        ]);

        return new Angebot($idOffer, $customerId);
    }

    public function PDFgenerieren($store = false)
    {
        $pdf = new \TCPDF('p', 'mm', 'A4');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetTitle('Angebot ' . $this->customer->getKundennummer());
        $pdf->SetSubject('Angebot');
        $pdf->SetKeywords('pdf, angebot');

        $pdf->AddPage();

        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->setCellMargins(0, 0, 0, 0);

        $cAddress = "<p>{$this->customer->getFirmenname()}<br>{$this->customer->getName()}<br>{$this->customer->getStrasse()} {$this->customer->getHausnummer()}<br>{$this->customer->getPostleitzahl()} {$this->customer->getOrt()}</p>";
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
        $this->loadPosten();
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
            $filename = "{$this->customer->getKundennummer()}_{$this->offerId}.pdf";
            $filelocation = "C:\\xampp\htdocs\\auftragsbearbeitung\\files\\generated\\offer";
            $fileNL = $filelocation . "\\" . $filename;
            $pdf->Output($fileNL, 'F');
        } else {
            $pdf->Output();
        }
    }

    private function getPc()
    {
        if (isset($_SESSION['offer_' . $this->customerId . '_pc'])) {
            return (int) $_SESSION['offer_' . $this->customerId . '_pc'];
        } else {
            $_SESSION['offer_' . $this->customerId . '_pc'] = 0;
            return 0;
        }
    }

    private function incPc()
    {
        $newPc = $this->getPc() + 1;
        $_SESSION['offer_' . $this->customerId . '_pc'] = $newPc;
        return $newPc;
    }

    private function decPc()
    {
        $newPc = $this->getPc() - 1;
        if ($newPc >= 0) {
            $_SESSION['offer_' . $this->customerId . '_pc'] = $newPc;
        }
        return $newPc;
    }

    public function getId()
    {
        return $this->offerId;
    }

    private function loadPosten()
    {
        $num = $this->getPc();
        if (is_numeric($num)) {
            for ($i = 1; $i <= $num; $i++) {
                if (isset($_SESSION['offer_' . $this->customerId . '_' . $i])) {
                    $posten = unserialize($_SESSION['offer_' . $this->customerId . '_' . $i]);
                    array_push($this->posten, $posten);
                }
            }
        }
    }

    private function deleteOldSessionData()
    {
        $num = $this->getPc();
        for ($i = 1; $i <= $num; $i++) {
            if (isset($_SESSION['offer_' . $this->customerId . '_' . $i])) {
                $_SESSION['offer_' . $this->customerId . '_' . $i] = null;
            }
        }
        $_SESSION['offer_' . $this->customerId . '_pc'] = null;
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
        $_SESSION['offer_' . $this->customerId . '_' . $postenId] = serialize($posten);

        echo $postenId;
        array_push($this->posten, $posten);
    }

    /* function is called from createOrder page only if offer session data is available */
    public function storeOffer($orderId)
    {
        $this->offerId = DBAccess::insertQuery("INSERT INTO angebot (kdnr, `status`) VALUES ({$this->customerId}, 0)");
        $this->loadPosten();
        if ($this->posten != null) {
            foreach ($this->posten as $p) {
                $p->storeToDB($orderId);
            }
        }

        $this->deleteOldSessionData();
        $this->PDFgenerieren(true);
    }

    public function loadAngebot() {}

    public static function getOfferTemplate()
    {
        $customerId = (int) Tools::get("customerId");
        if ($customerId == 0) {
            JSONResponseHandler::returnNotFound([
                "error" => "No customer id given",
            ]);
            return;
        }

        $offer = self::createNewOffer($customerId);
        $services = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");
        $content = TemplateController::getTemplate("offer", [
            "offer" => $offer,
            "customer" => $offer->customer,
            "vehicles" => $offer->fahrzeuge,
            "customerId" => $customerId,
            "services" => $services,
        ]);

        JSONResponseHandler::sendResponse([
            "content" => $content,
            "offerId" => $offer->getId(),
        ]);
    }

    public static function getOfferItems()
    {
        //
    }

}
