<?php

namespace Src\Classes\Project;

use Src\Classes\Pdf\TransactionPdf\DeliveryNotePDF;
use MaxBrennemann\PhpUtilities\Tools;

class DeliveryNote
{
    public static function getPDF(): void
    {
        $orderId = (int) Tools::get("id");
        $deliveryNote = new DeliveryNotePDF($orderId);

        $deliveryNote->generate();
        $deliveryNote->generateOutput($deliveryNote->getTitle());
    }
}
