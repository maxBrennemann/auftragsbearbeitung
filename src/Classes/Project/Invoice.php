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
    private Auftrag $auftrag;
    private int $addressId = 0;
    private int $contactId = 0;

    private int $invoiceId = 0;
    private int $invoiceNumber = 0;
    private float $amount = 0;
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
        $query = "SELECT id FROM invoice WHERE order_id = :orderId;";
        $data = DBAccess::selectQuery($query, [
            "orderId" => $orderId,
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
        $sum = (float) $this->auftrag->calcOrderSum();
        DBAccess::updateQuery("UPDATE invoice SET amount = :amount WHERE id = :id;", [
            "amount" => $sum,
            "id" => $this->invoiceId,
        ]);
    }

    public static function setAddress(): void
    {
        $invoiceId = (int) Tools::get("invoiceId");
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
        $invoice = new InvoicePDF($invoiceId, $orderId);

        $invoice->generate();
        $invoice->generateOutput($invoice->getTitle());
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
            CustomerAltNames::edit((int) $editText["id"], $editText["text"]);
        }

        foreach (json_decode($remove) as $removeId) {
            CustomerAltNames::remove((int) $removeId);
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
