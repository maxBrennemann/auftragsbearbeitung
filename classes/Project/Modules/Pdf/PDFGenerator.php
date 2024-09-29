<?php

class PDFGenerator extends TCPDF
{

    private String $title;

    function __construct(String $title)
    {
        $this->title = $title;
    }

    public function generate() {}

    public function save() {}

    private function generateHeader() {}

    public function Footer()
    {
        $this->SetY(-25);
        $this->SetFont('helvetica', 'I', 8);

        $this->Cell(0, 00, "Seite " . $this->getAliasNumPage() . "/" . $this->getAliasNbPages(), 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, $_ENV["COMPANY_IMPRINT"], 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, $_ENV["COMPANY_TEL"] . " " . $_ENV["COMPANY_USTIDNR"], 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, $_ENV["COMPANY_BANK"], 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, $_ENV["COMPANY_IBAN"], 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, 'Es gelten unsere Allgemeinen Geschäftsbedingungen (siehe ' . $_ENV["COMPANY_WEBSITE"] . ') | Die Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    }
}
