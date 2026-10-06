<?php

namespace Src\Classes\Project;

use Src\Classes\Controller\TemplateController;
use Src\Classes\Models\Invoice as InvoiceModel;
use Src\Classes\Pdf\TransactionPdf\InvoicePDF;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Invoice
{
    public const STATUS_DRAFT = "draft";
    public const STATUS_FINALIZED = "finalized";
    public const STATUS_CANCELLED = "cancelled";

    private Auftrag $auftrag;
    private int $addressId = 0;
    private int $contactId = 0;

    private int $invoiceId = 0;
    private int $invoiceNumber = 0;
    private float $amount = 0;
    private string $status = self::STATUS_DRAFT;
    /** @var array<Leistung|ProduktPosten|Zeit> */
    private array $posten = [];

    private ?\DateTime $creationDate = null;
    private ?\DateTime $performanceDate = null;
    private bool $showPerformanceDate = true;
    private string $performanceDateType = "date";

    public function __construct(int $invoiceId, int $orderId)
    {
        $this->auftrag = new Auftrag($orderId);
        $this->invoiceId = $invoiceId;
        //$this->invoiceModel = InvoiceModel::find($invoiceId);

        $query = "SELECT * FROM invoice WHERE id = :invoiceId";
        $data = DBAccess::selectQuery($query, [
            "invoiceId" => $invoiceId,
        ]);

        if (empty($data)) {
            throw new \Exception("Invoice not found.");
        }

        if ((int) $data[0]["order_id"] !== $orderId) {
            throw new \Exception("Invoice does not belong to the given order.");
        }

        $this->invoiceNumber = ((int) $data[0]["invoice_number"]);
        $this->status = (string) $data[0]["status"];
        $this->amount = (float) $data[0]["amount"];
        $this->creationDate = new \DateTime($data[0]["creation_date"]);
        $this->performanceDate = new \DateTime($data[0]["performance_date"]);
        $this->showPerformanceDate = (bool) $data[0]["show_performance_date"];
        $this->performanceDateType = (string) $data[0]["performance_date_type"];
        $this->addressId = (int) $data[0]["address_id"];
        $this->contactId = (int) $data[0]["contact_id"];
        $this->getTexts();
    }

    public function getAddressId(): int
    {
        return $this->addressId;
    }

    public function getContactId(): int
    {
        return $this->contactId;
    }

    public static function getInvoice(int $invoiceNumber): ?Invoice
    {
        if ($invoiceNumber <= 0) {
            return null;
        }

        $query = "SELECT order_id, id FROM invoice WHERE invoice_number = :invoiceNumber;";
        $data = DBAccess::selectQuery($query, [
            "invoiceNumber" => $invoiceNumber,
        ]);

        if (!empty($data)) {
            $orderId = (int) $data[0]["order_id"];
            $invoiceId = (int) $data[0]["id"];
            return new Invoice($invoiceId, $orderId);
        }

        return null;
    }

    public static function getInvoiceByOrderId(int $orderId): Invoice
    {
        $query = "SELECT id FROM invoice WHERE order_id = :orderId AND `status` != :cancelled ORDER BY id DESC LIMIT 1;";
        $data = DBAccess::selectQuery($query, [
            "orderId" => $orderId,
            "cancelled" => self::STATUS_CANCELLED,
        ]);

        if (!empty($data)) {
            $invoiceId = (int) $data[0]["id"];
            return new Invoice($invoiceId, $orderId);
        }

        $query = "INSERT INTO invoice (invoice_number, order_id, creation_date, performance_date, amount) VALUES (0, :orderId, :creationDate, :performanceDate, :amount)";
        $invoiceId = DBAccess::insertQuery($query, [
            "orderId" => $orderId,
            "creationDate" => date("Y-m-d"),
            "performanceDate" => date("Y-m-d"),
            "amount" => 0,
        ]);

        $invoice = new Invoice($invoiceId, $orderId);

        return $invoice;
    }

    public function getOrder(): Auftrag
    {
        return $this->auftrag;
    }

    public function getInvoiceEmail(): false|string
    {
        $customerId = $this->auftrag->getKundennummer();
        $customer = new Kunde($customerId);
        $invoiceEmail = $customer->getInvoiceEmail();

        if ($invoiceEmail == "") {
            return false;
        }

        return $invoiceEmail;
    }

    public function getPerformanceDate(): string
    {
        return $this->getPerformanceDateUnformatted()->format("Y-m-d");
    }

    public function getPerformanceDateUnformatted(): \DateTime
    {
        if ($this->performanceDate == null) {
            $this->performanceDate = new \DateTime();
            $this->performanceDate->setTimezone(new \DateTimeZone("Europe/Berlin"));
        }
        return $this->performanceDate;
    }

    public function getShowPerformanceDate(): bool
    {
        return $this->showPerformanceDate;
    }

    public function getPerformanceDateType(): string
    {
        return $this->performanceDateType;
    }

    /**
     * Formatted for use as the value of an <input type="week"> element (e.g. "2026-W33").
     */
    public function getPerformanceDateWeekValue(): string
    {
        return $this->getPerformanceDateUnformatted()->format('o-\WW');
    }

    public function getCreationDate(): string
    {
        return $this->getCreationDateUnformatted()->format("Y-m-d");
    }

    public function getCreationDateUnformatted(): \DateTime
    {
        if ($this->creationDate == null) {
            $this->creationDate = new \DateTime();
            $this->creationDate->setTimezone(new \DateTimeZone("Europe/Berlin"));
        }
        return $this->creationDate;
    }

    public function getId(): int
    {
        return $this->invoiceId;
    }

    public function getNumber(): int
    {
        return $this->invoiceNumber;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Eine ausgestellte (oder stornierte) Rechnung darf nicht mehr verändert werden. Korrekturen
     * laufen über eigene Belege: Gutschrift zur Rechnung (CreditNote) oder Stornorechnung (cancel()).
     */
    public function isLocked(): bool
    {
        return $this->status !== self::STATUS_DRAFT;
    }

    public static function getStoredPdfPath(int $invoiceNumber): string
    {
        return Config::get("paths.generatedDir") . "Rechnung_$invoiceNumber.pdf";
    }

    private static function assertEditable(int $invoiceId): void
    {
        $data = DBAccess::selectQuery("SELECT `status` FROM invoice WHERE id = :invoiceId;", [
            "invoiceId" => $invoiceId,
        ]);

        if (empty($data)) {
            JSONResponseHandler::throwError(404, "Rechnung nicht gefunden");
        }

        if ($data[0]["status"] !== self::STATUS_DRAFT) {
            JSONResponseHandler::throwError(400, "Die Rechnung ist abgeschlossen und kann nicht mehr geändert werden. Für Korrekturen bitte eine Gutschrift erstellen oder die Rechnung stornieren.");
        }
    }

    /**
     * Stornierte Rechnungen eines Auftrags samt zugehöriger Stornorechnung.
     *
     * @return array<int, array<string, string>>
     */
    public static function getCancelledForOrder(int $orderId): array
    {
        $query = "SELECT i.invoice_number, DATE_FORMAT(i.creation_date, '%d.%m.%Y') AS invoice_date,
                cn.credit_number AS cancellation_number, DATE_FORMAT(cn.creation_date, '%d.%m.%Y') AS cancellation_date, cn.reason
            FROM invoice i
            LEFT JOIN invoice_credit_note cn ON cn.invoice_id = i.id AND cn.`type` = 'cancellation'
            WHERE i.order_id = :orderId AND i.`status` = :status
            ORDER BY i.id";

        return DBAccess::selectQuery($query, [
            "orderId" => $orderId,
            "status" => self::STATUS_CANCELLED,
        ]);
    }

    /**
     * @return array<Leistung|ProduktPosten|Zeit>
     */
    public function loadPostenFromAuftrag(): array
    {
        $orderId = $this->auftrag->getAuftragsnummer();
        $this->posten = Posten::getOrderItems($orderId, true, 1);
        return $this->posten;
    }

    /**
     * Kopfzeilen-Override gilt pro Kunde (nicht pro Rechnung), damit einmal erfasste
     * Zeilen automatisch für alle Rechnungen dieses Kunden wiederverwendet werden.
     *
     * @return array<int, array<string, string>>
     */
    public function getAltNames(): array
    {
        return CustomerAltNames::getForCustomer($this->auftrag->getKundennummer());
    }

    public static function toggleText(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        self::assertEditable($invoiceId);
        $textId = (int) Tools::get("textId");

        $id = DocumentText::toggleText("invoice", $invoiceId, $textId, (string) Tools::get("text"));

        /* Adds default text if not already present */
        if ($textId == 0) {
            JSONResponseHandler::sendResponse([
                "status" => "success",
                "id" => $id,
            ]);
            return;
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function addText(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        self::assertEditable($invoiceId);
        $text = (string) Tools::get("text");

        $id = DocumentText::addText("invoice", $invoiceId, $text);

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "id" => $id,
        ]);
    }

    public static function editText(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        self::assertEditable($invoiceId);
        $textId = (int) Tools::get("textId");
        $text = (string) Tools::get("text");

        DocumentText::editText("invoice", $invoiceId, $textId, $text);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function deleteText(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        self::assertEditable($invoiceId);
        $textId = (int) Tools::get("textId");

        DocumentText::deleteText("invoice", $invoiceId, $textId);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTexts(): array
    {
        /* über die Einstellungen-Seite (pdf_texts, Typ invoice_default_text) editierbar statt im Code eingebrannt */
        $data = DocumentText::getTexts("invoice", $this->invoiceId, "invoice_default_text");

        /*
         * Leistungsdatum wird bewusst nicht über den generischen Text-Toggle verwaltet
         * (id < 0, da echte document_text-Zeilen immer eine positive AUTO_INCREMENT-id haben):
         * Sichtbarkeit steuert Invoice::setPerformanceDateVisibility(), nicht toggleText(),
         * damit der Eintrag nicht versehentlich als eigenständiger Text persistiert werden kann.
         */
        if ($this->showPerformanceDate) {
            $text = $this->performanceDateType === "week"
                ? "Leistungszeitraum KW " . $this->getPerformanceDateUnformatted()->format("W") . "/" . $this->getPerformanceDateUnformatted()->format("o")
                : "Leistungsdatum " . $this->getPerformanceDateUnformatted()->format("d.m.Y");

            $data[] = [
                "id" => -1,
                "id_invoice" => $this->invoiceId,
                "text" => $text,
                "active" => 1,
            ];
        }

        return $data;
    }

    /**
     * @return string[][]
     */
    public function getAttachedVehicles(): array
    {
        return $this->auftrag->getLinkedVehicles();
    }

    public function setInvoiceSum(): void
    {
        /* der Betrag einer ausgestellten Rechnung steht fest, auch wenn der Auftrag später geändert wird */
        if ($this->isLocked()) {
            return;
        }

        $sum = (float) $this->auftrag->calcOrderSum();
        DBAccess::updateQuery("UPDATE invoice SET amount = :amount WHERE id = :id;", [
            "amount" => $sum,
            "id" => $this->invoiceId,
        ]);
    }

    public static function setAddress(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        self::assertEditable($invoiceId);
        $addressId = (int) Tools::get("addressId");

        if ($addressId !== 0) {
            $customerId = self::getCustomerIdForInvoice($invoiceId);
            if (!Address::hasAddress($customerId, $addressId)) {
                JSONResponseHandler::sendErrorResponse(400, "Adresse gehört nicht zum Kunden dieser Rechnung.");
                return;
            }
        }

        $query = "UPDATE invoice SET address_id = :addressId WHERE id = :invoiceId;";
        DBAccess::updateQuery($query, [
            "addressId" => $addressId,
            "invoiceId" => $invoiceId,
        ]);

        JSONResponseHandler::returnOK();
    }

    /**
     * Resolves the customer an invoice belongs to via its order, without needing the order id
     * to be passed in separately (unlike constructing an Invoice instance).
     */
    private static function getCustomerIdForInvoice(int $invoiceId): int
    {
        $query = "SELECT a.Kundennummer AS customerId FROM invoice i
            JOIN auftrag a ON a.Auftragsnummer = i.order_id
            WHERE i.id = :invoiceId;";
        $data = DBAccess::selectQuery($query, [
            "invoiceId" => $invoiceId,
        ]);

        if (empty($data)) {
            throw new \Exception("Invoice not found.");
        }

        return (int) $data[0]["customerId"];
    }

    public static function setContact(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        self::assertEditable($invoiceId);
        $contactId = (int) Tools::get("contactId");
        $query = "UPDATE invoice SET contact_id = :contactId WHERE id = :invoiceId;";
        DBAccess::updateQuery($query, [
            "contactId" => $contactId,
            "invoiceId" => $invoiceId,
        ]);

        JSONResponseHandler::returnOK();
    }

    public static function setInvoiceDate(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        self::assertEditable($invoiceId);
        $date = Tools::get("date");

        $query = "UPDATE invoice SET creation_date = :date WHERE id = :invoiceId";
        DBAccess::updateQuery($query, [
            "date" => $date,
            "invoiceId" => $invoiceId,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function setServiceDate(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        self::assertEditable($invoiceId);
        $type = Tools::get("type") === "week" ? "week" : "date";
        $value = Tools::get("date");

        $date = $type === "week" ? self::mondayOfIsoWeek($value) : $value;

        $query = "UPDATE invoice SET performance_date = :date, performance_date_type = :type WHERE id = :invoiceId";
        DBAccess::updateQuery($query, [
            "date" => $date,
            "type" => $type,
            "invoiceId" => $invoiceId,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    /**
     * Converts an <input type="week"> value ("YYYY-Www") to the Monday of that ISO week.
     */
    private static function mondayOfIsoWeek(string $isoWeek): string
    {
        if (!preg_match('/^(\d{4})-W(\d{2})$/', $isoWeek, $matches)) {
            return date("Y-m-d");
        }

        $date = new \DateTime();
        $date->setISODate((int) $matches[1], (int) $matches[2]);

        return $date->format("Y-m-d");
    }

    public static function setPerformanceDateVisibility(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        self::assertEditable($invoiceId);
        $show = (int) Tools::get("show") ? 1 : 0;

        $query = "UPDATE invoice SET show_performance_date = :show WHERE id = :invoiceId";
        DBAccess::updateQuery($query, [
            "show" => $show,
            "invoiceId" => $invoiceId,
        ]);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function completeInvoice(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $orderId = (int) Tools::get("orderId");

        try {
            $invoice = new Invoice($invoiceId, $orderId);
        } catch (\Exception $e) {
            $invoice = self::getInvoiceByOrderId($orderId);
        }

        $invoiceId = $invoice->getId();

        if ($invoice->getStatus() === self::STATUS_CANCELLED) {
            JSONResponseHandler::throwError(400, "Die Rechnung wurde storniert");
        }

        /*
         * Eine bereits ausgestellte Rechnung wird nicht neu erzeugt oder erneut versendet, sondern nur
         * (wieder) dem Auftrag zugeordnet. Die PDF wird ausschließlich dann neu geschrieben, wenn die
         * gespeicherte Datei fehlt.
         */
        if ($invoice->isLocked()) {
            DBAccess::updateQuery("UPDATE auftrag SET Rechnungsnummer = :invoiceId WHERE Auftragsnummer = :orderId", [
                "invoiceId" => $invoiceId,
                "orderId" => $orderId,
            ]);

            if (!file_exists(self::getStoredPdfPath($invoice->getNumber()))) {
                $invoicePDF = new InvoicePDF($invoiceId, $orderId);
                $invoicePDF->generate();
                $invoicePDF->saveOutput($invoice->getNumber());
            }

            JSONResponseHandler::sendResponse([
                "status" => "success",
                "number" => $invoice->getNumber(),
                "id" => $invoiceId,
            ]);
            return;
        }

        $invoice->setInvoiceSum();

        $query = "UPDATE auftrag SET Rechnungsnummer = :invoiceId WHERE Auftragsnummer = :orderId";
        DBAccess::updateQuery($query, [
            "invoiceId" => $invoiceId,
            "orderId" => $orderId,
        ]);

        $invoiceNumber = $invoice->getNumber();
        if ($invoice->getNumber() == 0) {
            $invoiceNumber = InvoiceNumberTracker::completeInvoice($invoice);
        }

        $invoicePDF = new InvoicePDF($invoiceId, $orderId);
        $invoicePDF->generate();
        $invoicePDF->saveOutput($invoiceNumber);

        InvoiceHelper::sendInvoiceMail($invoice, $invoicePDF, $invoiceNumber);

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "number" => $invoiceNumber,
            "id" => $invoiceId,
        ]);
    }

    public static function setInvoicePaid(int $invoiceId, string $paymentDate, string $paymentType): void
    {
        $paymentTypes = [
            "ueberweisung",
            "bar",
            "paypal",
            "kreditkarte",
            "amazonpay",
            "weiteres"
        ];

        if (!in_array($paymentType, $paymentTypes)) {
            $paymentType = "ueberweisung";
        }

        if (!validateDateString($paymentDate, "Y-m-d")) {
            $paymentDate = date("Y-m-d");
        }

        $query = "UPDATE auftrag SET Bezahlt = 1 
			WHERE Rechnungsnummer = :invoice";

        DBAccess::updateQuery($query, [
            "invoice" => $invoiceId,
        ]);

        DBAccess::updateQuery("UPDATE invoice SET payment_date = :paymentDate, payment_type = :paymentType WHERE id = :invoice", [
            "paymentDate" => $paymentDate,
            "paymentType" => $paymentType,
            "invoice" => $invoiceId,
        ]);

        $orderId = DBAccess::selectQuery("SELECT Auftragsnummer FROM auftrag WHERE Rechnungsnummer = :invoice;", [
            "invoice" => $invoiceId
        ]);
        $orderId = (int) $orderId[0]["Auftragsnummer"];
        OrderHistory::add($orderId, $invoiceId, OrderHistory::TYPE_ORDER, OrderHistory::STATE_PAYED);
    }

    public static function setInvoicePaidAjax(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $paymentDate = Tools::get("date");
        $paymentType = (string) Tools::get("paymentType");

        self::setInvoicePaid($invoiceId, $paymentDate, $paymentType);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function setInvoiceUnpaid(int $invoiceId): void
    {
        $query = "UPDATE auftrag SET Bezahlt = 0
			WHERE Rechnungsnummer = :invoice";

        DBAccess::updateQuery($query, [
            "invoice" => $invoiceId,
        ]);

        DBAccess::updateQuery("UPDATE invoice SET payment_date = NULL, payment_type = 'unbezahlt' WHERE id = :invoice", [
            "invoice" => $invoiceId,
        ]);

        $orderId = DBAccess::selectQuery("SELECT Auftragsnummer FROM auftrag WHERE Rechnungsnummer = :invoice;", [
            "invoice" => $invoiceId
        ]);
        $orderId = (int) $orderId[0]["Auftragsnummer"];
        OrderHistory::add($orderId, $invoiceId, OrderHistory::TYPE_ORDER, OrderHistory::STATE_UNPAID);
    }

    public static function setInvoiceUnpaidAjax(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");

        self::setInvoiceUnpaid($invoiceId);

        JSONResponseHandler::sendResponse([
            "status" => "success",
        ]);
    }

    public static function getPDF(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $orderId = (int) Tools::get("orderId");

        /* ausgestellte Rechnungen werden so ausgeliefert, wie sie gespeichert wurden, nicht aus den aktuellen Auftragsdaten neu gerendert */
        $data = DBAccess::selectQuery("SELECT invoice_number, `status` FROM invoice WHERE id = :invoiceId AND order_id = :orderId;", [
            "invoiceId" => $invoiceId,
            "orderId" => $orderId,
        ]);
        if (!empty($data) && $data[0]["status"] !== self::STATUS_DRAFT) {
            $storedPath = self::getStoredPdfPath((int) $data[0]["invoice_number"]);
            if (file_exists($storedPath)) {
                header("Content-Type: application/pdf");
                header("X-Content-Type-Options: nosniff");
                header("Content-Disposition: inline; filename=\"" . basename($storedPath) . "\"");
                readfile($storedPath);
                return;
            }
        }

        $invoice = new InvoicePDF($invoiceId, $orderId);

        $invoice->generate();
        $invoice->generateOutput($invoice->getTitle());
    }

    /**
     * Storniert eine ausgestellte Rechnung vollständig: erzeugt eine Stornorechnung mit eigener Nummer
     * über den noch nicht gutgeschriebenen Betrag, markiert die Rechnung als storniert und gibt den
     * Auftrag wieder frei, sodass eine neue Rechnung erstellt werden kann. Die Originalrechnung
     * bleibt unverändert erhalten.
     */
    public static function cancel(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $orderId = (int) Tools::get("orderId");
        $reason = trim((string) Tools::get("reason"));
        $sendMail = (int) Tools::get("sendMail") === 1;

        try {
            $invoice = new Invoice($invoiceId, $orderId);
        } catch (\Exception $e) {
            JSONResponseHandler::throwError(404, "Rechnung nicht gefunden");
        }

        if ($invoice->getStatus() !== self::STATUS_FINALIZED) {
            JSONResponseHandler::throwError(400, "Nur abgeschlossene, nicht stornierte Rechnungen können storniert werden");
        }

        if ($reason === "") {
            JSONResponseHandler::throwError(400, "Bitte einen Grund für die Stornierung angeben");
        }

        /* muss vor dem Zurücksetzen von Bezahlt passieren, der Beleg weist den Zahlungsstand zum Stornozeitpunkt aus */
        $cancellation = CreditNote::createCancellation($invoice, $reason);

        DBAccess::updateQuery("UPDATE invoice SET `status` = :status WHERE id = :invoiceId", [
            "status" => self::STATUS_CANCELLED,
            "invoiceId" => $invoiceId,
        ]);

        DBAccess::updateQuery("UPDATE auftrag SET Rechnungsnummer = 0, Bezahlt = 0 WHERE Auftragsnummer = :orderId", [
            "orderId" => $orderId,
        ]);

        OrderHistory::add($orderId, $invoiceId, OrderHistory::TYPE_ORDER, OrderHistory::STATE_EDITED, "Rechnung Nr. {$invoice->getNumber()} storniert (Stornorechnung Nr. {$cancellation["number"]}): $reason");

        $mailSent = false;
        if ($sendMail) {
            $mailSent = CreditNote::sendMail($invoice, $cancellation["pdf"], $cancellation["number"], CreditNote::TYPE_CANCELLATION);
        }

        JSONResponseHandler::sendResponse([
            "status" => "success",
            "number" => $cancellation["number"],
            "link" => CreditNote::getPdfLink($cancellation["number"], CreditNote::TYPE_CANCELLATION),
            "mailRequested" => $sendMail,
            "mailSent" => $mailSent,
        ]);
    }

    public static function handleAltNames(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $add = Tools::get("add");
        $edit = Tools::get("edit");
        $remove = Tools::get("remove");

        $customerId = self::getCustomerIdForInvoice($invoiceId);

        foreach (json_decode($add) as $text) {
            CustomerAltNames::add($customerId, $text);
        }

        foreach (json_decode($edit, true) as $editText) {
            CustomerAltNames::edit($customerId, (int) $editText["id"], $editText["text"]);
        }

        foreach (json_decode($remove) as $removeId) {
            CustomerAltNames::remove($customerId, (int) $removeId);
        }

        JSONResponseHandler::returnOK();
    }

    public static function getAltNamesTemplate(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
        $orderId = (int) Tools::get("orderId");

        $invoice = new Invoice($invoiceId, $orderId);
        $altNames = $invoice->getAltNames();

        $template = TemplateController::getTemplate("invoiceAltNames", [
            "altNames" => $altNames,
        ]);

        JSONResponseHandler::sendResponse([
            "template" => $template,
        ]);
    }
}
