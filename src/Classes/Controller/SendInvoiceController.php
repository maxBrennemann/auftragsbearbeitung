<?php

namespace Src\Classes\Controller;

use Src\Classes\Mail\Mailer;
use Src\Classes\Mail\Templates\InvoiceMailTemplate;

class SendInvoiceController
{
    /**
     * @param array<string, string> $invoiceData
     * @return bool
     */
    public static function handle(array $invoiceData): bool
    {
        $mailer = new Mailer();
        $template = InvoiceMailTemplate::build($invoiceData);

        return $mailer->send(
            $invoiceData["email"],
            $template["subject"],
            $template["html"],
            [],
            $template["plain"]
        );
    }
}
