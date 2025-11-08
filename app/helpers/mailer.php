<?php
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Funkcija za slanje email-a koristeći PHPMailer
 * @param string $to Email primaoca
 * @param string $subject Naslov maila
 * @param string $body HTML sadržaj maila
 * @return bool True ako je uspješno, false ako ne
 */
function send_mail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // SMTP konfiguracija
        $mail->isSMTP();
        $mail->Host = 'smtp.google.com';              // <-- SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'admin@spes.ba';      // <-- SMTP username
        $mail->Password = 'gane atzl tvwy nocy';           // <-- SMTP password
        $mail->SMTPSecure = 'tls';                 // 'ssl' ili 'tls'
        $mail->Port = 587;                         // 465 za SSL, 587 za TLS

        // SSL opcije za shared hosting - FIX ZA CERTIFIKAT PROBLEM
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Sender i recipient
        $mail->setFrom('admin@spes.ba', 'SPES aplikacija');
        $mail->addAddress($to);

        // Sadržaj
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
