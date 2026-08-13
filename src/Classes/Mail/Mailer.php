<?php

namespace Src\Classes\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Src\Classes\Project\CompanyProfile;
use Src\Classes\Project\Settings;
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
        $this->mail->Host = (string) Settings::get("mail.host");
        $this->mail->SMTPAuth = true;
        $this->mail->Username = (string) Settings::get("mail.username");
        $this->mail->Password = (string) Settings::get("mail.password");
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = (int) Settings::get("mail.port");

        $this->mail->setFrom((string) Settings::get("mail.fromAddress"), (string) Settings::get("mail.fromName"));
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $htmlbody
     * @param ?array<string, string> $attachments
     * @param ?string $plainBody
     * @return bool
     */
    public function send(string $to, string $subject, string $htmlbody, ?array $attachments = null, ?string $plainBody = null): bool
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);

            $this->mail->addEmbeddedImage(CompanyProfile::getLogo(), 'logo');
            foreach ($attachments ?? [] as $path => $name) {
                $this->mail->addAttachment($path, $name);
            }
            
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
