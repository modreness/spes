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

// Dohvati podatke za dropdowne
try {
    // Pacijenti
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'pacijent' AND aktivan = 1 ORDER BY ime, prezime");
    $stmt->execute();
    $pacijenti = $stmt->fetchAll();
    
    // Terapeuti
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' AND aktivan = 1 ORDER BY ime, prezime");
    $stmt->execute();
    $terapeuti = $stmt->fetchAll();
    
    // Usluge sa kategorijama - SAMO POJEDINAČNE za sada (pakete ne prikazuj u dropdown)
    $stmt = $pdo->prepare("
        SELECT c.*, k.naziv as kategorija_naziv 
        FROM cjenovnik c 
        LEFT JOIN kategorije_usluga k ON c.kategorija_id = k.id 
        WHERE c.aktivan = 1 AND c.tip_usluge = 'pojedinacna'
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

// Proveri da li pacijent ima aktivan paket
$aktivni_paketi = [];
$odabrani_pacijent_id = $_POST['pacijent_id'] ?? $_GET['pacijent_id'] ?? '';
$odabrani_terapeut_id = $_POST['terapeut_id'] ?? $_GET['terapeut_id'] ?? '';

if (!empty($odabrani_pacijent_id)) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                kp.*,
                c.naziv as paket_naziv,
                c.cijena as paket_cijena
            FROM kupljeni_paketi kp
            JOIN cjenovnik c ON kp.usluga_id = c.id
            WHERE kp.pacijent_id = ? 
            AND kp.status = 'aktivan'
            AND kp.iskoristeno_termina < kp.ukupno_termina
            ORDER BY kp.datum_kupovine DESC
        ");
        $stmt->execute([$odabrani_pacijent_id]);
        $aktivni_paketi = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Greška pri dohvaćanju paketa: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pacijent_id = $_POST['pacijent_id'] ?? '';
    $terapeut_id = $_POST['terapeut_id'] ?? '';
    $usluga_id = $_POST['usluga_id'] ?? '';
    $datum = $_POST['datum'] ?? '';
    $vrijeme = $_POST['vrijeme'] ?? '';
    $napomena = trim($_POST['napomena'] ?? '');
    $koristi_paket = $_POST['koristi_paket'] ?? '';  // ID paketa ili 'ne'
    $placeno = isset($_POST['placeno']) ? 1 : 0;
    
    // Validacija
    if (empty($pacijent_id)) {
        $errors[] = 'Pacijent je obavezan.';
    }
    
   
    
    // Usluga je obavezna samo ako se NE koristi paket
    if (empty($koristi_paket) || $koristi_paket === 'ne') {
        if (empty($usluga_id)) {
            $errors[] = 'Usluga je obavezna.';
        }
    }
    
    if (empty($datum)) {
        $errors[] = 'Datum je obavezan.';
    }
    
    if (empty($vrijeme)) {
        $errors[] = 'Vrijeme je obavezno.';
    }
    
    // Kombinuj datum i vrijeme
    $datum_vrijeme = '';
    if (!empty($datum) && !empty($vrijeme)) {
        $datum_vrijeme = $datum . ' ' . $vrijeme;
        
        // Provjeri da li je u budućnosti
        if (strtotime($datum_vrijeme) <= time()) {
            $errors[] = 'Termin mora biti u budućnosti.';
        }
    }
    
    // Provjeri koliziju termina
    if (empty($errors) && !empty($datum_vrijeme)) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM termini 
            WHERE terapeut_id = ? 
            AND datum_vrijeme = ? 
            AND status IN ('zakazan', 'slobodan')
        ");
        $stmt->execute([$terapeut_id, $datum_vrijeme]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Terapeut već ima zakazan termin u to vrijeme.';
        }
    }
    
    // Ako se koristi paket - proveri validnost
    $paket_id = null;
    if (!empty($koristi_paket) && $koristi_paket !== 'ne') {
        $paket_id = (int)$koristi_paket;
        
        // Proveri da li paket postoji i ima slobodne termine
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM kupljeni_paketi 
                WHERE id = ? 
                AND pacijent_id = ? 
                AND status = 'aktivan'
                AND iskoristeno_termina < ukupno_termina
            ");
            $stmt->execute([$paket_id, $pacijent_id]);
            $paket = $stmt->fetch();
            
            if (!$paket) {
                $errors[] = 'Odabrani paket nije validan ili nema slobodnih termina.';
                $paket_id = null;
            } else {
                // Ako se koristi paket, postavi usluga_id iz paketa
                $usluga_id = $paket['usluga_id'];
            }
        } catch (PDOException $e) {
            error_log("Greška: " . $e->getMessage());
            $errors[] = 'Greška pri provjeri paketa.';
        }
    }
    
    // Ako se ne koristi paket, usluga_id mora biti postavljen
    if (empty($paket_id) && empty($usluga_id)) {
        $errors[] = 'Usluga je obavezna kada se ne koristi paket.';
    }
    
    // Spremi termin
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // 👉 VAŽNO: Učitaj podatke o terapeutu i pacijentu za zamrzavanje
            $terapeut = null;
            if (!empty($terapeut_id)) {
                $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
                $stmt->execute([$terapeut_id]);
                $terapeut = $stmt->fetch();
            }
            
            $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
            $stmt->execute([$pacijent_id]);
            $pacijent = $stmt->fetch();
            
            // Odredi cijenu
            $iz_paketa = !empty($paket_id) ? 1 : 0;
            $cijena = 0;
            
            if (!$iz_paketa) {
                // Dohvati cijenu usluge
                $stmt = $pdo->prepare("SELECT cijena FROM cjenovnik WHERE id = ?");
                $stmt->execute([$usluga_id]);
                $cijena = $stmt->fetchColumn();
            }
            
       
            // 1. Kreiraj termin SA ZAMRZNUTIM PODACIMA
         
            $stmt = $pdo->prepare("
                INSERT INTO termini 
                (pacijent_id, pacijent_ime, pacijent_prezime, terapeut_id, terapeut_ime, terapeut_prezime, 
                usluga_id, datum_vrijeme, status, tip_zakazivanja, napomena, placeno_iz_paketa, stvarna_cijena, placeno) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'zakazan', 'recepcioner', ?, ?, ?, ?)
            ");
            $stmt->execute([
                $pacijent_id,
                $pacijent['ime'],
                $pacijent['prezime'],
                $terapeut_id ?: null,
                $terapeut ? $terapeut['ime'] : null,
                $terapeut ? $terapeut['prezime'] : null,
                $usluga_id, 
                $datum_vrijeme, 
                $napomena,
                $iz_paketa,
                $iz_paketa ? null : $cijena,
                $iz_paketa ? 1 : $placeno  // Ako je iz paketa, automatski je plaćeno
            ]);
            $termin_id = $pdo->lastInsertId();
            
            // 2. Ako se koristi paket - poveži termin sa paketom
            if ($paket_id) {
                $stmt = $pdo->prepare("
                    INSERT INTO termini_iz_paketa (termin_id, paket_id) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$termin_id, $paket_id]);
                
                // Trigger će automatski povećati iskoristeno_termina
            }
            
            $pdo->commit();
            
            // ✉️ SLANJE EMAIL NOTIFIKACIJA
            require_once __DIR__ . '/../helpers/mailer.php';

            // Dohvati email adrese terapeuta (ako postoji) i pacijenta
            $stmt = $pdo->prepare("
                SELECT 
                    p.email as pacijent_email, p.ime as pacijent_ime, p.prezime as pacijent_prezime,
                    c.naziv as usluga_naziv
                FROM users p
                LEFT JOIN cjenovnik c ON c.id = ?
                WHERE p.id = ?
            ");
            $stmt->execute([$usluga_id, $pacijent_id]);
            $email_data = $stmt->fetch();

            // Dohvati terapeuta ako postoji
            $terapeut_email_data = null;
            if (!empty($terapeut_id)) {
                $stmt = $pdo->prepare("SELECT email, ime, prezime FROM users WHERE id = ?");
                $stmt->execute([$terapeut_id]);
                $terapeut_email_data = $stmt->fetch();
            }

            if ($email_data) {
                $datum_format = date('d.m.Y', strtotime($datum));
                $vrijeme_format = date('H:i', strtotime($datum . ' ' . $vrijeme));
                $paket_info = $iz_paketa ? " (plaćen iz paketa)" : "";
                
                // 📧 Email terapeutu (SAMO ako je odabran)
                if ($terapeut_email_data && !empty($terapeut_email_data['email'])) {
                    // Generiraj Google Calendar link za terapeuta
                    $start_time = strtotime($datum_vrijeme);
                    $end_time = $start_time + (60 * 60);
                    
                    $start_google = gmdate('Ymd\THis\Z', $start_time);
                    $end_google = gmdate('Ymd\THis\Z', $end_time);
                    
                    $calendar_title_terapeut = urlencode("Termin - {$email_data['usluga_naziv']}");
                    $calendar_details_terapeut = urlencode("Pacijent: {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']}");
                    $calendar_location = urlencode("SPES Fizioterapija, Sarajevo");
                    
                    $google_calendar_link_terapeut = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calendar_title_terapeut}&dates={$start_google}/{$end_google}&details={$calendar_details_terapeut}&location={$calendar_location}&sf=true&output=xml";
                    
                    $subject_terapeut = "Novi termin zakazan - " . $datum_format . " u " . $vrijeme_format;
                    $body_terapeut = "
                    <h3>Poštovani {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']},</h3>
                    
                    <p>Zakazan je novi termin:</p>
                    
                    <ul>
                        <li><strong>Pacijent:</strong> {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']}</li>
                        <li><strong>Datum:</strong> {$datum_format}</li>
                        <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                        <li><strong>Usluga:</strong> {$email_data['usluga_naziv']}{$paket_info}</li>
                        " . (!empty($napomena) ? "<li><strong>Napomena:</strong> " . htmlspecialchars($napomena) . "</li>" : "") . "
                    </ul>
                    
                    <div style=\"text-align: center; margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px;\">
                        <p style=\"margin: 0 0 10px 0; font-weight: bold; color: #333;\">Dodaj u kalendar:</p>
                        <a href=\"{$google_calendar_link_terapeut}\" target=\"_blank\" 
                        style=\"display: inline-block; background: #4285f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;\">
                        Dodaj u Google Calendar
                        </a>
                    </div>
                    
                    <hr>
                    <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
                    ";
                    
                    $mail_sent_terapeut = send_mail($terapeut_email_data['email'], $subject_terapeut, $body_terapeut);
                    if (!$mail_sent_terapeut) {
                        error_log("Greška pri slanju maila terapeutu: " . $terapeut_email_data['email']);
                    }
                }

                // 📧 Email pacijentu (uvijek, ako ima email)
                if (!empty($email_data['pacijent_email'])) {
                    $start_time = strtotime($datum_vrijeme);
                    $end_time = $start_time + (60 * 60);
                    
                    $start_google = gmdate('Ymd\THis\Z', $start_time);
                    $end_google = gmdate('Ymd\THis\Z', $end_time);
                    
                    $calendar_title_pacijent = urlencode("Termin - {$email_data['usluga_naziv']}");
                    $calendar_details_pacijent = $terapeut_email_data 
                        ? urlencode("Terapeut: {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']}")
                        : urlencode("Terapeut: Bit će dodijeljen");
                    $calendar_location = urlencode("SPES Fizioterapija, Sarajevo");
                    
                    $google_calendar_link_pacijent = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calendar_title_pacijent}&dates={$start_google}/{$end_google}&details={$calendar_details_pacijent}&location={$calendar_location}&sf=true&output=xml";
                    
                    // Terapeut info za email
                    $terapeut_line = $terapeut_email_data 
                        ? "<li><strong>Terapeut:</strong> {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']}</li>"
                        : "<li><strong>Terapeut:</strong> <em>Bit će dodijeljen</em></li>";
                    
                    $subject_pacijent = "Potvrda termina - " . $datum_format . " u " . $vrijeme_format;
                    $body_pacijent = "
                    <h3>Poštovani/a {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']},</h3>
                    
                    <p>Vaš termin je uspješno zakazan:</p>
                    
                    <ul>
                        <li><strong>Datum:</strong> {$datum_format}</li>
                        <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                        {$terapeut_line}
                        <li><strong>Usluga:</strong> {$email_data['usluga_naziv']}{$paket_info}</li>
                        " . (!empty($napomena) ? "<li><strong>Napomena:</strong> " . htmlspecialchars($napomena) . "</li>" : "") . "
                    </ul>
                    
                    <p>Molimo dođite 10 minuta prije termina.</p>
                    
                    <p>Za sve izmjene ili otkazivanja kontaktirajte recepciju.</p>
                    
                    <div style=\"text-align: center; margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px;\">
                        <p style=\"margin: 0 0 10px 0; font-weight: bold; color: #333;\">Dodaj u kalendar:</p>
                        <a href=\"{$google_calendar_link_pacijent}\" target=\"_blank\" 
                        style=\"display: inline-block; background: #4285f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;\">
                        Dodaj u Google Calendar
                        </a>
                    </div>
                    
                    <hr>
                    <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
                    ";
                    
                    $mail_sent_pacijent = send_mail($email_data['pacijent_email'], $subject_pacijent, $body_pacijent);
                    if (!$mail_sent_pacijent) {
                        error_log("Greška pri slanju maila pacijentu: " . $email_data['pacijent_email']);
                    }
                }
            }
            
            header('Location: /termini?msg=kreiran');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Greška pri kreiranju termina: " . $e->getMessage());
            $errors[] = 'Greška pri spremanju termina.';
        }
    }
}

$title = "Kreiraj termin";

ob_start();
require_once __DIR__ . '/../views/termini/kreiraj.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';