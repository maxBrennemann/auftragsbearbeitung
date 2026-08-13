<?php

namespace Src\Classes\Pdf\TransactionPdf;

use Src\Classes\Pdf\PDFGenerator;
use Src\Classes\Pdf\PDFTexts;
use Src\Classes\Project\Auftrag;
use Src\Classes\Project\CompanyProfile;
use Src\Classes\Project\Kunde;
use Src\Classes\Project\Leistung;
use Src\Classes\Project\ProduktPosten;
use Src\Classes\Project\Zeit;

class TransactionPDF extends PDFGenerator
{
    /** @var string */
    protected $title;

    /** @var array<string, string> */
    protected array $companyDetails;

    protected Auftrag $order;
    protected Kunde $customer;

    protected int $orderId;
    protected int $addressId = 0;
    protected int $contactId = 0;

    protected int $bottomMargin = 30;
    protected string $type = "transaction";

    public function __construct(string $title, int $orderId)
    {
        parent::__construct($title);
        $this->title = $title;

        $this->orderId = $orderId;
        $this->order = new Auftrag($orderId);

        /* orderId <= 0 means there is no underlying Auftrag yet (e.g. an Angebot) - subclasses are
         * responsible for setting $this->customer themselves in that case */
        if ($orderId > 0) {
            $this->customer = new Kunde($this->order->getKundennummer());
        }

        $this->companyDetails = CompanyProfile::get();
    }

    public function generate(): void
    {
        $this->setPrintHeader(false);

        $this->SetLineStyle([
            "width" => 0.25,
            "color" => [0, 0, 0],
        ]);

        $this->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);

        $this->AddPage();

        $this->setCellPaddings(1, 1, 1, 1);
        $this->setCellMargins(0, 0, 0, 0);
        $this->setMargins(20, 45, 20, true);

        $this->SetFont("helvetica", "", 8);
        $address = "<p>" . $this->companyDetails["companyImprint"] . "</p>";
        $this->writeHTMLCell(0, 10, 20, 44, $address);
    }

    public function save(): void {}

    /**
     * @param array<int, array<mixed>> $altNames
     * @return void
     */
    protected function fillAddress(array $altNames = []): void
    {
        $lineheight = 10;
        $this->setXY(20, 49);

        $firma = $this->customer->getFirmenname();
        $name = $this->customer->getName();
        $hausnr = $this->customer->getHausnummer($this->addressId);
        $strasse = $this->customer->getStrasse($this->addressId);
        $plz = $this->customer->getPostleitzahl($this->addressId);
        $ort = $this->customer->getOrt($this->addressId);

        if (!count($altNames) == 0) {
            foreach ($altNames as $name) {
                $this->Cell(85, $lineheight, $name["text"]);
                $this->ln(8);
            }
        } else {
            if ($firma != "") {
                $this->Cell(85, $lineheight, $firma);
                $this->ln(8);
            }

            if ($this->customer->getNachname() != "" && $this->customer->getVorname() != "") {
                $this->Cell(85, $lineheight, $name);
                $this->ln(8);
            }
        }

        $this->Cell(85, $lineheight, $strasse . " " . $hausnr);
        $this->ln(8);
        $this->Cell(85, $lineheight, $plz . " " . $ort);

        $this->Line(20, 105, 25, 105, [
            "width" => 0.4,
            "color" => [0, 0, 0],
        ]);
    }

    protected function renderItemsTableHeader(int $y, bool $showPrices = true): void
    {
        $this->setXY(20, $y);
        $this->SetFont("helvetica", "B", 12);
        $this->Cell(15, 10, 'Pos.', 'B');
        $this->Cell(20, 10, 'Menge', 'B');
        $this->Cell(20, 10, 'MEH', 'B');
        $this->Cell($showPrices ? 70 : 120, 10, 'Bezeichnung', 'B');

        if ($showPrices) {
            $this->Cell(20, 10, 'E-Preis', 'B');
            $this->Cell(20, 10, 'G-Preis', 'B');
        }

        $this->SetFont("helvetica", "", 12);
    }

    /* returns the consumed line height so callers can track their own page-break offset */
    protected function renderItemRow(Leistung|ProduktPosten|Zeit $p, int $count, bool $showPrices = true): float
    {
        $lineheight = 10;

        $this->Cell(15, $lineheight, (string) $count);
        $this->Cell(20, $lineheight, $p->getQuantityFormatted());
        $this->Cell(20, $lineheight, $p->getEinheit());

        $descriptionWidth = $showPrices ? 70 : 120;
        if ($showPrices && $p->getOhneBerechnung() == true) {
            $descriptionWidth = 50;
        }

        $height = $this->getStringHeight($descriptionWidth, $p->getDescription());
        $addToOffset = $lineheight;

        if ($height >= $lineheight) {
            $this->MultiCell($descriptionWidth, $lineheight, $p->getDescription(), '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
            $addToOffset = (float) ceil($height);
        } else {
            $this->Cell($descriptionWidth, $lineheight, $p->getDescription());
        }

        if ($showPrices) {
            if ($p->getOhneBerechnung() == true) {
                $this->SetFont("helvetica", "", 6);
                $this->Cell(20, $lineheight, "Ohne Berechnung");
                $this->SetFont("helvetica", "", 12);
            }

            $this->Cell(20, $lineheight, $p->bekommeEinzelPreis_formatted());
            $this->Cell(20, $lineheight, $p->bekommePreis_formatted(), 0, 0, 'R');
        }

        $this->ln($addToOffset);

        return $addToOffset;
    }

    protected function getEstimatedFooterHeight(): int
    {
        $texts = PDFTexts::get($this->type);
        $addToOffset = 0;
        $lineheight = 10;
        $this->SetFont("helvetica", "", 10);

        foreach ($texts as $text) {
            $height = $this->getStringHeight(0, $text);

            if ($height >= $lineheight) {
                $addToOffset += (int) ceil($height);
            } else {
                $addToOffset += $lineheight;
            }
        }

        return $this->bottomMargin + $addToOffset;
    }

    public function Footer(): void
    {
        $texts = PDFTexts::get($this->type);
        $addToOffset = 0;
        $lineheight = 10;
        $this->SetFont("helvetica", "", 10);

        foreach ($texts as $text) {
            $height = $this->getStringHeight(0, $text);

            if ($height >= $lineheight) {
                $this->SetY(-$this->bottomMargin - ceil($height));
                $this->MultiCell(0, $lineheight, $text, '', 'L', false, 0, null, null, true, 0, false, true, 0, 'B', false);
                $addToOffset = +ceil($height);
            } else {
                $this->SetY(-$this->bottomMargin - $lineheight);
                $this->Cell(0, $lineheight, $text);
                $addToOffset += $lineheight;
            }

            $this->SetY(-$this->bottomMargin + $addToOffset);
        }

        $this->SetY(-$this->bottomMargin);
        $this->SetFont('helvetica', 'B', 8);

        $this->Cell(0, 00, "Seite " . $this->getAliasNumPage() . "/" . $this->getAliasNbPages(), 0, 1, 'C', false, '', 0, false, 'T', 'M');

        $this->Cell(0, 0, $this->companyDetails["companyImprint"], 0, 1, 'C', false, '', 0, false, 'T', 'M');

        $this->Cell(0, 0, "Tel.: " . $this->companyDetails["companyPhone"] . " USt-ID-Nr. " . $this->companyDetails["companyUstIdNr"], 0, 1, 'C', false, '', 0, false, 'T', 'M');

        $this->Cell(0, 0, "Bankverbindung: " . $this->companyDetails["companyBank"], 0, 1, 'C', false, '', 0, false, 'T', 'M');

        $this->Cell(0, 0, "IBAN: " . $this->companyDetails["companyIban"] . " BIC: " . $this->companyDetails["companyBic"] . " Kontoinhaber: " . $this->companyDetails["companyKontoinhaber"], 0, 1, 'C', false, '', 0, false, 'T', 'M');

        $this->Cell(0, 0, 'Es gelten unsere Allgemeinen Geschäftsbedingungen (siehe ' . $this->companyDetails["companyWebsite"] . ')', 0, 1, 'C', false, '', 0, false, 'T', 'M');

        $this->Cell(0, 0, 'Die Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.', 0, 1, 'C', false, '', 0, false, 'T', 'M');
    }
}
