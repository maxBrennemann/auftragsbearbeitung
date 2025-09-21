<?php

namespace Classes\Project;

use Classes\Controller\TemplateController;
use Classes\Pdf\TransactionPdf\OfferPDF;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Angebot
{
    private int $customerId = 0;
    private Kunde $customer;
    private int $offerId = 0;

    //private $leistungen = null;

    /** @var array<string, string> */
    private array $fahrzeuge;

    /** @var Zeit|Leistung|ProduktPosten[] */
    private array $posten = [];

    public function __construct(int $offerId, int $customerId)
    {
        try {
            $this->customer = new Kunde($customerId);
        } catch (\Exception $e) {
            throw new \Exception("Kunde nicht gefunden");
        }

        $this->offerId = $offerId;
        $this->customerId = $customerId;
        //$this->leistungen = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");
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

    private function getPc(): int
    {
        if (isset($_SESSION['offer_' . $this->customerId . '_pc'])) {
            return (int) $_SESSION['offer_' . $this->customerId . '_pc'];
        } else {
            $_SESSION['offer_' . $this->customerId . '_pc'] = 0;
            return 0;
        }
    }

    private function incPc(): int
    {
        $newPc = $this->getPc() + 1;
        $_SESSION['offer_' . $this->customerId . '_pc'] = $newPc;
        return $newPc;
    }

    /*     private function decPc()
    {
        $newPc = $this->getPc() - 1;
        if ($newPc >= 0) {
            $_SESSION['offer_' . $this->customerId . '_pc'] = $newPc;
        }
        return $newPc;
    } */

    public function getId(): int
    {
        return $this->offerId;
    }

    private function loadPosten(): void
    {
        $num = $this->getPc();
        for ($i = 1; $i <= $num; $i++) {
            if (isset($_SESSION['offer_' . $this->customerId . '_' . $i])) {
                $posten = unserialize($_SESSION['offer_' . $this->customerId . '_' . $i]);
                array_push($this->posten, $posten);
            }
        }
    }

    private function deleteOldSessionData(): void
    {
        $num = $this->getPc();
        for ($i = 1; $i <= $num; $i++) {
            if (isset($_SESSION['offer_' . $this->customerId . '_' . $i])) {
                $_SESSION['offer_' . $this->customerId . '_' . $i] = null;
            }
        }
        $_SESSION['offer_' . $this->customerId . '_pc'] = null;
    }

    /* private function postenSum()
    {
        $sum = 0;
        foreach ($this->posten as $p) {
            $sum += $p->bekommePreis();
        }
        return $sum;
    } */

    /**
     * @param array<string, string> $posten
     * @return void
     */
    public function addPosten(array $posten): void
    {
        $postenId = $this->incPc();
        $_SESSION['offer_' . $this->customerId . '_' . $postenId] = serialize($posten);

        echo $postenId;
        array_push($this->posten, $posten);
    }

    /**
     * function is called from createOrder page only if offer session data is available
     */
    public function storeOffer(int $orderId): void
    {
        $this->offerId = DBAccess::insertQuery("INSERT INTO angebot (kdnr, `status`) VALUES ({$this->customerId}, 0)");
        $this->loadPosten();
        if ($this->posten != null) {
            foreach ($this->posten as $p) {
                $p->storeToDB($orderId);
            }
        }

        $this->deleteOldSessionData();
    }

    public function loadAngebot(): void {}

    public static function getOfferTemplate(): void
    {
        $customerId = (int) Tools::get("customerId");
        if ($customerId == 0) {
            JSONResponseHandler::returnNotFound([
                "error" => "No customer id given",
            ]);
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

    public static function getOfferItems(): void
    {
        JSONResponseHandler::sendResponse([]);
    }

    public static function getPDF(): void
    {
        $offerId = (int) Tools::get("offerId");
        $customerId = (int) Tools::get("customerId");
        $offerPDF = new OfferPDF($offerId, $customerId);
        $offerPDF->generate();
        $offerPDF->generateOutput();
    }
}
