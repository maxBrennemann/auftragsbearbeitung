<?php

namespace Classes\Pdf;

use Classes\Project\Config;
use TCPDF;

class PDFGenerator extends TCPDF
{
    /** @var string */
    protected $title;
    protected $fileName;

    protected $companyDetails;

    protected int $pageHeight = 297;
    protected int $footerHeight = 35;
    protected int $topMargin = 25;
    protected int $bottomMargin = 25;

    public function __construct(string $title)
    {
        parent::__construct("p", "mm", "A4");
        $this->title = $title;

        $this->companyDetails = Config::getCompanyDetails();
    }

    public function generate()
    {
    }

    public function generateOutput()
    {
        $this->Output();
    }

    public function saveOutput(): void
    {
        $fileName = $_SERVER["DOCUMENT_ROOT"] . "/generated/" . $this->fileName . ".pdf";

        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $this->Output($fileName, "F");
    }

    private function generateHeader()
    {
    }

    public function Footer()
    {
        $this->SetY(-$this->bottomMargin);
        $this->SetFont('helvetica', 'I', 8);
    }
}
