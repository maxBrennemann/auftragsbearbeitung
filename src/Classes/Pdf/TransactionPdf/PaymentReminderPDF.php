<?php

namespace Src\Classes\Pdf\TransactionPdf;

use Src\Classes\Project\CompanyProfile;
use Src\Classes\Project\Config;
use Src\Classes\Project\CreditNote;
use Src\Classes\Project\Invoice;
use Src\Classes\Project\Settings;

class PaymentReminderPDF extends TransactionPDF
{
    private Invoice $invoice;
    private int $level;
    protected string $type = "reminder";

    public function __construct(Invoice $invoice, int $level)
    {
        parent::__construct("Mahnung_" . $invoice->getNumber(), $invoice->getOrder()->getAuftragsnummer());
        $this->invoice = $invoice;
        $this->level = $level;
        $this->fileName = "Mahnung_" . $invoice->getNumber() . "_Stufe" . $level;

        $this->addressId = $invoice->getAddressId();
        $this->contactId = $invoice->getContactId();
    }

    public function generate(): void
    {
        parent::generate();

        $this->SetTitle($this->getTitle());
        $this->SetSubject("Mahnung");
        $this->SetKeywords("Mahnung");

        $this->SetFont("helvetica", "", 12);
        $this->fillAddress($this->invoice->getAltNames());

        $this->Image(CompanyProfile::getLogo(), 125, 46, 60);

        $heading = $this->getLevelTitle();
        $this->setXY(125, 54);
        $this->setFontStretching(200);
        $this->SetFont("helvetica", "B", 17);
        $this->Cell(60, 20, mb_strtoupper($heading), 0, 0, 'L', false, '', 2);
        $this->setFontStretching();

        $this->addInfoBlock();
        $this->addBody();
    }

    private function addInfoBlock(int $y = 69): void
    {
        $this->SetFont("helvetica", "", 12);
        $this->setXY(125, $y);
        $this->Cell(30, 10, "Rechnungs-Nr:");
        $this->Cell(30, 10, (string) $this->invoice->getNumber(), 0, 0, 'R');
        $this->setXY(125, $y + 6);
        $this->Cell(30, 10, "Rechnungsdatum:");
        $this->Cell(30, 10, $this->invoice->getCreationDateUnformatted()->format("d.m.Y"), 0, 0, 'R');
        $this->setXY(125, $y + 12);
        $this->Cell(30, 10, "Kunden-Nr.:");
        $this->Cell(30, 10, (string) $this->customer->getKundennummer(), 0, 0, 'R');
        $this->setXY(125, $y + 18);
        $this->Cell(30, 10, "Datum:");
        $this->Cell(30, 10, date("d.m.Y"), 0, 0, 'R');
    }

    private function addBody(): void
    {
        $rate = $this->getVatRate();
        $grossAmount = $this->invoice->getAmount() * (1 + $rate);
        $creditedGross = CreditNote::getTotalNetForInvoice($this->invoice->getId()) * (1 + $rate);
        $fee = $this->getReminderFee();
        $total = $grossAmount - $creditedGross + $fee;

        $dueDate = (clone $this->invoice->getCreationDateUnformatted())
            ->modify("+" . $this->getDueDays() . " days");

        $this->setXY(20, 118);
        $this->SetFont("helvetica", "", 11);
        $this->MultiCell(170, 6, $this->getBodyText($dueDate->format("d.m.Y")), 0, 'L');

        $this->ln(6);
        $this->SetFont("helvetica", "B", 11);
        $this->Cell(120, 8, "Rechnungsbetrag:");
        $this->Cell(50, 8, number_format($grossAmount, 2, ',', '.') . ' €', 0, 1, 'R');

        if ($creditedGross > 0) {
            $this->SetFont("helvetica", "", 11);
            $this->Cell(120, 8, "Abzüglich Gutschriften:");
            $this->Cell(50, 8, number_format(-$creditedGross, 2, ',', '.') . ' €', 0, 1, 'R');
        }

        if ($fee > 0) {
            $this->SetFont("helvetica", "", 11);
            $this->Cell(120, 8, "Mahngebühr:");
            $this->Cell(50, 8, number_format($fee, 2, ',', '.') . ' €', 0, 1, 'R');
        }

        $this->SetFont("helvetica", "B", 11);
        $this->Cell(120, 8, "Gesamtbetrag:", 'T');
        $this->Cell(50, 8, number_format($total, 2, ',', '.') . ' €', 'T', 1, 'R');
    }

    private function getBodyText(string $dueDate): string
    {
        return match ($this->level) {
            1 => "Sehr geehrte Damen und Herren,\n\nfür die oben genannte Rechnung konnten wir bislang keinen Zahlungseingang feststellen. Möglicherweise haben Sie die Zahlung bereits veranlasst - in diesem Fall betrachten Sie dieses Schreiben bitte als gegenstandslos. Andernfalls bitten wir Sie, den offenen Betrag bis zum $dueDate zu begleichen.",
            2 => "Sehr geehrte Damen und Herren,\n\ntrotz unserer Zahlungserinnerung konnten wir bislang keinen Zahlungseingang für die oben genannte Rechnung feststellen. Wir bitten Sie, den offenen Betrag inklusive Mahngebühr umgehend, spätestens bis zum $dueDate, zu begleichen.",
            default => "Sehr geehrte Damen und Herren,\n\nauch nach unserer ersten Mahnung konnten wir keinen Zahlungseingang für die oben genannte Rechnung feststellen. Wir fordern Sie hiermit letztmalig auf, den offenen Betrag inklusive Mahngebühr bis zum $dueDate zu begleichen. Andernfalls sehen wir uns gezwungen, weitere Schritte einzuleiten.",
        };
    }

    private function getLevelTitle(): string
    {
        return match ($this->level) {
            1 => "Zahlungserinnerung",
            2 => "1. Mahnung",
            default => "2. Mahnung",
        };
    }

    private function getVatRate(): float
    {
        $vatRaw = Settings::get('invoice.vatRate');
        return is_numeric($vatRaw) ? (float) $vatRaw / 100.0 : 0.19;
    }

    private function getDueDays(): int
    {
        $dueRaw = Settings::get('invoice.dueDate');
        return is_numeric($dueRaw) ? (int) $dueRaw : 0;
    }

    private function getReminderFee(): float
    {
        if ($this->level < 2) {
            return 0.0;
        }

        $feeRaw = Settings::get('invoice.reminderFee');
        return is_numeric($feeRaw) ? (float) $feeRaw : 0.0;
    }

    public function getTitle(): string
    {
        return $this->getLevelTitle() . " zu Rechnung " . $this->invoice->getNumber() . " für " . $this->customer->getFirmenname() . " " . $this->customer->getName();
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getOutputPath(): string
    {
        return Config::get("paths.generatedDir") . $this->fileName . ".pdf";
    }
}
