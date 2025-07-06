<?php

namespace Classes\Pdf\TransactionPdf;

use Classes\Link;
use Classes\Project\Invoice;
use Classes\Project\ClientSettings;

class InvoicePDF extends TransactionPDF
{

    private Invoice $invoice;

    public function __construct(int $invoiceId, int $orderId)
    {
        parent::__construct("Rechnung_" . $invoiceId, $orderId);
        $this->fileName = "Rechnung_" . $invoiceId;
        $this->invoice = new Invoice($invoiceId, $orderId);

        $this->invoiceId = $invoiceId;
        $this->addressId = $this->invoice->getAddressId();
        $this->contactId = $this->invoice->getContactId();
    }

    public function generate()
    {
        parent::generate();

        $this->SetTitle("Rechnung für " . $this->customer->getFirmenname() . " " . $this->customer->getName());
        $this->SetSubject("Rechnung");
        $this->SetKeywords("Rechnung");

        $this->SetFont("helvetica", "", 12);
        $this->fillAddress($this->invoice->getAltNames());

        $this->Image($this->getCompanyLogo(), 125, 46, 60);

        $this->setXY(125, 54);
        $this->setFontStretching(200);
        $this->SetFont("helvetica", "B", 17);
        $this->Cell(60, 20, "RECHNUNG", 0, 0, 'L', 0, '', 2);
        $this->setFontStretching();

        $this->addTableHeader();

        /* iterates over all posten and adds lines */
        $lineheight = 10;
        $posten = $this->invoice->loadPostenFromAuftrag();
        $offset = 124;
        $this->setXY(20, $offset);
        $count = 1;

        foreach ($posten as $p) {
            $this->Cell(15, $lineheight, $count);
            $this->Cell(20, $lineheight, $p->getQuantity());
            $this->Cell(20, $lineheight, $p->getEinheit());

            $height = $this->getStringHeight(70, $p->getDescription());
            $addToOffset = $lineheight;

            $descriptionWidth = 70;
            if ($p->getOhneBerechnung() == true) {
                $descriptionWidth = 50;
            }

            if ($height >= $lineheight) {
                $x = $this->GetX();
                $y = $this->getY();
                $this->MultiCell($descriptionWidth, $lineheight, $p->getDescription(), '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
                $addToOffset = ceil($height);
            } else {
                $this->Cell($descriptionWidth, $lineheight, $p->getDescription());
            }

            if ($p->getOhneBerechnung() == true) {
                $this->SetFont("helvetica", "", 6);
                $this->Cell(20, $lineheight, "Ohne Berechnung");
                $this->SetFont("helvetica", "", 12);
            }

            $this->Cell(20, $lineheight, $p->bekommeEinzelPreis_formatted());
            $this->Cell(20, $lineheight, $p->bekommePreis_formatted(), 0, 0, 'R');

            $offset += $addToOffset;
            $this->ln($addToOffset);

            /* 297: Din A4 Seitenhöhe, 25: Abstand von unten für die Fußzeile */
            if ($this->GetY() + $addToOffset >= 297 - 35) {
                $this->AddPage();
                $this->addTableHeader(25);
                $this->ln(10);
            }

            $count++;
        }

        foreach ($this->invoice->getTexts() as $text) {
            if ($text["active"] == "0") {
                continue;
            }

            $this->Cell(55, $lineheight, "");

            $heigth = $this->getStringHeight(70, $text["text"]);
            $addToOffset = $lineheight;

            if ($heigth >= $lineheight) {
                $this->MultiCell(70, $lineheight, $text["text"], '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
                $addToOffset = ceil($heigth);
            } else {
                $this->Cell(70, $lineheight, $text["text"]);
            }
            $this->Cell(40, $lineheight, "");
            $offset += $addToOffset;
            $this->ln($addToOffset);

            /* 297: Din A4 Seitenhöhe, 25: Abstand von unten für die Fußzeile */
            if ($this->GetY() + $addToOffset >= 297 - 35) {
                $this->AddPage();
                $this->addTableHeader(25);
                $this->ln(10);
            }

            $count++;
        }

        /**
         * 297: Din A4 Seitenhöhe,
         * 25: Abstand von unten für die Fußzeile,
         * 55: bezieht sich auf die Zwischensumme und Rechnungssumme, 
         * damit diese immer auf einer Seite stehen
         */
        if ($this->GetY() + 55 >= 297 - 35) {
            $this->AddPage();
            $this->addTableHeader(25);
            $this->ln(10);
        }

        /* Code für Zwischensumme und Rechnungssumme */
        $summe = $this->order->calcOrderSum();
        $zwischensumme = number_format($summe, 2, ',', '') . ' €';
        $mwst = number_format($summe * 0.19, 2, ',', '') . ' €';
        $rechnungssumme = number_format($summe * 1.19, 2, ',', '') . ' €';

        $this->ln();
        $this->Cell(85, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(60, 10, 'Zwischensumme:', 'T');
        $this->SetFont("helvetica", "", 12);
        $this->Cell(20, 10, $zwischensumme, 'T', 0, 'R');

        $this->ln();
        $this->Cell(85, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(60, 10, '19% MwSt.:', 'B');
        $this->SetFont("helvetica", "", 12);
        $this->Cell(20, 10, $mwst, 'B', 0, 'R');

        $this->ln();
        $this->Cell(85, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(60, 10, 'Rechnungssumme:', 'B');
        $this->SetFont("helvetica", "", 12);
        $this->Cell(20, 10, $rechnungssumme, 'B', 0, 'R');

        $this->ln();
        $this->setCellMargins(0, 1, 0, 0);
        $this->Cell(85, 10, "");
        $this->Cell(60, 10, '', 'T');
        $this->Cell(20, 10, '', 'T');

        /* Code für "Zahlbar sofort ohne weitere Abzüge" */
        $this->ln();
        $this->setCellMargins(0, 0, 0, 0);
        $this->SetFont("helvetica", "", 10);
        $this->Cell(160, 10, "Zahlbar sofort ohne weitere Abzüge.");
    }

    private function addTableHeader($y = 69)
    {
        $this->SetFont("helvetica", "", 12);
        $this->setXY(125, $y);
        $this->Cell(30, 10, "Rechnungs-Nr:");
        $this->Cell(30, 10, $this->invoice->getNumber(), 0, 0, 'R');
        $this->setXY(125, $y + 6);
        $this->Cell(30, 10, "Auftrags-Nr:");
        $this->Cell(30, 10, $this->order->getAuftragsnummer(), 0, 0, 'R');
        $this->setXY(125, $y + 12);
        $this->Cell(30, 10, "Datum:");
        $this->Cell(30, 10, $this->invoice->getCreationDateUnformatted()->format("d.m.Y"), 0, 0, 'R');
        $this->setXY(125, $y + 18);
        $this->Cell(30, 10, "Kunden-Nr.:");
        $this->Cell(30, 10, $this->customer->getKundennummer(), 0, 0, 'R');
        $this->setXY(125, $y + 24);
        $this->Cell(30, 10, "Seite:");
        $this->Cell(30, 10, $this->getAliasRightShift() . $this->PageNo() . ' von ' . $this->getAliasNbPages(), 0, 0, 'R');

        $this->setXY(20, $y + 45);
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(15, 10, 'Pos.', 'B');
        $this->Cell(20, 10, 'Menge', 'B');
        $this->Cell(20, 10, 'MEH', 'B');
        $this->Cell(70, 10, 'Bezeichnung', 'B');
        $this->Cell(20, 10, 'E-Preis', 'B');
        $this->Cell(20, 10, 'G-Preis', 'B');
        $this->SetFont("helvetica", "", 12);
    }

    private function getCompanyLogo(): string
    {
        $image = ClientSettings::getLogo();
        if ($image == "") {
            return "files/assets/img/default_image.png";
        } else {
            return Link::getFilePath($image, "upload");
        }
    }
}
