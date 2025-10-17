<?php

namespace Src\Classes;

class Mailer
{
    public static function sendMail(string $receiver, string $subject, string $message, string $sender): void
    {
        $header  = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";

        $header .= "From: $sender\r\n";
        $header .= "Reply-To: contact@max-website.tk\r\n";
        $header .= "X-Mailer: PHP " . phpversion();

        mail($receiver, $subject, $message, $header);
    }
}
