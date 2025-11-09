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
    
    // Sačuvaj trenutni status za poređenje
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
            $pdo->beginTransaction();
            
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
            
            $pdo->commit();
            
            // ✉️ SLANJE EMAIL NOTIFIKACIJA ZA PROMENU STATUSA
            if ($old_status !== $status && in_array($status, ['obavljeno', 'otkazano', 'nije_dosao'])) {
                require_once __DIR__ . '/../helpers/mailer.php';
                
                // Dohvati fresh podatke nakon ažuriranja
                $stmt = $pdo->prepare("
                    SELECT t.*, 
                           u_pacijent.email as pacijent_email,
                           u_pacijent.ime as pacijent_ime_fresh, 
                           u_pacijent.prezime as pacijent_prezime_fresh,
                           u_terapeut.email as terapeut_email,
                           u_terapeut.ime as terapeut_ime_fresh, 
                           u_terapeut.prezime as terapeut_prezime_fresh,
                           c.naziv as usluga_naziv
                    FROM termini t
                    LEFT JOIN users u_pacijent ON t.pacijent_id = u_pacijent.id
                    LEFT JOIN users u_terapeut ON t.terapeut_id = u_terapeut.id
                    LEFT JOIN cjenovnik c ON t.usluga_id = c.id
                    WHERE t.id = ?
                ");
                $stmt->execute([$termin_id]);
                $termin_fresh = $stmt->fetch();
                
                if ($termin_fresh) {
                    // Koristi zamrznute podatke iz tabele termini
                    $termin_data = [
                        'datum_vrijeme' => $termin_fresh['datum_vrijeme'],
                        'pacijent_ime' => $termin_fresh['pacijent_ime'],        // Zamrznuto ime
                        'pacijent_prezime' => $termin_fresh['pacijent_prezime'], // Zamrznuto prezime
                        'terapeut_ime' => $termin_fresh['terapeut_ime'],         // Zamrznuto ime
                        'terapeut_prezime' => $termin_fresh['terapeut_prezime'], // Zamrznuto prezime
                        'usluga_naziv' => $termin_fresh['usluga_naziv'],
                        'napomena' => $termin_fresh['napomena']
                    ];
                    
                    // 📧 Email terapeutu
                    if (!empty($termin_fresh['terapeut_email'])) {
                        $terapeut_email_data = [
                            'email' => $termin_fresh['terapeut_email'],
                            'ime' => $termin_fresh['terapeut_ime_fresh'],      // Fresh ime za email greeting
                            'prezime' => $termin_fresh['terapeut_prezime_fresh'] // Fresh prezime za email greeting
                        ];
                        
                        $mail_sent_terapeut = send_status_change_email(
                            $terapeut_email_data, 
                            $termin_data, 
                            $old_status, 
                            $status, 
                            false
                        );
                        
                        if (!$mail_sent_terapeut) {
                            error_log("Greška pri slanju status email-a terapeutu: " . $termin_fresh['terapeut_email']);
                        }
                    }
                    
                    // 📧 Email pacijentu (samo ako ima email)
                    if (!empty($termin_fresh['pacijent_email'])) {
                        $pacijent_email_data = [
                            'email' => $termin_fresh['pacijent_email'],
                            'ime' => $termin_fresh['pacijent_ime_fresh'],      // Fresh ime za email greeting
                            'prezime' => $termin_fresh['pacijent_prezime_fresh'] // Fresh prezime za email greeting
                        ];
                        
                        $mail_sent_pacijent = send_status_change_email(
                            $pacijent_email_data, 
                            $termin_data, 
                            $old_status, 
                            $status, 
                            true
                        );
                        
                        if (!$mail_sent_pacijent) {
                            error_log("Greška pri slanju status email-a pacijentu: " . $termin_fresh['pacijent_email']);
                        }
                    } else {
                        error_log("Pacijent nema email adresu - preskačem slanje status email-a");
                    }
                }
            }
            
            header('Location: /termini?msg=azuriran');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
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