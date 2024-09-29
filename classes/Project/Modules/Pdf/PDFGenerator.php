<?php

require_once('vendor/autoload.php');

class PDFGenerator extends TCPDF
{

    private String $title;

    /* company info from global variables */
    private static String $companyBank = COMPANY_BANK;
    private static String $companyIban = COMPANY_IBAN;
    private static String $companyTele = COMPANY_TEL;
    private static String $companyImpr = COMPANY_IMPRINT;
    private static String $companyUstn = COMPANY_USTIDNR;
    private static String $companyWebp = COMPANY_WEBSITE;

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
        $this->Cell(0, 0, self::$companyImpr, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, self::$companyTele . " " . self::$companyUstn, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, self::$companyBank, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, self::$companyIban, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 0, 'Es gelten unsere Allgemeinen Geschäftsbedingungen (siehe ' . self::$companyWebp . ') | Die Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    }
}
