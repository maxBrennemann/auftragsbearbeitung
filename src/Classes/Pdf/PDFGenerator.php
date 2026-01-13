<?php

namespace Src\Classes\Pdf;

use Src\Classes\Project\CompanyProfile;
use Src\Classes\Project\Config;
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

        $this->companyDetails = CompanyProfile::get();
    }

    public function generate(): void {}

    public function generateOutput(): void
    {
        $this->withPdfSafeErrorHandling(function () {
            header('Content-Type: application/pdf');
            header('X-Content-Type-Options: nosniff');

            $this->Output($this->fileName . ".pdf", "I");
        });
    }

    public function saveOutput(): void
    {
        $filePath = Config::get("paths.generatedDir") . $this->fileName . ".pdf";

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        error_reporting(error_reporting() & ~E_DEPRECATED);

        $this->withPdfSafeErrorHandling(function () use ($filePath) {
            $this->Output($filePath, "F");
        });
    }

    public function Footer(): void
    {
        $this->SetY(-$this->bottomMargin);
        $this->SetFont('helvetica', 'I', 8);
    }

    private function withPdfSafeErrorHandling(callable $fn): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $prevReporting = error_reporting();
        error_reporting($prevReporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        $prevHandler = set_error_handler(
            function (int $severity, string $message, string $file, int $line) use (&$prevHandler): bool {
                if ($severity === E_DEPRECATED || $severity === E_USER_DEPRECATED) {
                    return true;
                }

                if (is_callable($prevHandler)) {
                    return (bool) $prevHandler($severity, $message, $file, $line);
                }

                return false;
            }
        );

        try {
            $fn();
        } finally {
            restore_error_handler();
            error_reporting($prevReporting);
        }
    }
}
