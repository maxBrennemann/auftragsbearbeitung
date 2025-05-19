<?php

namespace Classes\Pdf;

use TCPDF;

use Classes\Project\Config;

class PDFGenerator extends TCPDF
{

    /** @var string */
    protected $title;
    protected $fileName;

    protected $companyDetails;

    public function __construct(string $title)
    {
        parent::__construct("p", "mm", "A4");
        $this->title = $title;

        $this->companyDetails = Config::getCompanyDetails();
    }

    public function generate() {}

    public function generateOutput()
    {
        $this->Output();
    }

    public function saveOutput() 
    {
        $fileName = $_SERVER["DOCUMENT_ROOT"] . "/generated/" . $this->fileName . ".pdf";

        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $this->Output($fileName, "F");
    }

    private function generateHeader() {}

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
