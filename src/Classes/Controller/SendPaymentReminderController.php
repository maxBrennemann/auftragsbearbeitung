<?php

namespace Src\Classes\Controller;

use Src\Classes\Mail\Mailer;
use Src\Classes\Mail\Templates\PaymentReminderMailTemplate;

class SendPaymentReminderController
{
    /**
     * @param array{email: string, invoiceNumber: int, level: int, attachment?: array<string, string>} $reminderData
     * @return bool
     */
    public static function handle(array $reminderData): bool
    {
        $mailer = new Mailer();
        $template = PaymentReminderMailTemplate::build($reminderData);
        $attachment = $reminderData["attachment"] ?? null;

        return $mailer->send(
            $reminderData["email"],
            $template["subject"],
            $template["html"],
            $attachment,
            $template["plain"]
        );
    }
}
