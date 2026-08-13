<?php

namespace Src\Classes\Project;

use Src\Classes\Controller\SendOfferController;
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
    private int $offerNumber = 0;
    private ?string $validUntil = null;

    /** @var array<int, array<string>> */
    private array $fahrzeuge;

    public function __construct(int $offerId, int $customerId)
    {
        try {
            $this->customer = new Kunde($customerId);
        } catch (\Exception $e) {
            throw new \Exception("Kunde nicht gefunden");
        }

        $data = DBAccess::selectQuery("SELECT `state`, offer_number, valid_until FROM offer WHERE id = :offerId AND customer_id = :customerId;", [
            "offerId" => $offerId,
            "customerId" => $customerId,
        ]);

        if (empty($data)) {
            throw new \Exception("Angebot nicht gefunden");
        }

        $this->state = OfferState::tryFrom($data[0]["state"]) ?? OfferState::Open;
        $this->offerNumber = (int) $data[0]["offer_number"];
        $this->validUntil = $data[0]["valid_until"];
        $this->offerId = $offerId;
        $this->customerId = $customerId;
        $this->fahrzeuge = Fahrzeug::getSelection($customerId);
    }

    public static function createNewOffer(int $customerId): Angebot
    {
        $validityDays = Settings::get('offer.validityDays');
        $days = is_numeric($validityDays) ? (int) $validityDays : 30;

        $query = "INSERT INTO offer (customer_id, creation_date, `state`, valid_until)
            VALUES (:customerId, NOW(), :state, DATE_ADD(NOW(), INTERVAL :days DAY))";
        $idOffer = DBAccess::insertQuery($query, [
            "customerId" => $customerId,
            "state" => OfferState::Open->value,
            "days" => $days,
        ]);

        OrderHistory::add($idOffer, $idOffer, OrderHistory::TYPE_OFFER, OrderHistory::STATE_ADDED, "Angebot erstellt");

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

    public function getOfferNumber(): int
    {
        return $this->offerNumber;
    }

    public function getValidUntil(): ?string
    {
        return $this->validUntil;
    }

    public function getCustomer(): Kunde
    {
        return $this->customer;
    }

    public function getCustomerEmail(): false|string
    {
        $email = $this->customer->getEmail();
        if ($email == "") {
            return false;
        }
        return $email;
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

    public static function completeOffer(): void
    {
        $offerId = (int) Tools::get("offerId");
        $data = DBAccess::selectQuery("SELECT offer_number, `state` FROM offer WHERE id = :offerId;", [
            "offerId" => $offerId,
        ]);

        if (empty($data)) {
            JSONResponseHandler::throwError(404, "Angebot existiert nicht");
        }

        $state = OfferState::tryFrom($data[0]["state"]) ?? OfferState::Open;
        if ($state->isFinal()) {
            JSONResponseHandler::throwError(400, "Angebot ist bereits abgeschlossen");
        }

        $offerNumber = (int) $data[0]["offer_number"];
        if ($offerNumber == 0) {
            $offerNumber = OfferNumberTracker::completeOffer($offerId);
            OrderHistory::add($offerId, $offerId, OrderHistory::TYPE_OFFER, OrderHistory::STATE_FINISHED, "Angebot abgeschlossen, Nr. $offerNumber vergeben");
        }

        JSONResponseHandler::sendResponse([
            "success" => true,
            "offerNumber" => $offerNumber,
        ]);
    }

    public static function rejectOffer(): void
    {
        $offerId = (int) Tools::get("offerId");
        $data = DBAccess::selectQuery("SELECT `state` FROM offer WHERE id = :offerId;", [
            "offerId" => $offerId,
        ]);

        if (empty($data)) {
            JSONResponseHandler::throwError(404, "Angebot existiert nicht");
        }

        $state = OfferState::tryFrom($data[0]["state"]) ?? OfferState::Open;
        if ($state->isFinal()) {
            JSONResponseHandler::throwError(400, "Angebot ist bereits abgeschlossen");
        }

        DBAccess::updateQuery("UPDATE offer SET `state` = :state WHERE id = :offerId;", [
            "state" => OfferState::Rejected->value,
            "offerId" => $offerId,
        ]);

        OrderHistory::add($offerId, $offerId, OrderHistory::TYPE_OFFER, OrderHistory::STATE_REJECTED, "Angebot abgelehnt");

        JSONResponseHandler::sendResponse([
            "success" => true,
        ]);
    }

    public static function sendOffer(): void
    {
        $offerId = (int) Tools::get("offerId");
        $data = DBAccess::selectQuery("SELECT customer_id FROM offer WHERE id = :offerId;", [
            "offerId" => $offerId,
        ]);

        if (empty($data)) {
            JSONResponseHandler::throwError(404, "Angebot existiert nicht");
        }

        $customerId = (int) $data[0]["customer_id"];
        $offer = new Angebot($offerId, $customerId);

        if ($offer->getState()->isFinal()) {
            JSONResponseHandler::throwError(400, "Angebot ist bereits abgeschlossen");
        }

        $email = $offer->getCustomerEmail();
        if ($email === false) {
            JSONResponseHandler::throwError(400, "Kunde hat keine E-Mail-Adresse hinterlegt");
        }

        $offerNumber = $offer->getOfferNumber();
        if ($offerNumber == 0) {
            $offerNumber = OfferNumberTracker::completeOffer($offerId);
            OrderHistory::add($offerId, $offerId, OrderHistory::TYPE_OFFER, OrderHistory::STATE_FINISHED, "Angebot abgeschlossen, Nr. $offerNumber vergeben");
        }

        $offerPDF = new OfferPDF($offerId, $customerId);
        $offerPDF->generate();
        $offerPDF->saveOutput();

        $sent = SendOfferController::handle([
            "email" => $email,
            "offerNumber" => $offerNumber,
            "attachment" => [$offerPDF->getOutputPath() => $offerPDF->getTitle()],
        ]);

        if ($sent) {
            OrderHistory::add($offerId, $offerId, OrderHistory::TYPE_OFFER, OrderHistory::STATE_SENT, "Angebot per E-Mail versendet");
        }

        JSONResponseHandler::sendResponse([
            "success" => $sent,
        ]);
    }

    public static function attachToOrder(int $offerId, int $orderId): void
    {
        $data = DBAccess::selectQuery("SELECT `state`, offer_number FROM offer WHERE id = :offerId;", [
            "offerId" => $offerId,
        ]);

        if (empty($data)) {
            return;
        }

        $state = OfferState::tryFrom($data[0]["state"]) ?? OfferState::Open;
        if ($state->isFinal()) {
            return;
        }

        DBAccess::updateQuery("UPDATE posten SET Auftragsnummer = :orderId, offer_id = NULL WHERE offer_id = :offerId;", [
            "orderId" => $orderId,
            "offerId" => $offerId,
        ]);

        $offerNumber = (int) $data[0]["offer_number"];
        if ($offerNumber == 0) {
            $offerNumber = OfferNumberTracker::completeOffer($offerId);
        }

        DBAccess::updateQuery("UPDATE offer SET `state` = :state WHERE id = :offerId;", [
            "state" => OfferState::Accepted->value,
            "offerId" => $offerId,
        ]);

        OrderHistory::add($offerId, $offerId, OrderHistory::TYPE_OFFER, OrderHistory::STATE_ACCEPTED, "Angebot angenommen, Auftrag #$orderId erstellt");
        OrderHistory::add($orderId, $orderId, OrderHistory::TYPE_ORDER, OrderHistory::STATE_ADDED, "Aus Angebot #$offerId erstellt (Angebotsnr. $offerNumber)");
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
            "history" => OrderHistory::representOfferHistoryAsHTML($offer->offerId),
        ]);

        JSONResponseHandler::sendResponse([
            "content" => $content,
            "offerId" => $offer->getId(),
            "customerId" => $offer->customerId,
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
