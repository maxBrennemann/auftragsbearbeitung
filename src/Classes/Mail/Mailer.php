<?php

namespace Src\Classes\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Src\Classes\Project\CompanyProfile;
use Src\Classes\Protocol;

class Mailer
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->configure();
    }

    private function configure(): void
    {
        $this->mail->isSMTP();
        $this->mail->Host = $_ENV["MAIL_HOST"] ?? "";
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV["MAIL_USERNAME"];
        $this->mail->Password = $_ENV["MAIL_PASSWORD"];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = $_ENV["MAIL_PORT"];

        $this->mail->setFrom($_ENV["MAIL_FROM"], $_ENV["MAIL_FROM_NAME"]);
    }

    public function send(string $to, string $subject, string $htmlbody, ?string $plainBody): bool
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);

            $this->mail->addEmbeddedImage(CompanyProfile::getLogo(), 'logo');
            $this->mail->addAttachment(ROOT . "storage/generated/Rechnung_3212.pdf", 'Rechnung_3212.pdf');
            
            $this->mail->Subject = $subject;
            $this->mail->Body = $htmlbody;
            $this->mail->AltBody = $plainBody ?? strip_tags($htmlbody);
            $this->mail->send();

            return true;
        } catch (Exception $e) {

            Protocol::write("Mail error: {$e->getMessage()}");
            return false;
        }
    }
}
