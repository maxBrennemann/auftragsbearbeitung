<?php

namespace Classes\Pdf\TransactionPdf;

use Classes\Pdf\PDFGenerator;
use Classes\Pdf\PDFTexts;
use Classes\Project\Auftrag;
use Classes\Project\Config;
use Classes\Project\Kunde;

class TransactionPDF extends PDFGenerator
{
    /** @var string */
    protected $title;

    protected $companyDetails;

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
        $this->customer = new Kunde($this->order->getKundennummer());

        $this->companyDetails = Config::getCompanyDetails();
    }

    public function generate()
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

    public function save() {}

    protected function fillAddress($altNames = [])
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

    protected function getEstimatedFooterHeight()
    {
        $texts = PDFTexts::get($this->type);
        $addToOffset = 0;
        $lineheight = 10;
        $this->SetFont("helvetica", "", 10);

        foreach ($texts as $text) {
            $height = $this->getStringHeight(0, $text);

            if ($height >= $lineheight) {
                $addToOffset = +ceil($height);
            } else {
                $addToOffset += $lineheight;
            }
        }

        return $this->bottomMargin + $addToOffset;
    }

    public function Footer()
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

        $this->Cell(0, 0, "IBAN: " . $this->companyDetails["companyIban"], 0, 1, 'C', false, '', 0, false, 'T', 'M');

        $this->Cell(0, 0, 'Es gelten unsere Allgemeinen Geschäftsbedingungen (siehe ' . $this->companyDetails["companyWebsite"] . ')', 0, 1, 'C', false, '', 0, false, 'T', 'M');

        $this->Cell(0, 0, 'Die Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.', 0, 1, 'C', false, '', 0, false, 'T', 'M');
    }
}
