<?php

namespace Classes\Pdf\TransactionPdf;

use Classes\Project\Invoice;
use Classes\Project\InvoiceNumberTracker;

use Classes\Link;
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
        $this->setPrintHeader(false);

        $this->SetTitle("Rechnung für " . $this->customer->getFirmenname() . " " . $this->customer->getName());
        $this->SetSubject("Rechnung");
        $this->SetKeywords("Rechnung");

        $this->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);

        $this->AddPage();

        $this->setCellPaddings(1, 1, 1, 1);
        $this->setCellMargins(0, 0, 0, 0);
        $this->setMargins(25, 45, 20, true);

        $this->SetFont("helvetica", "", 8);
        $address = "<p>" . $this->companyDetails["companyImprint"] . "</p>";
        $this->writeHTMLCell(0, 10, 25, 20, $address);

        $this->SetFont("helvetica", "", 12);
        $this->fillAddress();

        $this->Image($this->getCompanyLogo(), 120, 22, 60);

        $this->setXY(120, 30);
        $this->setFontStretching(200);
        $this->SetFont("helvetica", "B", 17);
        $this->Cell(60, 20, "RECHNUNG", 0, 0, 'L', 0, '', 2);
        $this->setFontStretching();

        $this->addTableHeader();

        /* iterates over all posten and adds lines */
        $lineheight = 10;
        $posten = $this->invoice->loadPostenFromAuftrag();
        $offset = 100;
        $this->setXY(25, $offset);
        $count = 1;

        foreach ($posten as $p) {
            $this->Cell(10, $lineheight, $count);
            $this->Cell(20, $lineheight, $p->getQuantity());
            $this->Cell(20, $lineheight, $p->getEinheit());

            $height = $this->getStringHeight(70, $p->getDescription());
            $addToOffset = $lineheight;

            if ($p->getOhneBerechnung() == true) {
                $addToOffset = $this->ohneBerechnungBtn($height, $lineheight, $p);
            } else {
                if ($height >= $lineheight) {
                    $this->MultiCell(70, $lineheight, $p->getDescription(), '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
                    $addToOffset = ceil($height);
                } else {
                    $this->Cell(70, $lineheight, $p->getDescription());
                }
            }

            $this->Cell(20, $lineheight, $p->bekommeEinzelPreis_formatted());
            $this->Cell(20, $lineheight, $p->bekommePreis_formatted(), 0, 0, 'R');

            $offset += $addToOffset;
            $this->ln($addToOffset);

            /* 297: Din A4 Seitenhöhe, 25: Abstand von unten für die Fußzeile */
            if ($this->GetY() + $addToOffset >= 297 - 25) {
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

            $this->Cell(50, $lineheight, "");

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
            if ($this->GetY() + $addToOffset >= 297 - 25) {
                $this->AddPage();
                $this->addTableHeader(25);
                $this->ln(10);
            }

            $count++;
        }

        /* 297: Din A4 Seitenhöhe, 25: Abstand von unten für die Fußzeile, 55: bezieht sich auf die Zwischensumme und Rechnungssumme, damit diese immer auf einer Seite stehen */
        if ($this->GetY() + 55 >= 297 - 25) {
            $this->AddPage();
        }

        /* Code für Zwischensumme und Rechnungssumme */
        $summe = $this->order->calcOrderSum();
        $zwischensumme = number_format($summe, 2, ',', '') . ' €';
        $mwst = number_format($summe * 0.19, 2, ',', '') . ' €';
        $rechnungssumme = number_format($summe * 1.19, 2, ',', '') . ' €';

        $this->ln();
        $this->Cell(80, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(60, 10, 'Zwischensumme:', 'T');
        $this->SetFont("helvetica", "", 12);
        $this->Cell(20, 10, $zwischensumme, 'T', 0, 'R');

        $this->ln();
        $this->Cell(80, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(60, 10, '19% MwSt.:', 'B');
        $this->SetFont("helvetica", "", 12);
        $this->Cell(20, 10, $mwst, 'B', 0, 'R');

        $this->ln();
        $this->Cell(80, 10, "");
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(60, 10, 'Rechnungssumme:', 'B');
        $this->SetFont("helvetica", "", 12);
        $this->Cell(20, 10, $rechnungssumme, 'B', 0, 'R');

        $this->ln();
        $this->setCellMargins(0, 1, 0, 0);
        $this->Cell(80, 10, "");
        $this->Cell(60, 10, '', 'T');
        $this->Cell(20, 10, '', 'T');

        /* Code für "Zahlbar sofort ohne weitere Abzüge" */
        $this->ln();
        $this->setCellMargins(0, 0, 0, 0);
        $this->SetFont("helvetica", "", 10);
        $this->Cell(160, 10, "Zahlbar sofort ohne weitere Abzüge.");
    }

    private function addTableHeader($y = 45)
    {
        $this->SetFont("helvetica", "", 12);
        $this->setXY(120, $y);
        $this->Cell(30, 10, "Rechnungs-Nr:");
        $this->Cell(30, 10, InvoiceNumberTracker::peekNextInvoiceNumber(), 0, 0, 'R');
        $this->setXY(120, $y + 6);
        $this->Cell(30, 10, "Auftrags-Nr:");
        $this->Cell(30, 10, $this->order->getAuftragsnummer(), 0, 0, 'R');
        $this->setXY(120, $y + 12);
        $this->Cell(30, 10, "Datum:");
        $this->Cell(30, 10, $this->invoice->getCreationDateUnformatted()->format("d.m.Y"), 0, 0, 'R');
        $this->setXY(120, $y + 18);
        $this->Cell(30, 10, "Kunden-Nr.:");
        $this->Cell(30, 10, $this->customer->getKundennummer(), 0, 0, 'R');
        $this->setXY(120, $y + 24);
        $this->Cell(30, 10, "Seite:");
        $this->Cell(30, 10, $this->getAliasRightShift() . $this->PageNo() . ' von ' . $this->getAliasNbPages(), 0, 0, 'R');

        $this->setXY(25, $y + 45);
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(10, 10, 'Pos.', 'B');
        $this->Cell(20, 10, 'Menge', 'B');
        $this->Cell(20, 10, 'MEH', 'B');
        $this->Cell(70, 10, 'Bezeichnung', 'B');
        $this->Cell(20, 10, 'E-Preis', 'B');
        $this->Cell(20, 10, 'G-Preis', 'B');
        $this->SetFont("helvetica", "", 12);
    }

    private function ohneBerechnungBtn(&$height, &$lineheight, &$p)
    {
        $this->MultiCell(50, $lineheight, $p->getDescription(), '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
        $addToOffset = ceil($height);

        $this->SetFont("helvetica", "", 7);
        $this->MultiCell(20, $lineheight, "Ohne Berechnung", '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
        $this->SetFont("helvetica", "", 12);

        return $addToOffset;
    }

    private function getCompanyLogo(): string
    {
        $image = ClientSettings::getLogo();
        if ($image == "") {
            return "img/default_image.png";
            //return Link::getImageLink("");
        } else {
            return Link::getResourcesLink($image, "upload", false);
        }
    }
}
