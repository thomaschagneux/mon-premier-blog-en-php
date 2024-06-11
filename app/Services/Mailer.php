<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Mailer
 * 
 * This class provides functionalities to send emails using PHPMailer.
 */
class Mailer {

    private PHPMailer $mailer;

    /**
     * Constructor to initialize PHPMailer settings.
     */
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host       = 'smtp.gmail.com'; // Set the SMTP server to send through
        $this->mailer->SMTPAuth   = true;               // Enable SMTP authentication
        $this->mailer->Username   = 'thomas.chagneux@greta-cfa-aquitaine.academy'; // SMTP username
        $this->mailer->Password   = '7823Vz64$';    // SMTP password
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption, `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $this->mailer->Port       = 587;                // TCP port to connect to
    }

     /**
     * Sends an email.
     * 
     * @param string $to Recipient email address.
     * @param string $subject Subject of the email.
     * @param string $body Body content of the email.
     * @param string $from Sender email address.
     * @param string $fromName Sender name.
     * 
     * @return string Message indicating success or failure.
     */
    public function sendMail($to, $subject, $body, $from = 'thomas.chagneux@greta-cfa-aquitaine.academy', $fromName = 'Thomas Chagneux') {
        try {
            // Recipients
            $this->mailer->setFrom($from, $fromName);
            $this->mailer->addAddress($to);     // Add a recipient

            // Content
            $this->mailer->isHTML(true);        // Set email format to HTML
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;

            $this->mailer->send();
            return "Message has been sent";
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}";
        }
    }
}
