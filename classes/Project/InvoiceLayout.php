<?php

namespace Classes\Project;

use MaxBrennemann\PhpUtilities\DBAccess;

class InvoiceLayout
{

    private int $invoiceId;
    private array $layout;

    public function __construct(int $invoiceId)
    {
        $this->invoiceId = $invoiceId;
        $this->getLayoutData();
    }

    private function getLayoutData()
    {
        $query = "SELECT * FROM invoice_layout WHERE invoice_id = :invoiceId ORDER BY position;";
        $data = DBAccess::selectQuery($query, [
            "invoiceId" => $this->invoiceId,
        ]);

        $this->layout = $data;
    }

    public function getLayout(): array
    {
        return $this->layout;
    }

    public function isOrdered(): bool
    {
        return $this->layout == null ? false : true;
    }
}
