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
    
    // Usluge sa kategorijama - SAMO POJEDINAČNE
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

// Proveri da li pacijent ima aktivan paket (samo za pojedinačne termine)
$aktivni_paketi = [];
$odabrani_pacijent_id = $_POST['pacijent_id'] ?? $_GET['pacijent_id'] ?? '';
$odabrani_terapeut_id = $_POST['terapeut_id'] ?? $_GET['terapeut_id'] ?? '';
$tip_termina = $_POST['tip_termina'] ?? $_GET['tip_termina'] ?? 'pojedinacni';

if (!empty($odabrani_pacijent_id) && $tip_termina === 'pojedinacni') {
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
    $tip_termina = $_POST['tip_termina'] ?? 'pojedinacni';
    $pacijent_id = $_POST['pacijent_id'] ?? '';
    $pacijenti_ids = $_POST['pacijenti_ids'] ?? [];
    $terapeut_id = $_POST['terapeut_id'] ?? '';
    $usluga_id = $_POST['usluga_id'] ?? '';
    $datum = $_POST['datum'] ?? '';
    $vrijeme = $_POST['vrijeme'] ?? '';
    $napomena = trim($_POST['napomena'] ?? '');
    $koristi_paket = $_POST['koristi_paket'] ?? '';
    $placeno = isset($_POST['placeno']) ? 1 : 0;
    $dozvoli_pridruzivanje = isset($_POST['dozvoli_pridruzivanje']) ? 1 : 0;
    
    // Tip plaćanja
    $tip_placanja = $_POST['tip_placanja'] ?? 'puna_cijena';
    $umanjenje_posto = floatval($_POST['umanjenje_posto'] ?? 0);
    
    $besplatno = ($tip_placanja === 'besplatno') ? 1 : 0;
    $poklon_bon = ($tip_placanja === 'poklon_bon') ? 1 : 0;
    if ($tip_placanja !== 'umanjenje') {
        $umanjenje_posto = 0;
    }
    
    // Validacija
    if ($tip_termina === 'pojedinacni') {
        if (empty($pacijent_id)) {
            $errors[] = 'Pacijent je obavezan.';
        }
    } else {
        if (empty($pacijenti_ids) || count($pacijenti_ids) < 2) {
            $errors[] = 'Za grupni termin morate odabrati najmanje 2 pacijenta.';
        }
    }
    
    if ($tip_termina === 'pojedinacni' && (!empty($koristi_paket) && $koristi_paket !== 'ne')) {
        // Paket se koristi
    } else {
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
    
    if ($tip_placanja === 'umanjenje' && ($umanjenje_posto <= 0 || $umanjenje_posto > 100)) {
        $errors[] = 'Umanjenje mora biti između 1 i 100%.';
    }
    
    // Kombinuj datum i vrijeme
    $datum_vrijeme = '';
    $je_retroaktivan = false;
    if (!empty($datum) && !empty($vrijeme)) {
        $datum_vrijeme = $datum . ' ' . $vrijeme;
        if (strtotime($datum_vrijeme) <= time()) {
            $je_retroaktivan = true;
        }
    }
    
    // PROVJERA KOLIZIJE
    if (empty($errors) && !empty($terapeut_id) && !empty($datum_vrijeme)) {
        try {
            $stmt = $pdo->prepare("
                SELECT id, dozvoli_pridruzivanje, 
                       CONCAT(pacijent_ime, ' ', pacijent_prezime) as pacijent_ime
                FROM termini 
                WHERE terapeut_id = ? 
                AND datum_vrijeme = ? 
                AND status IN ('zakazan', 'slobodan')
            ");
            $stmt->execute([$terapeut_id, $datum_vrijeme]);
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
    
    // Provjera paketa
    $paket_id = null;
    if ($tip_termina === 'pojedinacni' && !empty($koristi_paket) && $koristi_paket !== 'ne') {
        $paket_id = (int)$koristi_paket;
        
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM kupljeni_paketi 
                WHERE id = ? AND pacijent_id = ? AND status = 'aktivan'
                AND iskoristeno_termina < ukupno_termina
            ");
            $stmt->execute([$paket_id, $pacijent_id]);
            $paket = $stmt->fetch();
            
            if (!$paket) {
                $errors[] = 'Odabrani paket nije validan ili nema slobodnih termina.';
                $paket_id = null;
            } else {
                $usluga_id = $paket['usluga_id'];
            }
        } catch (PDOException $e) {
            error_log("Greška: " . $e->getMessage());
            $errors[] = 'Greška pri provjeri paketa.';
        }
    }
    
    // Spremi termin(e)
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $terapeut = null;
            if (!empty($terapeut_id)) {
                $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
                $stmt->execute([$terapeut_id]);
                $terapeut = $stmt->fetch();
            }
            
            $cijena = 0;
            if (!empty($usluga_id)) {
                $stmt = $pdo->prepare("SELECT cijena FROM cjenovnik WHERE id = ?");
                $stmt->execute([$usluga_id]);
                $cijena = $stmt->fetchColumn();
            }
            
            $status = $je_retroaktivan ? 'obavljen' : 'zakazan';
            $kreirani_termini = [];
            
            if ($tip_termina === 'pojedinacni') {
                $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
                $stmt->execute([$pacijent_id]);
                $pacijent = $stmt->fetch();
                
                $iz_paketa = !empty($paket_id) ? 1 : 0;
                $stvarna_cijena = null;
                
                if (!$iz_paketa) {
                    if ($besplatno || $poklon_bon) {
                        $stvarna_cijena = 0;
                    } elseif ($umanjenje_posto > 0) {
                        $stvarna_cijena = $cijena * (100 - $umanjenje_posto) / 100;
                    } else {
                        $stvarna_cijena = $cijena;
                    }
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO termini 
                    (pacijent_id, pacijent_ime, pacijent_prezime, terapeut_id, terapeut_ime, terapeut_prezime, 
                    usluga_id, datum_vrijeme, status, tip_termina, grupa_id, tip_zakazivanja, napomena, dozvoli_pridruzivanje,
                    placeno_iz_paketa, stvarna_cijena, placeno, besplatno, poklon_bon, umanjenje_posto) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pojedinacni', NULL, 'recepcioner', ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $pacijent_id, $pacijent['ime'], $pacijent['prezime'],
                    $terapeut_id ?: null, $terapeut ? $terapeut['ime'] : null, $terapeut ? $terapeut['prezime'] : null,
                    $usluga_id, $datum_vrijeme, $status, $napomena, $dozvoli_pridruzivanje,
                    $iz_paketa, $stvarna_cijena, $iz_paketa ? 1 : $placeno,
                    $iz_paketa ? 0 : $besplatno, $iz_paketa ? 0 : $poklon_bon, $iz_paketa ? 0 : $umanjenje_posto
                ]);
                $termin_id = $pdo->lastInsertId();
                
                $kreirani_termini[] = [
                    'id' => $termin_id,
                    'pacijent_id' => $pacijent_id,
                    'pacijent_ime' => $pacijent['ime'],
                    'pacijent_prezime' => $pacijent['prezime']
                ];
                
                if ($paket_id) {
                    $stmt = $pdo->prepare("INSERT INTO termini_iz_paketa (termin_id, paket_id) VALUES (?, ?)");
                    $stmt->execute([$termin_id, $paket_id]);
                }
                
            } else {
                // GRUPNI TERMIN
                $grupa_id = time() . rand(100, 999);
                
                $stvarna_cijena = $cijena;
                if ($besplatno || $poklon_bon) {
                    $stvarna_cijena = 0;
                } elseif ($umanjenje_posto > 0) {
                    $stvarna_cijena = $cijena * (100 - $umanjenje_posto) / 100;
                }
                
                foreach ($pacijenti_ids as $pid) {
                    $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
                    $stmt->execute([$pid]);
                    $pacijent = $stmt->fetch();
                    
                    if (!$pacijent) continue;
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO termini 
                        (pacijent_id, pacijent_ime, pacijent_prezime, terapeut_id, terapeut_ime, terapeut_prezime, 
                        usluga_id, datum_vrijeme, status, tip_termina, grupa_id, tip_zakazivanja, napomena, dozvoli_pridruzivanje,
                        placeno_iz_paketa, stvarna_cijena, placeno, besplatno, poklon_bon, umanjenje_posto) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'grupni', ?, 'recepcioner', ?, ?, 0, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $pid, $pacijent['ime'], $pacijent['prezime'],
                        $terapeut_id ?: null, $terapeut ? $terapeut['ime'] : null, $terapeut ? $terapeut['prezime'] : null,
                        $usluga_id, $datum_vrijeme, $status, $grupa_id, $napomena, $dozvoli_pridruzivanje,
                        $stvarna_cijena, $placeno, $besplatno, $poklon_bon, $umanjenje_posto
                    ]);
                    
                    $kreirani_termini[] = [
                        'id' => $pdo->lastInsertId(),
                        'pacijent_id' => $pid,
                        'pacijent_ime' => $pacijent['ime'],
                        'pacijent_prezime' => $pacijent['prezime']
                    ];
                }
            }
            
            $pdo->commit();
            
            // EMAIL NOTIFIKACIJE - samo ako nije retroaktivan
            if (!$je_retroaktivan) {
                require_once __DIR__ . '/../helpers/mailer.php';
                
                $stmt = $pdo->prepare("SELECT naziv FROM cjenovnik WHERE id = ?");
                $stmt->execute([$usluga_id]);
                $usluga_naziv = $stmt->fetchColumn();
                
                $datum_format = date('d.m.Y', strtotime($datum));
                $vrijeme_format = date('H:i', strtotime($datum . ' ' . $vrijeme));
                
                $terapeut_email_data = null;
                if (!empty($terapeut_id)) {
                    $stmt = $pdo->prepare("SELECT email, ime, prezime FROM users WHERE id = ?");
                    $stmt->execute([$terapeut_id]);
                    $terapeut_email_data = $stmt->fetch();
                }
                
                // Email terapeutu
                if ($terapeut_email_data && !empty($terapeut_email_data['email'])) {
                    $pacijenti_lista = array_map(function($t) {
                        return $t['pacijent_ime'] . ' ' . $t['pacijent_prezime'];
                    }, $kreirani_termini);
                    $pacijenti_str = implode(', ', $pacijenti_lista);
                    
                    $tip_label = $tip_termina === 'grupni' ? ' (GRUPNI TERMIN)' : '';
                    
                    $subject_terapeut = "Novi termin zakazan{$tip_label} - " . $datum_format . " u " . $vrijeme_format;
                    $body_terapeut = "<h3>Poštovani {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']},</h3>
                    <p>Zakazan je novi termin:</p>
                    <ul>
                        <li><strong>Pacijent(i):</strong> {$pacijenti_str}</li>
                        <li><strong>Datum:</strong> {$datum_format}</li>
                        <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                        <li><strong>Usluga:</strong> {$usluga_naziv}</li>
                    </ul>
                    <hr><small>SPES aplikacija</small>";
                    
                    send_mail($terapeut_email_data['email'], $subject_terapeut, $body_terapeut);
                }
                
                // Email pacijentima
                foreach ($kreirani_termini as $termin_info) {
                    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                    $stmt->execute([$termin_info['pacijent_id']]);
                    $pacijent_email = $stmt->fetchColumn();
                    
                    if (!empty($pacijent_email)) {
                        $subject_pacijent = "Potvrda termina - " . $datum_format . " u " . $vrijeme_format;
                        $body_pacijent = "<h3>Poštovani/a {$termin_info['pacijent_ime']} {$termin_info['pacijent_prezime']},</h3>
                        <p>Vaš termin je uspješno zakazan:</p>
                        <ul>
                            <li><strong>Datum:</strong> {$datum_format}</li>
                            <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                            <li><strong>Usluga:</strong> {$usluga_naziv}</li>
                        </ul>
                        <p>Molimo dođite 10 minuta prije termina.</p>
                        <hr><small>SPES aplikacija</small>";
                        
                        send_mail($pacijent_email, $subject_pacijent, $body_pacijent);
                    }
                }
            }
            
            $msg = $tip_termina === 'grupni' ? 'kreiran_grupni' : 'kreiran';
            header('Location: /termini?msg=' . $msg);
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