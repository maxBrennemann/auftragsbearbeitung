<?php

namespace Src\Classes\Controller;

use Src\Classes\Mail\Mailer;
use Src\Classes\Mail\Templates\CreditNoteMailTemplate;

class SendCreditNoteController
{
    /**
     * @param array{email: string, creditNumber: int, invoiceNumber: int, attachment?: array<string, string>} $creditNoteData
     * @return bool
     */
    public static function handle(array $creditNoteData): bool
    {
        $mailer = new Mailer();
        $template = CreditNoteMailTemplate::build($creditNoteData);
        $attachment = $creditNoteData["attachment"] ?? null;

        return $mailer->send(
            $creditNoteData["email"],
            $template["subject"],
            $template["html"],
            $attachment,
            $template["plain"]
        );
    }
}
