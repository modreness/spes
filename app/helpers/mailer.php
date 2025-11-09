<?php
// app/helpers/mailer.php - POJEDNOSTAVLJENI SA KALENDAR DUGMADIMA

require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function send_mail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // SMTP konfiguracija
        $mail->isSMTP();
        $mail->Host = 'mail.spes.ba';              // <-- SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'web@spes.ba';      // <-- SMTP username
        $mail->Password = 'uS+[^G]QeWLll;^!';           // <-- SMTP password
        $mail->SMTPSecure = 'ssl';                 // 'ssl' ili 'tls'
        $mail->Port = 465;                         // 465 za SSL, 587 za TLS

        /*
        // SSL opcije za shared hosting - FIX ZA CERTIFIKAT PROBLEM
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        */

        $mail->setFrom('admin@spes.ba', 'SPES aplikacija');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Mail Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Generiraj Google Calendar link
 */
function generate_google_calendar_link($termin_data) {
    $datum_vrijeme = $termin_data['datum_vrijeme'];
    $start_time = strtotime($datum_vrijeme);
    $end_time = $start_time + (60 * 60); // +1 sat
    
    // Google Calendar format
    $start_google = gmdate('Ymd\THis\Z', $start_time);
    $end_google = gmdate('Ymd\THis\Z', $end_time);
    
    $title = urlencode("Terapeutski termin - {$termin_data['usluga_naziv']}");
    $details = urlencode("Pacijent: {$termin_data['pacijent_ime']} {$termin_data['pacijent_prezime']}\nTerapeut: {$termin_data['terapeut_ime']} {$termin_data['terapeut_prezime']}\nUsluga: {$termin_data['usluga_naziv']}");
    $location = urlencode("SPES Fizioterapija, Sarajevo");
    
    return "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$start_google}/{$end_google}&details={$details}&location={$location}&sf=true&output=xml";
}

/**
 * Generiraj Outlook Calendar link
 */
function generate_outlook_calendar_link($termin_data) {
    $datum_vrijeme = $termin_data['datum_vrijeme'];
    $start_time = strtotime($datum_vrijeme);
    $end_time = $start_time + (60 * 60);
    
    // Outlook format
    $start_outlook = gmdate('Y-m-d\TH:i:s.000\Z', $start_time);
    $end_outlook = gmdate('Y-m-d\TH:i:s.000\Z', $end_time);
    
    $title = urlencode("Terapeutski termin - {$termin_data['usluga_naziv']}");
    $body = urlencode("Pacijent: {$termin_data['pacijent_ime']} {$termin_data['pacijent_prezime']}\nTerapeut: {$termin_data['terapeut_ime']} {$termin_data['terapeut_prezime']}\nUsluga: {$termin_data['usluga_naziv']}");
    $location = urlencode("SPES Fizioterapija, Sarajevo");
    
    return "https://outlook.live.com/calendar/0/deeplink/compose?subject={$title}&startdt={$start_outlook}&enddt={$end_outlook}&body={$body}&location={$location}";
}

/**
 * Generiraj kalendar dugmad HTML
 */
function generate_calendar_buttons($termin_data) {
    $google_link = generate_google_calendar_link($termin_data);
    $outlook_link = generate_outlook_calendar_link($termin_data);
    
    $html = '
    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; border: 2px dashed #ddd;">
        <h4 style="margin: 0 0 15px 0; color: #333; font-size: 16px;">📅 Dodaj u kalendar</h4>
        
        <div style="display: inline-block; margin: 0 auto;">
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="padding: 0 8px 0 0;">
                        <a href="' . $google_link . '" target="_blank" 
                           style="background: #4285f4; color: white !important; padding: 12px 20px; text-decoration: none; border-radius: 6px; display: inline-block; font-size: 14px; font-weight: bold; text-align: center; min-width: 140px;">
                           📅 Google Calendar
                        </a>
                    </td>
                    <td style="padding: 0 0 0 8px;">
                        <a href="' . $outlook_link . '" target="_blank"
                           style="background: #0078d4; color: white !important; padding: 12px 20px; text-decoration: none; border-radius: 6px; display: inline-block; font-size: 14px; font-weight: bold; text-align: center; min-width: 140px;">
                           📅 Outlook
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        
        <p style="margin: 12px 0 0 0; font-size: 12px; color: #666;">
            Kliknite da automatski dodate termin u vaš kalendar
        </p>
    </div>';
    
    return $html;
}

/**
 * Pošalji email za novi termin SA KALENDAR DUGMADIMA
 */
function send_appointment_email($email_data, $termin_data, $is_patient = false) {
    if (empty($email_data['email'])) {
        return false;
    }
    
    $datum_format = date('d.m.Y', strtotime($termin_data['datum']));
    $vrijeme_format = date('H:i', strtotime($termin_data['datum'] . ' ' . $termin_data['vrijeme']));
    $paket_info = $termin_data['iz_paketa'] ? " (plaćen iz paketa)" : "";
    
    // Generiraj kalendar dugmad
    $calendar_buttons = generate_calendar_buttons($termin_data);
    
    if ($is_patient) {
        // EMAIL ZA PACIJENTA
        $subject = "Potvrda termina - " . $datum_format . " u " . $vrijeme_format;
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
        
        <h3 style='color: #2196f3; border-bottom: 2px solid #2196f3; padding-bottom: 10px;'>
            👋 Poštovani/a {$email_data['ime']} {$email_data['prezime']},
        </h3>
        
        <p style='font-size: 16px; line-height: 1.6;'>Vaš termin je uspješno zakazan:</p>
        
        <div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #4caf50;'>
            <table style='width: 100%; font-size: 15px; line-height: 1.8;'>
                <tr><td style='width: 100px; font-weight: bold; color: #333;'>📅 Datum:</td><td>{$datum_format}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>⏰ Vrijeme:</td><td>{$vrijeme_format}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>👩‍⚕️ Terapeut:</td><td>dr. {$termin_data['terapeut_ime']} {$termin_data['terapeut_prezime']}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>💊 Usluga:</td><td>{$termin_data['usluga_naziv']}{$paket_info}</td></tr>
                " . (!empty($termin_data['napomena']) ? "<tr><td style='font-weight: bold; color: #333;'>📝 Napomena:</td><td>" . htmlspecialchars($termin_data['napomena']) . "</td></tr>" : "") . "
            </table>
        </div>
        
        {$calendar_buttons}
        
        <div style='background: #fff3e0; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #ff9800;'>
            <p style='margin: 0 0 10px 0; font-weight: bold; color: #333;'>⚠️ Važno:</p>
            <ul style='margin: 0; padding-left: 20px; line-height: 1.6;'>
                <li>Dođite 10 minuta prije termina</li>
                <li>Ponesite ličnu kartu i zdravstvenu knjižicu</li>
                <li>Za izmjene kontaktirajte recepciju</li>
            </ul>
        </div>
        
        <p style='font-size: 16px; color: #4caf50; font-weight: bold; text-align: center; margin: 25px 0;'>
            💚 Hvala što ste odabrali SPES!
        </p>
        
        <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
        <p style='font-size: 12px; color: #666; text-align: center; margin: 0;'>
            Ova poruka je automatski generirana iz SPES aplikacije.
        </p>
        
        </div>
        ";
        
    } else {
        // EMAIL ZA TERAPEUTA
        $subject = "Novi termin zakazan - " . $datum_format . " u " . $vrijeme_format;
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
        
        <h3 style='color: #2196f3; border-bottom: 2px solid #2196f3; padding-bottom: 10px;'>
            👨‍⚕️ Poštovani dr. {$email_data['ime']} {$email_data['prezime']},
        </h3>
        
        <p style='font-size: 16px; line-height: 1.6;'>Zakazan je novi termin:</p>
        
        <div style='background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #2196f3;'>
            <table style='width: 100%; font-size: 15px; line-height: 1.8;'>
                <tr><td style='width: 100px; font-weight: bold; color: #333;'>👤 Pacijent:</td><td>{$termin_data['pacijent_ime']} {$termin_data['pacijent_prezime']}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>📅 Datum:</td><td>{$datum_format}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>⏰ Vrijeme:</td><td>{$vrijeme_format}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>💊 Usluga:</td><td>{$termin_data['usluga_naziv']}{$paket_info}</td></tr>
                " . (!empty($termin_data['napomena']) ? "<tr><td style='font-weight: bold; color: #333;'>📝 Napomena:</td><td>" . htmlspecialchars($termin_data['napomena']) . "</td></tr>" : "") . "
            </table>
        </div>
        
        {$calendar_buttons}
        
        <p style='font-size: 16px; line-height: 1.6; text-align: center; color: #555; margin: 25px 0;'>
            📋 Molimo potvrdite dolazak u aplikaciji.
        </p>
        
        <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
        <p style='font-size: 12px; color: #666; text-align: center; margin: 0;'>
            Ova poruka je automatski generirana iz SPES aplikacije.
        </p>
        
        </div>
        ";
    }
    
    return send_mail($email_data['email'], $subject, $body);
}

/**
 * Pošalji email za promjenu status termina
 */
function send_status_change_email($email_data, $termin_data, $old_status, $new_status, $is_patient = false) {
    if (empty($email_data['email'])) {
        return false;
    }
    
    $datum_format = date('d.m.Y', strtotime($termin_data['datum_vrijeme']));
    $vrijeme_format = date('H:i', strtotime($termin_data['datum_vrijeme']));
    
    $status_labels = [
        'zakazan' => 'Zakazan',
        'obavljeno' => 'Obavljeno',
        'otkazano' => 'Otkazano',
        'nije_dosao' => 'Nije došao'
    ];
    
    $old_status_label = $status_labels[$old_status] ?? $old_status;
    $new_status_label = $status_labels[$new_status] ?? $new_status;
    
    // Determini boju i ikonu na osnovu status
    $status_style = [
        'obavljeno' => ['color' => '#4caf50', 'icon' => '✅', 'bg' => '#e8f5e8'],
        'otkazano' => ['color' => '#f44336', 'icon' => '❌', 'bg' => '#ffebee'],
        'nije_dosao' => ['color' => '#ff9800', 'icon' => '⚠️', 'bg' => '#fff3e0'],
        'zakazan' => ['color' => '#2196f3', 'icon' => '📅', 'bg' => '#e3f2fd']
    ];
    
    $style = $status_style[$new_status] ?? ['color' => '#666', 'icon' => '📋', 'bg' => '#f5f5f5'];
    
    if ($is_patient) {
        // EMAIL ZA PACIJENTA
        $subject = "Status termina: " . $new_status_label;
        
        $status_message = '';
        switch ($new_status) {
            case 'obavljeno':
                $status_message = "
                <div style='background: #e8f5e8; padding: 15px; border-radius: 6px; margin: 20px 0; text-align: center;'>
                    <p style='margin: 0; color: #4caf50; font-size: 18px; font-weight: bold;'>✅ Vaš termin je uspješno obavljeno!</p>
                    <p style='margin: 10px 0 0 0; color: #333;'>Hvala što ste došli. Nadamo se da ste zadovoljni uslugom.</p>
                </div>";
                break;
            case 'otkazano':
                $status_message = "
                <div style='background: #ffebee; padding: 15px; border-radius: 6px; margin: 20px 0; text-align: center;'>
                    <p style='margin: 0; color: #f44336; font-size: 18px; font-weight: bold;'>❌ Vaš termin je otkazan</p>
                    <p style='margin: 10px 0 0 0; color: #333;'>Za nova zakazivanja kontaktirajte recepciju.</p>
                </div>";
                break;
            case 'nije_dosao':
                $status_message = "
                <div style='background: #fff3e0; padding: 15px; border-radius: 6px; margin: 20px 0; text-align: center;'>
                    <p style='margin: 0; color: #ff9800; font-size: 18px; font-weight: bold;'>⚠️ Niste došli na zakazan termin</p>
                    <p style='margin: 10px 0 0 0; color: #333;'>Za nova zakazivanja kontaktirajte recepciju.</p>
                </div>";
                break;
        }
        
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
        
        <h3 style='color: {$style['color']}; border-bottom: 2px solid {$style['color']}; padding-bottom: 10px;'>
            {$style['icon']} Poštovani/a {$email_data['ime']} {$email_data['prezime']},
        </h3>
        
        <p style='font-size: 16px; line-height: 1.6;'>Obavještavamo vas o promjeni status vašeg termina:</p>
        
        <div style='background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;'>
            <table style='width: 100%; font-size: 15px; line-height: 1.8;'>
                <tr><td style='width: 100px; font-weight: bold; color: #333;'>📅 Datum:</td><td>{$datum_format}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>⏰ Vrijeme:</td><td>{$vrijeme_format}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>👩‍⚕️ Terapeut:</td><td>dr. {$termin_data['terapeut_ime']} {$termin_data['terapeut_prezime']}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>💊 Usluga:</td><td>{$termin_data['usluga_naziv']}</td></tr>
            </table>
        </div>
        
        <div style='background: {$style['bg']}; border-left: 6px solid {$style['color']}; padding: 20px; margin: 20px 0; text-align: center;'>
            <p style='margin: 0; font-size: 18px; font-weight: bold; color: {$style['color']};'>
                Status: {$old_status_label} → {$new_status_label}
            </p>
        </div>
        
        {$status_message}
        
        <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
        <p style='font-size: 12px; color: #666; text-align: center; margin: 0;'>
            Ova poruka je automatski generirana iz SPES aplikacije.
        </p>
        
        </div>
        ";
        
    } else {
        // EMAIL ZA TERAPEUTA  
        $subject = "Status termina promenjen - " . $datum_format;
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
        
        <h3 style='color: {$style['color']}; border-bottom: 2px solid {$style['color']}; padding-bottom: 10px;'>
            👨‍⚕️ Poštovani dr. {$email_data['ime']} {$email_data['prezime']},
        </h3>
        
        <p style='font-size: 16px; line-height: 1.6;'>Status termina je promenjen:</p>
        
        <div style='background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;'>
            <table style='width: 100%; font-size: 15px; line-height: 1.8;'>
                <tr><td style='width: 100px; font-weight: bold; color: #333;'>👤 Pacijent:</td><td>{$termin_data['pacijent_ime']} {$termin_data['pacijent_prezime']}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>📅 Datum:</td><td>{$datum_format}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>⏰ Vrijeme:</td><td>{$vrijeme_format}</td></tr>
                <tr><td style='font-weight: bold; color: #333;'>💊 Usluga:</td><td>{$termin_data['usluga_naziv']}</td></tr>
            </table>
        </div>
        
        <div style='background: {$style['bg']}; border-left: 6px solid {$style['color']}; padding: 20px; margin: 20px 0; text-align: center;'>
            <p style='margin: 0; font-size: 18px; font-weight: bold; color: {$style['color']};'>
                Status: {$old_status_label} → {$new_status_label}
            </p>
        </div>
        
        <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
        <p style='font-size: 12px; color: #666; text-align: center; margin: 0;'>
            Ova poruka je automatski generirana iz SPES aplikacije.
        </p>
        
        </div>
        ";
    }
    
    return send_mail($email_data['email'], $subject, $body);
}