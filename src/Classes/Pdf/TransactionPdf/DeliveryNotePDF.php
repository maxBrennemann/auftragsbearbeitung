<?php

namespace Src\Classes\Pdf\TransactionPdf;

use Src\Classes\Project\CompanyProfile;
use Src\Classes\Project\Posten;

class DeliveryNotePDF extends TransactionPDF
{
    protected string $type = "deliveryNote";

    public function __construct(int $orderId)
    {
        parent::__construct("Lieferschein_" . $orderId, $orderId);
        $this->fileName = "Lieferschein_" . $orderId;
    }

    public function generate(): void
    {
        parent::generate();

        $this->SetTitle($this->getTitle());
        $this->SetSubject("Lieferschein");
        $this->SetKeywords("Lieferschein");

        $this->SetFont("helvetica", "", 12);
        $this->fillAddress();

        $this->Image(CompanyProfile::getLogo(), 125, 46, 60);

        $this->setXY(125, 54);
        $this->setFontStretching(200);
        $this->SetFont("helvetica", "B", 17);
        $this->Cell(60, 20, "LIEFERSCHEIN", 0, 0, 'L', false, '', 2);
        $this->setFontStretching();

        $this->addInfoBlock();
        $this->addItemsTable();
    }

    private function addInfoBlock(int $y = 69): void
    {
        $this->SetFont("helvetica", "", 12);
        $this->setXY(125, $y);
        $this->Cell(30, 10, "Auftrags-Nr:");
        $this->Cell(30, 10, (string) $this->order->getAuftragsnummer(), 0, 0, 'R');
        $this->setXY(125, $y + 6);
        $this->Cell(30, 10, "Datum:");
        $this->Cell(30, 10, date("d.m.Y"), 0, 0, 'R');
        $this->setXY(125, $y + 12);
        $this->Cell(30, 10, "Kunden-Nr.:");
        $this->Cell(30, 10, (string) $this->customer->getKundennummer(), 0, 0, 'R');
        $this->setXY(125, $y + 18);
        $this->Cell(30, 10, "Seite:");
        $this->Cell(30, 10, $this->getAliasRightShift() . $this->PageNo() . ' von ' . $this->getAliasNbPages(), 0, 0, 'R');

        $this->renderItemsTableHeader($y + 39, false);
    }

    private function addItemsTable(): void
    {
        $positions = Posten::getOrderItems($this->orderId, true, 1);
        $offset = 118;
        $this->setXY(20, $offset);
        $count = 1;

        foreach ($positions as $p) {
            $addToOffset = $this->renderItemRow($p, $count, false);
            $offset += $addToOffset;
            $count++;

            /* 297: Din A4 Seitenhöhe, 35: Abstand von unten für die Fußzeile */
            if ($this->GetY() + $addToOffset >= 297 - 35) {
                $this->AddPage();
                $this->addInfoBlock(25);
                $this->ln(10);
            }
        }
    }

    public function getTitle(): string
    {
        return "Lieferschein für Auftrag " . $this->order->getAuftragsnummer() . " " . $this->customer->getFirmenname() . " " . $this->customer->getName();
    }
}
