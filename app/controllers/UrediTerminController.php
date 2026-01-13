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
               c.naziv as usluga_naziv,
               c.cijena as usluga_cijena
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
    
    $old_status = $termin['status'];
    $old_terapeut_id = $termin['terapeut_id'];
    $old_datum_vrijeme = $termin['datum_vrijeme'];
    
    // Ako je grupni termin, dohvati ostale članove grupe
    $grupa_clanovi = [];
    if (!empty($termin['grupa_id'])) {
        $stmt = $pdo->prepare("
            SELECT t.id, t.pacijent_id,
                   COALESCE(CONCAT(u.ime, ' ', u.prezime), CONCAT(t.pacijent_ime, ' ', t.pacijent_prezime)) as pacijent_ime,
                   t.status, t.placeno
            FROM termini t
            LEFT JOIN users u ON t.pacijent_id = u.id
            WHERE t.grupa_id = ? AND t.id != ?
            ORDER BY pacijent_ime
        ");
        $stmt->execute([$termin['grupa_id'], $termin_id]);
        $grupa_clanovi = $stmt->fetchAll();
    }
    
    // Odredi trenutni tip plaćanja
    $trenutni_tip_placanja = 'puna_cijena';
    if (!empty($termin['poklon_bon'])) {
        $trenutni_tip_placanja = 'poklon_bon';
    } elseif (!empty($termin['besplatno'])) {
        $trenutni_tip_placanja = 'besplatno';
    } elseif (!empty($termin['umanjenje_posto']) && $termin['umanjenje_posto'] > 0) {
        $trenutni_tip_placanja = 'umanjenje';
    }
    
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
    $placeno = isset($_POST['placeno']) ? 1 : 0;
    $dozvoli_pridruzivanje = isset($_POST['dozvoli_pridruzivanje']) ? 1 : 0;
    $azuriraj_grupu = isset($_POST['azuriraj_grupu']) ? 1 : 0;
    
    // Tip plaćanja
    $tip_placanja = $_POST['tip_placanja'] ?? 'puna_cijena';
    $umanjenje_posto = floatval($_POST['umanjenje_posto'] ?? 0);
    
    $besplatno = ($tip_placanja === 'besplatno') ? 1 : 0;
    $poklon_bon = ($tip_placanja === 'poklon_bon') ? 1 : 0;
    if ($tip_placanja !== 'umanjenje') {
        $umanjenje_posto = 0;
    }
    
    // Validacija
    if (empty($pacijent_id)) {
        $errors[] = 'Pacijent je obavezan.';
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
    
    if ($tip_placanja === 'umanjenje' && ($umanjenje_posto <= 0 || $umanjenje_posto > 100)) {
        $errors[] = 'Umanjenje mora biti između 1 i 100%.';
    }
    
    $datum_vrijeme = '';
    if (!empty($datum) && !empty($vrijeme)) {
        $datum_vrijeme = $datum . ' ' . $vrijeme;
    }
    
    // PROVJERA KOLIZIJE - samo ako se promijenio terapeut ili datum/vrijeme
    if (empty($errors) && !empty($terapeut_id) && !empty($datum_vrijeme)) {
        $terapeut_promijenjen = ($terapeut_id != $old_terapeut_id);
        $vrijeme_promijenjeno = ($datum_vrijeme != $old_datum_vrijeme);
        
        if ($terapeut_promijenjen || $vrijeme_promijenjeno) {
            try {
                $stmt = $pdo->prepare("
                    SELECT id, dozvoli_pridruzivanje, 
                           CONCAT(pacijent_ime, ' ', pacijent_prezime) as pacijent_ime
                    FROM termini 
                    WHERE terapeut_id = ? 
                    AND datum_vrijeme = ? 
                    AND status IN ('zakazan', 'slobodan')
                    AND id != ?
                ");
                $stmt->execute([$terapeut_id, $datum_vrijeme, $termin_id]);
                $postojeci_termin = $stmt->fetch();
                
                if ($postojeci_termin) {
                    if (!$postojeci_termin['dozvoli_pridruzivanje']) {
                        $errors[] = 'Terapeut već ima zakazan termin u to vrijeme (pacijent: ' . $postojeci_termin['pacijent_ime'] . '). Ako želite dodati još jednog pacijenta, prvo omogućite "Dozvoli pridruživanje" na postojećem terminu.';
                    }
                }
            } catch (PDOException $e) {
                error_log("Greška pri provjeri kolizije: " . $e->getMessage());
            }
        }
    }
    
    // Ažuriraj termin
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $terapeut = null;
            if (!empty($terapeut_id)) {
                $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
                $stmt->execute([$terapeut_id]);
                $terapeut = $stmt->fetch();
            }

            $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
            $stmt->execute([$pacijent_id]);
            $pacijent = $stmt->fetch();
            
            // Izračunaj stvarnu cijenu (samo ako nije iz paketa)
            $stvarna_cijena = $termin['stvarna_cijena'];
            
            if (!$termin['placeno_iz_paketa']) {
                $stmt = $pdo->prepare("SELECT cijena FROM cjenovnik WHERE id = ?");
                $stmt->execute([$usluga_id]);
                $cijena = $stmt->fetchColumn();
                
                if ($besplatno || $poklon_bon) {
                    $stvarna_cijena = 0;
                } elseif ($umanjenje_posto > 0) {
                    $stvarna_cijena = $cijena * (100 - $umanjenje_posto) / 100;
                } else {
                    $stvarna_cijena = $cijena;
                }
            }
            
            // Ažuriraj glavni termin
            $stmt = $pdo->prepare("
                UPDATE termini 
                SET pacijent_id = ?, pacijent_ime = ?, pacijent_prezime = ?,
                    terapeut_id = ?, terapeut_ime = ?, terapeut_prezime = ?,
                    usluga_id = ?, datum_vrijeme = ?, status = ?, napomena = ?,
                    dozvoli_pridruzivanje = ?, placeno = ?, besplatno = ?, poklon_bon = ?,
                    umanjenje_posto = ?, stvarna_cijena = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $pacijent_id, $pacijent['ime'] ?? null, $pacijent['prezime'] ?? null,
                $terapeut_id ?: null, $terapeut['ime'] ?? null, $terapeut['prezime'] ?? null,
                $usluga_id, $datum_vrijeme, $status, $napomena,
                $dozvoli_pridruzivanje, $placeno,
                $termin['placeno_iz_paketa'] ? 0 : $besplatno,
                $termin['placeno_iz_paketa'] ? 0 : $poklon_bon,
                $termin['placeno_iz_paketa'] ? 0 : $umanjenje_posto,
                $stvarna_cijena,
                $termin_id
            ]);
            
            // Ako je grupni termin i korisnik želi ažurirati cijelu grupu
            if (!empty($termin['grupa_id']) && $azuriraj_grupu) {
                $stmt = $pdo->prepare("
                    UPDATE termini 
                    SET terapeut_id = ?, terapeut_ime = ?, terapeut_prezime = ?,
                        usluga_id = ?, datum_vrijeme = ?, status = ?, napomena = ?,
                        dozvoli_pridruzivanje = ?, besplatno = ?, poklon_bon = ?,
                        umanjenje_posto = ?, stvarna_cijena = ?
                    WHERE grupa_id = ? AND id != ?
                ");
                $stmt->execute([
                    $terapeut_id ?: null, $terapeut['ime'] ?? null, $terapeut['prezime'] ?? null,
                    $usluga_id, $datum_vrijeme, $status, $napomena,
                    $dozvoli_pridruzivanje, $besplatno, $poklon_bon, $umanjenje_posto, $stvarna_cijena,
                    $termin['grupa_id'], $termin_id
                ]);
            }
            
            $pdo->commit();
            
            // EMAIL NOTIFIKACIJE
            $terapeut_dodan = (empty($old_terapeut_id) && !empty($terapeut_id));

            if (($old_status !== $status && in_array($status, ['obavljen', 'otkazan'])) || $terapeut_dodan) {
                require_once __DIR__ . '/../helpers/mailer.php';
                
                $stmt = $pdo->prepare("
                    SELECT p.email as pacijent_email, p.ime as pacijent_ime, p.prezime as pacijent_prezime,
                           c.naziv as usluga_naziv
                    FROM users p
                    LEFT JOIN cjenovnik c ON c.id = ?
                    WHERE p.id = ?
                ");
                $stmt->execute([$usluga_id, $pacijent_id]);
                $email_data = $stmt->fetch();
                
                $terapeut_email_data = null;
                if (!empty($terapeut_id)) {
                    $stmt = $pdo->prepare("SELECT email, ime, prezime FROM users WHERE id = ?");
                    $stmt->execute([$terapeut_id]);
                    $terapeut_email_data = $stmt->fetch();
                }
                
                if ($email_data) {
                    $datum_format = date('d.m.Y', strtotime($datum));
                    $vrijeme_format = date('H:i', strtotime($datum . ' ' . $vrijeme));
                    
                    if ($terapeut_dodan && $old_status === $status && $terapeut_email_data) {
                        // Email terapeutu
                        if (!empty($terapeut_email_data['email'])) {
                            $subject = "Dodijeljen vam je termin - " . $datum_format;
                            $body = "<h3>Poštovani {$terapeut_email_data['ime']},</h3>
                            <p>Dodijeljen vam je termin:</p>
                            <ul>
                                <li><strong>Pacijent:</strong> {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']}</li>
                                <li><strong>Datum:</strong> {$datum_format} u {$vrijeme_format}</li>
                                <li><strong>Usluga:</strong> {$email_data['usluga_naziv']}</li>
                            </ul>
                            <hr><small>SPES aplikacija</small>";
                            send_mail($terapeut_email_data['email'], $subject, $body);
                        }
                        
                        // Email pacijentu
                        if (!empty($email_data['pacijent_email'])) {
                            $subject = "Ažuriranje termina - dodijeljen terapeut";
                            $body = "<h3>Poštovani/a {$email_data['pacijent_ime']},</h3>
                            <p>Vašem terminu je dodijeljen terapeut:</p>
                            <ul>
                                <li><strong>Datum:</strong> {$datum_format} u {$vrijeme_format}</li>
                                <li><strong>Terapeut:</strong> {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']}</li>
                            </ul>
                            <hr><small>SPES aplikacija</small>";
                            send_mail($email_data['pacijent_email'], $subject, $body);
                        }
                    }
                    elseif ($old_status !== $status && in_array($status, ['obavljen', 'otkazan'])) {
                        $status_label = $status === 'obavljen' ? 'Obavljen' : 'Otkazan';
                        
                        if (!empty($email_data['pacijent_email'])) {
                            $subject = "Status termina: " . $status_label;
                            $body = "<h3>Poštovani/a {$email_data['pacijent_ime']},</h3>
                            <p>Status vašeg termina je promijenjen na: <strong>{$status_label}</strong></p>
                            <ul>
                                <li><strong>Datum:</strong> {$datum_format} u {$vrijeme_format}</li>
                                <li><strong>Usluga:</strong> {$email_data['usluga_naziv']}</li>
                            </ul>
                            <hr><small>SPES aplikacija</small>";
                            send_mail($email_data['pacijent_email'], $subject, $body);
                        }
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