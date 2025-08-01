<?php

namespace Classes\Pdf\TransactionPdf;

use Classes\Project\Angebot;

class OfferPDF extends TransactionPDF
{
    private Angebot $offer;
    private int $offerId;
    private int $customerId;

    public function __construct(int $offerId, int $customerId)
    {
        parent::__construct("Angebot " . $offerId, 0);
        $this->offer = new Angebot($offerId, $customerId);
        $this->offerId = $offerId;
        $this->customerId = $customerId;
    }
}
