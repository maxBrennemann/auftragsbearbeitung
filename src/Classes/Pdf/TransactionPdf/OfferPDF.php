<?php

namespace Src\Classes\Pdf\TransactionPdf;

use Src\Classes\Project\Angebot;
use Src\Classes\Project\CompanyProfile;
use Src\Classes\Project\Config;
use Src\Classes\Project\OfferNumberTracker;
use Src\Classes\Project\Posten;
use Src\Classes\Project\Settings;

class OfferPDF extends TransactionPDF
{
    private int $offerId;
    private int $customerId;
    private Angebot $offer;
    protected string $type = "offer";

    public function __construct(int $offerId, int $customerId)
    {
        parent::__construct("Angebot_" . $offerId, 0);
        $this->fileName = "Angebot_" . $offerId;
        $this->offerId = $offerId;
        $this->customerId = $customerId;
        $this->offer = new Angebot($offerId, $customerId);
        $this->customer = $this->offer->getCustomer();
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function generate(): void
    {
        parent::generate();

        $this->SetTitle($this->getTitle());
        $this->SetSubject("Angebot");
        $this->SetKeywords("Angebot");

        $this->SetFont("helvetica", "", 12);
        $this->fillAddress();

        $this->Image(CompanyProfile::getLogo(), 125, 46, 60);

        $this->setXY(125, 54);
        $this->setFontStretching(200);
        $this->SetFont("helvetica", "B", 17);
        $this->Cell(60, 20, "ANGEBOT", 0, 0, 'L', false, '', 2);
        $this->setFontStretching();

        $this->addTableHeader();
        $summe = $this->addOfferItems();

        /* 55: bezieht sich auf die Zwischensumme und Angebotssumme, damit diese immer auf einer Seite stehen */
        if ($this->GetY() + 55 >= $this->pageHeight - $this->getEstimatedFooterHeight()) {
            $this->AddPage();
            $this->addTableHeader($this->topMargin);
            $this->ln(10);
        }

        $zwischensumme = number_format($summe, 2, ',', '') . ' €';
        $vatRaw = Settings::get('invoice.vatRate');
        $rate = is_numeric($vatRaw) ? (float) $vatRaw / 100.0 : 0.19;
        $mwst = number_format($summe * $rate, 2, ',', '') . ' €';
        $gesamtsumme = number_format($summe * (1 + $rate), 2, ',', '') . ' €';

        $this->ln();
        $this->Cell(85, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(60, 10, 'Zwischensumme:', 'T');
        $this->SetFont("helvetica", "", 12);
        $this->Cell(20, 10, $zwischensumme, 'T', 0, 'R');

        $this->ln();
        $this->Cell(85, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $label = ($rate === 0.0) ? 'MwSt. 0%:' : sprintf('%.0f%% MwSt.:', $rate * 100);
        $this->Cell(60, 10, $label, 'B');
        $this->SetFont("helvetica", "", 12);
        $this->Cell(20, 10, $mwst, 'B', 0, 'R');

        $this->ln();
        $this->Cell(85, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(60, 10, 'Angebotssumme:', 'B');
        $this->SetFont("helvetica", "", 12);
        $this->Cell(20, 10, $gesamtsumme, 'B', 0, 'R');
    }

    private function addTableHeader(int $y = 69): void
    {
        $validUntil = $this->offer->getValidUntil();
        $validUntilTimestamp = $validUntil ? strtotime($validUntil) : false;
        $validUntilFormatted = $validUntilTimestamp !== false ? date("d.m.Y", $validUntilTimestamp) : "-";

        $this->SetFont("helvetica", "", 12);
        $this->setXY(125, $y);
        $this->Cell(30, 10, "Angebots-Nr:");
        $this->Cell(30, 10, (string) $this->getOfferNumber(), 0, 0, 'R');
        $this->setXY(125, $y + 6);
        $this->Cell(30, 10, "Datum:");
        $this->Cell(30, 10, date("d.m.Y"), 0, 0, 'R');
        $this->setXY(125, $y + 12);
        $this->Cell(30, 10, "Gültig bis:");
        $this->Cell(30, 10, $validUntilFormatted, 0, 0, 'R');
        $this->setXY(125, $y + 18);
        $this->Cell(30, 10, "Kunden-Nr.:");
        $this->Cell(30, 10, (string) $this->customer->getKundennummer(), 0, 0, 'R');
        $this->setXY(125, $y + 24);
        $this->Cell(30, 10, "Seite:");
        $this->Cell(30, 10, $this->getAliasRightShift() . $this->PageNo() . ' von ' . $this->getAliasNbPages(), 0, 0, 'R');

        $this->renderItemsTableHeader($y + 45);
    }

    private function getOfferNumber(): int
    {
        $offerNumber = $this->offer->getOfferNumber();
        if ($offerNumber == 0) {
            $offerNumber = OfferNumberTracker::peekNextOfferNumber();
        }

        return $offerNumber;
    }

    public function getOutputPath(): string
    {
        return Config::get("paths.generatedDir") . $this->fileName . ".pdf";
    }

    private function addOfferItems(): float
    {
        $positions = Posten::getOfferItems($this->offerId);
        $offset = 124;
        $this->setXY(20, $offset);
        $count = 1;
        $sum = 0.0;

        foreach ($positions as $p) {
            $sum += $p->bekommePreis();
            $addToOffset = $this->renderItemRow($p, $count);
            $offset += $addToOffset;
            $count++;

            /* 297: Din A4 Seitenhöhe, 35: Abstand von unten für die Fußzeile */
            if ($this->GetY() + $addToOffset >= 297 - 35) {
                $this->AddPage();
                $this->addTableHeader(25);
                $this->ln(10);
            }
        }

        return $sum;
    }

    public function getTitle(): string
    {
        return "Angebot " . $this->offerId . " für " . $this->customer->getFirmenname() . " " . $this->customer->getName();
    }
}
