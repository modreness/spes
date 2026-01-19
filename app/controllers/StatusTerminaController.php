<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

// AŽURIRANO - dodaj terapeuta u dozvoljene uloge
if (!in_array($user['uloga'], ['admin', 'recepcioner', 'terapeut'])) {
    header('Location: /dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $termin_id = $_POST['id'] ?? null;
    $novi_status = $_POST['status'] ?? null;
    
    if (!$termin_id || !$novi_status) {
        header('Location: /termini/lista?msg=greska');
        exit;
    }
    
    // Validacija dozvoljenih statusa
    $dozvoljeni_statusi = ['zakazan', 'otkazan', 'obavljen', 'slobodan'];
    if (!in_array($novi_status, $dozvoljeni_statusi)) {
        header('Location: /termini/lista?msg=greska');
        exit;
    }
    
    try {
        // Provjeri da li termin postoji i dohvati stari status
        $stmt = $pdo->prepare("
            SELECT t.id, t.status, t.terapeut_id, t.pacijent_id, t.usluga_id, t.datum_vrijeme, t.napomena,
                   DATE(t.datum_vrijeme) as datum, TIME(t.datum_vrijeme) as vrijeme
            FROM termini t 
            WHERE t.id = ?
        ");
        $stmt->execute([$termin_id]);
        $termin = $stmt->fetch();
        
        if (!$termin) {
            header('Location: /termini/lista?msg=greska');
            exit;
        }
        
        // 👉 Sačuvaj stari status za email poređenje
        $stari_status = $termin['status'];
        
        // DODATNA PROVJERA - terapeut može mijenjati samo svoje termine
        if ($user['uloga'] === 'terapeut' && $termin['terapeut_id'] != $user['id']) {
            header('Location: /termini/lista?msg=greska');
            exit;
        }
        
        // Ažuriraj status
        $stmt = $pdo->prepare("UPDATE termini SET status = ? WHERE id = ?");
        $stmt->execute([$novi_status, $termin_id]);
        
        // ✉️ SLANJE EMAIL NOTIFIKACIJA ZA PROMENU STATUSA
        if ($stari_status !== $novi_status && in_array($novi_status, ['obavljen', 'otkazan'])) {
            require_once __DIR__ . '/../helpers/mailer.php';
            
            // Dohvati email podatke
            $stmt = $pdo->prepare("
                SELECT 
                    t.email as terapeut_email, t.ime as terapeut_ime, t.prezime as terapeut_prezime,
                    p.email as pacijent_email, p.ime as pacijent_ime, p.prezime as pacijent_prezime,
                    c.naziv as usluga_naziv
                FROM users t
                CROSS JOIN users p
                LEFT JOIN cjenovnik c ON c.id = ?
                WHERE t.id = ? AND p.id = ?
            ");
            $stmt->execute([$termin['usluga_id'], $termin['terapeut_id'], $termin['pacijent_id']]);
            $email_data = $stmt->fetch();
            
            if ($email_data) {
                $datum_format = date('d.m.Y', strtotime($termin['datum']));
                $vrijeme_format = date('H:i', strtotime($termin['datum'] . ' ' . $termin['vrijeme']));
                
                // Status labeli (primijetiti da se ovdje koristi 'obavljen' umjesto 'obavljeno')
                $status_labels = [
                    'zakazan' => 'Zakazan',
                    'obavljen' => 'Obavljeno',  // ← Mapira 'obavljen' na 'Obavljeno' 
                    'otkazan' => 'Otkazano',
                    'slobodan' => 'Slobodan'
                ];
                
                $old_status_label = $status_labels[$stari_status] ?? $stari_status;
                $new_status_label = $status_labels[$novi_status] ?? $novi_status;
                
                // 📧 Email terapeutu
                if (!empty($email_data['terapeut_email'])) {
                    $subject_terapeut = "Status termina promijenjen - " . $datum_format;
                    $body_terapeut = "
                    <h3>Poštovani dr. {$email_data['terapeut_ime']} {$email_data['terapeut_prezime']},</h3>
                    
                    <p>Status termina je promijenjen:</p>
                    
                    <ul>
                        <li><strong>Pacijent:</strong> {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']}</li>
                        <li><strong>Datum:</strong> {$datum_format}</li>
                        <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                        <li><strong>Usluga:</strong> {$email_data['usluga_naziv']}</li>
                        <li><strong>Status:</strong> {$old_status_label} → {$new_status_label}</li>
                        " . (!empty($termin['napomena']) ? "<li><strong>Napomena:</strong> " . htmlspecialchars($termin['napomena']) . "</li>" : "") . "
                    </ul>
                    
                    <hr>
                    <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
                    ";
                    
                    $mail_sent_terapeut = send_mail($email_data['terapeut_email'], $subject_terapeut, $body_terapeut);
                    if (!$mail_sent_terapeut) {
                        error_log("Greška pri slanju status email-a terapeutu: " . $email_data['terapeut_email']);
                    }
                }
                
                // 📧 Email pacijentu (samo ako ima email)
                if (!empty($email_data['pacijent_email'])) {
                    $subject_pacijent = "Status termina: " . $new_status_label;
                    
                    // Različite poruke za različite statusse
                    $status_message = '';
                    if ($novi_status === 'obavljen') {  // ← Primijetiti 'obavljen'
                        $status_message = "<p><strong>Vaš termin je uspješno obavljeno.</strong> Hvala što ste došli!</p>";
                    } elseif ($novi_status === 'otkazan') {
                        $status_message = "<p><strong>Vaš termin je otkazan.</strong> Za nova zakazivanja kontaktirajte recepciju.</p>";
                    }
                    
                    $body_pacijent = "
                    <h3>Poštovani/a {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']},</h3>
                    
                    <p>Obavještavamo vas o promjeni status vašeg termina:</p>
                    
                    <ul>
                        <li><strong>Datum:</strong> {$datum_format}</li>
                        <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                        <li><strong>Terapeut:</strong> dr. {$email_data['terapeut_ime']} {$email_data['terapeut_prezime']}</li>
                        <li><strong>Usluga:</strong> {$email_data['usluga_naziv']}</li>
                        <li><strong>Status:</strong> {$old_status_label} → {$new_status_label}</li>
                    </ul>
                    
                    {$status_message}
                    
                    <hr>
                    <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
                    ";
                    
                    $mail_sent_pacijent = send_mail($email_data['pacijent_email'], $subject_pacijent, $body_pacijent);
                    if (!$mail_sent_pacijent) {
                        error_log("Greška pri slanju status email-a pacijentu: " . $email_data['pacijent_email']);
                    }
                } else {
                    error_log("Pacijent nema email adresu - preskačem slanje status email-a: " . $email_data['pacijent_ime'] . " " . $email_data['pacijent_prezime']);
                }
            }
        }
        
        // Redirekt sa porukom
        $msg = $novi_status === 'otkazan' ? 'otkazan' : 
               ($novi_status === 'obavljen' ? 'obavljen' : 'azuriran');
        
        header("Location: /termini/lista?msg=$msg");
        exit;
        
    } catch (PDOException $e) {
        error_log("Greška pri ažuriranju statusa termina: " . $e->getMessage());
        header('Location: /termini/lista?msg=greska');
        exit;
    }
}

// Ako nije POST, redirekt na listu
header('Location: /termini/lista');
exit;
?>