<?php

namespace Classes\Project\Modules\Pdf\TransactionPdf;

use Classes\Project\Modules\Pdf\PDFGenerator;

use Classes\Project\Config;
use Classes\Project\Auftrag;
use Classes\Project\Kunde;

class TransactionPDF extends PDFGenerator
{

    /** @var string */
    protected $title;

    protected $companyDetails;

    protected Auftrag $order;
    protected Kunde $customer;

    protected int $orderId;
    protected int $invoiceId;
    protected int $addressId = 0;
    protected int $contactId = 0;

    public function __construct(string $title, int $orderId)
    {
        parent::__construct("p", "mm", "A4");
        $this->title = $title;

        $this->orderId = $orderId;
        $this->order = new Auftrag($orderId);
        $this->customer = new Kunde($this->order->getKundennummer());

        $this->companyDetails = Config::getCompanyDetails();
    }

    public function generate() {}

    public function save() {}

    private function generateHeader() {}

    protected function fillAddress()
    {
        $lineheight = 10;
        $this->setXY(25, 25);

        $firma = $this->customer->getFirmenname();
        $name = $this->customer->getName();
        $hausnr = $this->customer->getHausnummer($this->addressId);
        $strasse = $this->customer->getStrasse($this->addressId);
        $plz = $this->customer->getPostleitzahl($this->addressId);
        $ort = $this->customer->getOrt($this->addressId);

        if ($firma != null || $firma != "") {
            $this->Cell(85, $lineheight, $firma);
            $this->ln(5);
        }
        if ($this->customer->getNachname() != "" && $this->customer->getVorname() != "") {
            $this->Cell(85, $lineheight, $name);
            $this->ln(5);
        }
        $this->Cell(85, $lineheight, $strasse . " " . $hausnr);
        $this->ln(5);
        $this->Cell(85, $lineheight, $plz . " " . $ort);
    }

    public function Footer()
    {
        $this->SetY(-25);
        $this->SetFont('helvetica', 'I', 8);

        $this->Cell(0, 00, "Seite " . $this->getAliasNumPage() . "/" . $this->getAliasNbPages(), 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, $this->companyDetails["companyImprint"], 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, $this->companyDetails["companyPhone"] . " " . $this->companyDetails["companyUstIdNr"], 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, $this->companyDetails["companyBank"], 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, $this->companyDetails["companyIban"], 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, 'Es gelten unsere Allgemeinen Geschäftsbedingungen (siehe ' . $this->companyDetails["companyWebsite"] . ') | Die Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    }
}
