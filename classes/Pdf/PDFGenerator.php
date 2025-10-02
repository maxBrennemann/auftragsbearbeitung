<?php

namespace Classes\Pdf;

use Classes\Project\Config;
use TCPDF;

class PDFGenerator extends TCPDF
{
    /** @var string */
    protected $title;
    protected string $fileName;

    /** @var array<string, string> */
    protected array $companyDetails;

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

    public function generate(): void
    {
    }

    public function generateOutput(): void
    {
        $this->Output();
    }

    public function saveOutput(): void
    {
        $fileName = $_SERVER["DOCUMENT_ROOT"] . "/storage/generated/" . $this->fileName . ".pdf";

        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $this->Output($fileName, "F");
    }

    public function Footer(): void
    {
        $this->SetY(-$this->bottomMargin);
        $this->SetFont('helvetica', 'I', 8);
    }
}
