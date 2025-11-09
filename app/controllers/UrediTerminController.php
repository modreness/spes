<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

$errors = [];
$termin_id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$termin_id) {
    header('Location: /termini');
    exit;
}

// Dohvati termin
try {
    $stmt = $pdo->prepare("
        SELECT t.*, 
               CONCAT(u_pacijent.ime, ' ', u_pacijent.prezime) as pacijent_ime_display,
               CONCAT(u_terapeut.ime, ' ', u_terapeut.prezime) as terapeut_ime_display,
               u_pacijent.email as pacijent_email,
               u_terapeut.email as terapeut_email,
               c.naziv as usluga_naziv
        FROM termini t
        LEFT JOIN users u_pacijent ON t.pacijent_id = u_pacijent.id
        LEFT JOIN users u_terapeut ON t.terapeut_id = u_terapeut.id
        LEFT JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE t.id = ?
    ");
    $stmt->execute([$termin_id]);
    $termin = $stmt->fetch();
    
    if (!$termin) {
        header('Location: /termini?msg=greska');
        exit;
    }
    
    // 👉 Sačuvaj trenutni status za poređenje
    $old_status = $termin['status'];
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju termina: " . $e->getMessage());
    header('Location: /termini?msg=greska');
    exit;
}

// Dohvati podatke za dropdowne
try {
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'pacijent' AND aktivan = 1 ORDER BY ime, prezime");
    $stmt->execute();
    $pacijenti = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' AND aktivan = 1 ORDER BY ime, prezime");
    $stmt->execute();
    $terapeuti = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT c.*, k.naziv as kategorija_naziv 
        FROM cjenovnik c 
        LEFT JOIN kategorije_usluga k ON c.kategorija_id = k.id 
        WHERE c.aktivan = 1 
        ORDER BY k.naziv, c.naziv
    ");
    $stmt->execute();
    $usluge = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju podataka: " . $e->getMessage());
    $pacijenti = [];
    $terapeuti = [];
    $usluge = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pacijent_id = $_POST['pacijent_id'] ?? '';
    $terapeut_id = $_POST['terapeut_id'] ?? '';
    $usluga_id = $_POST['usluga_id'] ?? '';
    $datum = $_POST['datum'] ?? '';
    $vrijeme = $_POST['vrijeme'] ?? '';
    $status = $_POST['status'] ?? '';
    $napomena = trim($_POST['napomena'] ?? '');
    
    // Validacija
    if (empty($pacijent_id)) {
        $errors[] = 'Pacijent je obavezan.';
    }
    
    if (empty($terapeut_id)) {
        $errors[] = 'Terapeut je obavezan.';
    }
    
    if (empty($usluga_id)) {
        $errors[] = 'Usluga je obavezna.';
    }
    
    if (empty($datum)) {
        $errors[] = 'Datum je obavezan.';
    }
    
    if (empty($vrijeme)) {
        $errors[] = 'Vrijeme je obavezno.';
    }
    
    if (empty($status)) {
        $errors[] = 'Status je obavezan.';
    }
    
    // Kombinuj datum i vrijeme
    $datum_vrijeme = '';
    if (!empty($datum) && !empty($vrijeme)) {
        $datum_vrijeme = $datum . ' ' . $vrijeme;
    }
    
    // Provjeri koliziju termina (osim trenutnog)
    if (empty($errors) && !empty($datum_vrijeme)) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM termini 
            WHERE terapeut_id = ? 
            AND datum_vrijeme = ? 
            AND status IN ('zakazan', 'slobodan')
            AND id != ?
        ");
        $stmt->execute([$terapeut_id, $datum_vrijeme, $termin_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Terapeut već ima zakazan termin u to vrijeme.';
        }
    }
    
    // Ažuriraj termin
    if (empty($errors)) {
        try {
            // 👉 VAŽNO: Učitaj podatke o terapeutu i pacijentu za zamrzavanje
            $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
            $stmt->execute([$terapeut_id]);
            $terapeut = $stmt->fetch();
            
            $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
            $stmt->execute([$pacijent_id]);
            $pacijent = $stmt->fetch();
            
            // Ažuriraj sa zamrznutim podacima
            $stmt = $pdo->prepare("
                UPDATE termini 
                SET pacijent_id = ?, 
                    pacijent_ime = ?,
                    pacijent_prezime = ?,
                    terapeut_id = ?, 
                    terapeut_ime = ?,
                    terapeut_prezime = ?,
                    usluga_id = ?, 
                    datum_vrijeme = ?, 
                    status = ?, 
                    napomena = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $pacijent_id,
                $pacijent['ime'],           // 👈 Ažuriraj zamrznuto ime pacijenta
                $pacijent['prezime'],       // 👈 Ažuriraj zamrznuto prezime pacijenta
                $terapeut_id,
                $terapeut['ime'],           // 👈 Ažuriraj zamrznuto ime terapeuta
                $terapeut['prezime'],       // 👈 Ažuriraj zamrznuto prezime terapeuta
                $usluga_id, 
                $datum_vrijeme, 
                $status, 
                $napomena, 
                $termin_id
            ]);
            
            // ✉️ SLANJE EMAIL NOTIFIKACIJA ZA PROMENU STATUSA
            if ($old_status !== $status && in_array($status, ['obavljen', 'otkazan'])) {
                require_once __DIR__ . '/../helpers/mailer.php';
                
                // Dohvati fresh email podatke
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
                $stmt->execute([$usluga_id, $terapeut_id, $pacijent_id]);
                $email_data = $stmt->fetch();
                
                if ($email_data) {
                    $datum_format = date('d.m.Y', strtotime($datum));
                    $vrijeme_format = date('H:i', strtotime($datum . ' ' . $vrijeme));
                    
                    // Status labeli
                    $status_labels = [
                        'zakazan' => 'Zakazan',
                        'obavljen' => 'Obavljen',
                        'otkazan' => 'Otkazan',
                        'slobodan' => 'Slobodan'
                    ];
                    
                    $old_status_label = $status_labels[$old_status] ?? $old_status;
                    $new_status_label = $status_labels[$status] ?? $status;
                    
                    // 📧 Email terapeutu
                    if (!empty($email_data['terapeut_email'])) {
                        $subject_terapeut = "Status termina promenjen - " . $datum_format;
                        $body_terapeut = "
                        <h3>Poštovani dr. {$email_data['terapeut_ime']} {$email_data['terapeut_prezime']},</h3>
                        
                        <p>Status termina je promenjen:</p>
                        
                        <ul>
                            <li><strong>Pacijent:</strong> {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']}</li>
                            <li><strong>Datum:</strong> {$datum_format}</li>
                            <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                            <li><strong>Usluga:</strong> {$email_data['usluga_naziv']}</li>
                            <li><strong>Status:</strong> {$old_status_label} → {$new_status_label}</li>
                            " . (!empty($napomena) ? "<li><strong>Napomena:</strong> " . htmlspecialchars($napomena) . "</li>" : "") . "
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
                        if ($status === 'obavljen') {
                            $status_message = "<p><strong>Vaš termin je uspješno obavljen.</strong> Hvala što ste došli!</p>";
                        } elseif ($status === 'otkazan') {
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
            
            header('Location: /termini?msg=azuriran');
            exit;
            
        } catch (PDOException $e) {
            error_log("Greška pri ažuriranju termina: " . $e->getMessage());
            $errors[] = 'Greška pri spremanju promjena.';
        }
    }
}

$title = "Uredi termin";

ob_start();
require_once __DIR__ . '/../views/termini/uredi.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';