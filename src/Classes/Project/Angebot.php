<?php

namespace Src\Classes\Project;

use Src\Classes\Controller\TemplateController;
use Src\Classes\Pdf\TransactionPdf\OfferPDF;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Angebot
{
    private int $customerId = 0;
    private Kunde $customer;
    private int $offerId = 0;
    private OfferState $state = OfferState::Open;

    /** @var array<int, array<string>> */
    private array $fahrzeuge;

    public function __construct(int $offerId, int $customerId)
    {
        try {
            $this->customer = new Kunde($customerId);
        } catch (\Exception $e) {
            throw new \Exception("Kunde nicht gefunden");
        }

        $data = DBAccess::selectQuery("SELECT `state` FROM offer WHERE id = :offerId AND customer_id = :customerId;", [
            "offerId" => $offerId,
            "customerId" => $customerId,
        ]);

        if (empty($data)) {
            throw new \Exception("Angebot nicht gefunden");
        }

        $this->state = OfferState::tryFrom($data[0]["state"]) ?? OfferState::Open;
        $this->offerId = $offerId;
        $this->customerId = $customerId;
        $this->fahrzeuge = Fahrzeug::getSelection($customerId);
    }

    public static function createNewOffer(int $customerId): Angebot
    {
        $query = "INSERT INTO offer (customer_id, creation_date, `state`) VALUES (:customerId, NOW(), :state)";
        $idOffer = DBAccess::insertQuery($query, [
            "customerId" => $customerId,
            "state" => OfferState::Open->value,
        ]);

        return new Angebot($idOffer, $customerId);
    }

    public function getId(): int
    {
        return $this->offerId;
    }

    public function getState(): OfferState
    {
        return $this->state;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getOpenOffers(): array
    {
        $query = "SELECT o.id, o.creation_date, o.customer_id, CONCAT(k.Vorname, ' ', k.Nachname) AS name
            FROM offer o, kunde k
            WHERE o.`state` = :state
                AND k.Kundennummer = o.customer_id;";
        return DBAccess::selectQuery($query, [
            "state" => OfferState::Open->value,
        ]);
    }

    public static function deleteOffer(): void
    {
        $offerId = (int) Tools::get("offerId");

        $query = "DELETE FROM offer WHERE id = :offerId;";
        DBAccess::deleteQuery($query, [
            "offerId" => $offerId,
        ]);

        if (DBAccess::getAffectedRows() == 0) {
            JSONResponseHandler::throwError(404, "Angebot existiert nicht");
        }

        JSONResponseHandler::sendResponse([
            "success" => true,
        ]);
    }

    public static function getOfferTemplate(): void
    {
        $customerId = (int) Tools::get("customerId");
        if ($customerId == 0) {
            JSONResponseHandler::returnNotFound("No customer id given");
        }

        $offer = self::createNewOffer($customerId);
        self::sendOfferTemplate($offer);
    }

    public static function getExistingOfferTemplate(): void
    {
        $offerId = (int) Tools::get("offerId");

        $data = DBAccess::selectQuery("SELECT customer_id FROM offer WHERE id = :offerId;", [
            "offerId" => $offerId,
        ]);

        if (empty($data)) {
            JSONResponseHandler::returnNotFound("Angebot nicht gefunden");
        }

        $offer = new Angebot($offerId, (int) $data[0]["customer_id"]);
        self::sendOfferTemplate($offer);
    }

    private static function sendOfferTemplate(Angebot $offer): void
    {
        $services = DBAccess::selectQuery("SELECT Bezeichnung, Nummer, Aufschlag FROM leistung");
        $content = TemplateController::getTemplate("offer", [
            "offer" => $offer,
            "customer" => $offer->customer,
            "vehicles" => $offer->fahrzeuge,
            "customerId" => $offer->customerId,
            "services" => $services,
        ]);

        JSONResponseHandler::sendResponse([
            "content" => $content,
            "offerId" => $offer->getId(),
        ]);
    }

    public static function getOfferItems(): void
    {
        $offerId = (int) Tools::get("id");
        $items = Posten::getOfferItems($offerId);

        JSONResponseHandler::sendResponse(Posten::formatItemsForTable($items));
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
