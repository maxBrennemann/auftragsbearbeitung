<?php

namespace Classes\Project\Modules\Pdf;

use Classes\Project\Angebot;

class OfferPDF extends PDFGenerator
{
    private Angebot $offer;
    private int $offerId;
    private int $customerId;

    public function __construct(int $offerId, int $customerId)
    {
        parent::__construct("Angebot " . $offerId);
        $this->offer = new Angebot($offerId, $customerId);
        $this->offerId = $offerId;
        $this->customerId = $customerId;
    }
}
