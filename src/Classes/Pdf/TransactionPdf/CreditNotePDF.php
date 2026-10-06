<?php

namespace Src\Classes\Pdf\TransactionPdf;

use Src\Classes\Project\CompanyProfile;
use Src\Classes\Project\Config;
use Src\Classes\Project\CreditNote;
use Src\Classes\Project\Invoice;

class CreditNotePDF extends TransactionPDF
{
    private Invoice $invoice;
    private int $creditNumber;
    private \DateTime $creationDate;
    private float $netAmount;
    private float $vatRate;
    private string $reason;
    protected string $type = "creditNote";

    /**
     * @param array<string, string> $creditNote row of invoice_credit_note
     */
    public function __construct(Invoice $invoice, array $creditNote)
    {
        $this->creditNumber = (int) $creditNote["credit_number"];

        parent::__construct(CreditNote::getFileName($this->creditNumber), $invoice->getOrder()->getAuftragsnummer());
        $this->fileName = CreditNote::getFileName($this->creditNumber);

        $this->invoice = $invoice;
        $this->creationDate = new \DateTime($creditNote["creation_date"]);
        $this->netAmount = (float) $creditNote["net_amount"];
        $this->vatRate = (float) $creditNote["vat_rate"] / 100.0;
        $this->reason = $creditNote["reason"];

        $this->addressId = $invoice->getAddressId();
        $this->contactId = $invoice->getContactId();
    }

    public function generate(): void
    {
        parent::generate();

        $this->SetTitle($this->getTitle());
        $this->SetSubject("Gutschrift");
        $this->SetKeywords("Gutschrift");

        $this->SetFont("helvetica", "", 12);
        $this->fillAddress($this->invoice->getAltNames());

        $this->Image(CompanyProfile::getLogo(), 125, 46, 60);

        $this->setXY(125, 54);
        $this->setFontStretching(200);
        $this->SetFont("helvetica", "B", 17);
        $this->Cell(60, 20, "GUTSCHRIFT", 0, 0, 'L', false, '', 2);
        $this->setFontStretching();

        $this->addInfoBlock();
        $this->addBody();
    }

    private function addInfoBlock(int $y = 69): void
    {
        $this->SetFont("helvetica", "", 12);
        $this->setXY(125, $y);
        $this->Cell(30, 10, "Gutschrift-Nr:");
        $this->Cell(30, 10, (string) $this->creditNumber, 0, 0, 'R');
        $this->setXY(125, $y + 6);
        $this->Cell(30, 10, "Datum:");
        $this->Cell(30, 10, $this->creationDate->format("d.m.Y"), 0, 0, 'R');
        $this->setXY(125, $y + 12);
        $this->Cell(30, 10, "Rechnungs-Nr:");
        $this->Cell(30, 10, (string) $this->invoice->getNumber(), 0, 0, 'R');
        $this->setXY(125, $y + 18);
        $this->Cell(30, 10, "Rechnungsdatum:");
        $this->Cell(30, 10, $this->invoice->getCreationDateUnformatted()->format("d.m.Y"), 0, 0, 'R');
        $this->setXY(125, $y + 24);
        $this->Cell(30, 10, "Kunden-Nr.:");
        $this->Cell(30, 10, (string) $this->customer->getKundennummer(), 0, 0, 'R');
    }

    private function addBody(): void
    {
        $invoiceNumber = $this->invoice->getNumber();
        $invoiceDate = $this->invoice->getCreationDateUnformatted()->format("d.m.Y");

        $net = $this->netAmount;
        $vat = round($net * $this->vatRate, 2);
        $gross = $net + $vat;

        $this->setXY(20, 118);
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(170, 8, "Gutschrift zur Rechnung Nr. $invoiceNumber vom $invoiceDate", 0, 1);

        $this->SetFont("helvetica", "", 11);
        $this->MultiCell(170, 6, "Sehr geehrte Damen und Herren,\n\nzu der oben genannten Rechnung schreiben wir Ihnen den folgenden Betrag gut. Das Entgelt der Rechnung mindert sich entsprechend.", 0, 'L');

        $this->ln(6);
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(130, 10, 'Bezeichnung', 'B');
        $this->Cell(40, 10, 'Betrag', 'B', 1, 'R');

        $this->SetFont("helvetica", "", 12);
        $y = $this->GetY();
        $this->MultiCell(130, 10, $this->reason, 0, 'L', false, 0);
        $this->Cell(40, 10, $this->formatAmount(-$net), 0, 1, 'R');
        $this->SetY(max($this->GetY(), $y + $this->getStringHeight(130, $this->reason)));

        $this->ln(4);
        $this->Cell(85, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(55, 10, 'Nettobetrag:', 'T');
        $this->SetFont("helvetica", "", 12);
        $this->Cell(30, 10, $this->formatAmount(-$net), 'T', 1, 'R');

        $this->Cell(85, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $label = ($this->vatRate === 0.0) ? 'MwSt. 0%:' : sprintf('%s%% MwSt.:', rtrim(rtrim(number_format($this->vatRate * 100, 2, ',', ''), '0'), ','));
        $this->Cell(55, 10, $label, 'B');
        $this->SetFont("helvetica", "", 12);
        $this->Cell(30, 10, $this->formatAmount(-$vat), 'B', 1, 'R');

        $this->Cell(85, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(55, 10, 'Gutschriftsbetrag:', 'B');
        $this->Cell(30, 10, $this->formatAmount(-$gross), 'B', 1, 'R');

        $this->ln(8);
        $this->SetFont("helvetica", "", 11);
        $this->MultiCell(170, 6, $this->getSettlementText(), 0, 'L');
    }

    /**
     * Offene Rechnung: der Kunde zahlt den um alle Gutschriften geminderten Betrag.
     * Bereits bezahlte Rechnung: der Gutschriftsbetrag wird erstattet.
     */
    private function getSettlementText(): string
    {
        $gross = $this->netAmount + round($this->netAmount * $this->vatRate, 2);

        if ($this->order->isPaid()) {
            return "Die Rechnung wurde bereits beglichen. Den Gutschriftsbetrag in Höhe von " . $this->formatAmount($gross) . " erstatten wir Ihnen.";
        }

        $invoiceNet = $this->invoice->getAmount();
        $invoiceGross = $invoiceNet + round($invoiceNet * $this->vatRate, 2);

        $creditedGross = 0.0;
        foreach (CreditNote::getForInvoice($this->invoice->getId()) as $creditNote) {
            /* nur Gutschriften bis einschließlich dieser, damit der Beleg seinen Stand zum Ausstellungszeitpunkt zeigt */
            if ((int) $creditNote["credit_number"] > $this->creditNumber) {
                continue;
            }
            $creditNet = (float) $creditNote["net_amount"];
            $creditedGross += $creditNet + round($creditNet * ((float) $creditNote["vat_rate"] / 100.0), 2);
        }

        return "Ursprünglicher Rechnungsbetrag: " . $this->formatAmount($invoiceGross) . "\n"
            . "Abzüglich Gutschriften: " . $this->formatAmount($creditedGross) . "\n"
            . "Verbleibender Zahlbetrag: " . $this->formatAmount($invoiceGross - $creditedGross);
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, ',', '.') . ' €';
    }

    public function getTitle(): string
    {
        return "Gutschrift " . $this->creditNumber . " zur Rechnung " . $this->invoice->getNumber();
    }

    public function getOutputPath(): string
    {
        return Config::get("paths.generatedDir") . $this->fileName . ".pdf";
    }
}
