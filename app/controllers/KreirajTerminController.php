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
    $pacijenti_ids = $_POST['pacijenti_ids'] ?? []; // Za grupne termine
    $terapeut_id = $_POST['terapeut_id'] ?? '';
    $usluge_ids = $_POST['usluge_ids'] ?? []; // MULTISELECT - više usluga
    $datum = $_POST['datum'] ?? '';
    $vrijeme = $_POST['vrijeme'] ?? '';
    $napomena = trim($_POST['napomena'] ?? '');
    $koristi_paket = $_POST['koristi_paket'] ?? '';  // ID paketa ili 'ne'
    $placeno = isset($_POST['placeno']) ? 1 : 0;
    $dozvoli_pridruzivanje = isset($_POST['dozvoli_pridruzivanje']) ? 1 : 0;
    
    // Tip plaćanja: puna_cijena, besplatno, poklon_bon, umanjenje
    $tip_placanja = $_POST['tip_placanja'] ?? 'puna_cijena';
    $umanjenje_posto = floatval($_POST['umanjenje_posto'] ?? 0);
    
    // Postavi besplatno i poklon_bon na osnovu tip_placanja
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
        // Grupni termin
        if (empty($pacijenti_ids) || count($pacijenti_ids) < 2) {
            $errors[] = 'Za grupni termin morate odabrati najmanje 2 pacijenta.';
        }
    }
    
    // Usluge su obavezne samo ako se NE koristi paket
    if ($tip_termina === 'pojedinacni' && (!empty($koristi_paket) && $koristi_paket !== 'ne')) {
        // Paket se koristi, usluge nisu obavezne
    } else {
        if (empty($usluge_ids)) {
            $errors[] = 'Morate odabrati barem jednu uslugu.';
        }
    }
    
    if (empty($datum)) {
        $errors[] = 'Datum je obavezan.';
    }
    
    if (empty($vrijeme)) {
        $errors[] = 'Vrijeme je obavezno.';
    }
    
    // Validacija umanjenja
    if ($tip_placanja === 'umanjenje' && ($umanjenje_posto <= 0 || $umanjenje_posto > 100)) {
        $errors[] = 'Umanjenje mora biti između 1 i 100%.';
    }
    
    // Kombinuj datum i vrijeme
    $datum_vrijeme = '';
    $je_retroaktivan = false;
    if (!empty($datum) && !empty($vrijeme)) {
        $datum_vrijeme = $datum . ' ' . $vrijeme;
        
        // Provjeri da li je retroaktivan (u prošlosti)
        if (strtotime($datum_vrijeme) <= time()) {
            $je_retroaktivan = true;
        }
    }
    
    // PROVJERA KOLIZIJE - da li terapeut već ima termin u to vrijeme
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
                // Postoji termin - provjeri da li dozvoljava pridruživanje
                if (!$postojeci_termin['dozvoli_pridruzivanje']) {
                    $errors[] = 'Terapeut već ima zakazan termin u to vrijeme (pacijent: ' . $postojeci_termin['pacijent_ime'] . '). Ako želite dodati još jednog pacijenta, prvo omogućite "Dozvoli pridruživanje" na postojećem terminu.';
                }
            }
        } catch (PDOException $e) {
            error_log("Greška pri provjeri kolizije: " . $e->getMessage());
        }
    }
    
    // Ako se koristi paket - proveri validnost (samo za pojedinačne)
    $paket_id = null;
    if ($tip_termina === 'pojedinacni' && !empty($koristi_paket) && $koristi_paket !== 'ne') {
        $paket_id = (int)$koristi_paket;
        
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
                // Postavi usluge iz paketa
                $usluge_ids = [$paket['usluga_id']];
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
            
            // Dohvati terapeuta
            $terapeut = null;
            if (!empty($terapeut_id)) {
                $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
                $stmt->execute([$terapeut_id]);
                $terapeut = $stmt->fetch();
            }
            
            // Dohvati cijene svih odabranih usluga
            $ukupna_cijena = 0;
            $usluge_podaci = [];
            $prva_usluga_id = null;
            
            if (!empty($usluge_ids)) {
                $placeholders = implode(',', array_fill(0, count($usluge_ids), '?'));
                $stmt = $pdo->prepare("SELECT id, naziv, cijena FROM cjenovnik WHERE id IN ($placeholders)");
                $stmt->execute($usluge_ids);
                $usluge_podaci = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($usluge_podaci as $usluga) {
                    $ukupna_cijena += $usluga['cijena'];
                }
                
                // Prva usluga za kompatibilnost sa starim kodom
                $prva_usluga_id = $usluge_podaci[0]['id'] ?? null;
            }
            
            // Odredi status - ako je retroaktivan, postavi na 'obavljen'
            $status = $je_retroaktivan ? 'obavljen' : 'zakazan';
            
            $kreirani_termini = [];
            
            if ($tip_termina === 'pojedinacni') {
                // ========== POJEDINAČNI TERMIN ==========
                $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
                $stmt->execute([$pacijent_id]);
                $pacijent = $stmt->fetch();
                
                $iz_paketa = !empty($paket_id) ? 1 : 0;
                $stvarna_cijena = null;
                
                if (!$iz_paketa) {
                    if ($besplatno || $poklon_bon) {
                        $stvarna_cijena = 0;
                    } elseif ($umanjenje_posto > 0) {
                        $stvarna_cijena = $ukupna_cijena * (100 - $umanjenje_posto) / 100;
                    } else {
                        $stvarna_cijena = $ukupna_cijena;
                    }
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO termini 
                    (pacijent_id, pacijent_ime, pacijent_prezime, terapeut_id, terapeut_ime, terapeut_prezime, 
                    usluga_id, datum_vrijeme, status, tip_termina, grupa_id, tip_zakazivanja, napomena, dozvoli_pridruzivanje,
                    placeno_iz_paketa, stvarna_cijena, ukupna_cijena, placeno, besplatno, poklon_bon, umanjenje_posto) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pojedinacni', NULL, 'recepcioner', ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $pacijent_id,
                    $pacijent['ime'],
                    $pacijent['prezime'],
                    $terapeut_id ?: null,
                    $terapeut ? $terapeut['ime'] : null,
                    $terapeut ? $terapeut['prezime'] : null,
                    $prva_usluga_id,  // Prva usluga za kompatibilnost
                    $datum_vrijeme,
                    $status,
                    $napomena,
                    $dozvoli_pridruzivanje,
                    $iz_paketa,
                    $stvarna_cijena,
                    $ukupna_cijena,  // Nova kolona - ukupna cijena svih usluga
                    $iz_paketa ? 1 : $placeno,
                    $iz_paketa ? 0 : $besplatno,
                    $iz_paketa ? 0 : $poklon_bon,
                    $iz_paketa ? 0 : $umanjenje_posto
                ]);
                $termin_id = $pdo->lastInsertId();
                
                // Spremi sve usluge u junction tabelu
                if (!empty($usluge_podaci)) {
                    $stmt_usluge = $pdo->prepare("
                        INSERT INTO termin_usluge (termin_id, usluga_id, naziv_usluge, cijena) 
                        VALUES (?, ?, ?, ?)
                    ");
                    foreach ($usluge_podaci as $usluga) {
                        $stmt_usluge->execute([
                            $termin_id,
                            $usluga['id'],
                            $usluga['naziv'],
                            $usluga['cijena']
                        ]);
                    }
                }
                
                $kreirani_termini[] = [
                    'id' => $termin_id,
                    'pacijent_id' => $pacijent_id,
                    'pacijent_ime' => $pacijent['ime'],
                    'pacijent_prezime' => $pacijent['prezime']
                ];
                
                // Ako se koristi paket
                if ($paket_id) {
                    // 1. Upiši u junction tabelu
                    $stmt = $pdo->prepare("
                        INSERT INTO termini_iz_paketa (termin_id, paket_id) 
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$termin_id, $paket_id]);
                    
                    // 2. Ažuriraj broj iskorištenih termina
                    $stmt = $pdo->prepare("
                        UPDATE kupljeni_paketi 
                        SET iskoristeno_termina = iskoristeno_termina + 1 
                        WHERE id = ?
                    ");
                    $stmt->execute([$paket_id]);
                    
                    // 3. Provjeri da li je paket iskorišten i ažuriraj status
                    $stmt = $pdo->prepare("
                        UPDATE kupljeni_paketi 
                        SET status = 'zavrsen' 
                        WHERE id = ? AND iskoristeno_termina >= ukupno_termina
                    ");
                    $stmt->execute([$paket_id]);
                }
                
            } else {
                // ========== GRUPNI TERMIN ==========
                // Generiši jedinstveni grupa_id (timestamp + random)
                $grupa_id = time() . rand(100, 999);
                
                // Izračunaj stvarnu cijenu (za sve usluge zajedno)
                $stvarna_cijena = $ukupna_cijena;
                if ($besplatno || $poklon_bon) {
                    $stvarna_cijena = 0;
                } elseif ($umanjenje_posto > 0) {
                    $stvarna_cijena = $ukupna_cijena * (100 - $umanjenje_posto) / 100;
                }
                
                // Kreiraj termin za svakog pacijenta
                foreach ($pacijenti_ids as $pid) {
                    $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
                    $stmt->execute([$pid]);
                    $pacijent = $stmt->fetch();
                    
                    if (!$pacijent) continue;
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO termini 
                        (pacijent_id, pacijent_ime, pacijent_prezime, terapeut_id, terapeut_ime, terapeut_prezime, 
                        usluga_id, datum_vrijeme, status, tip_termina, grupa_id, tip_zakazivanja, napomena, dozvoli_pridruzivanje,
                        placeno_iz_paketa, stvarna_cijena, ukupna_cijena, placeno, besplatno, poklon_bon, umanjenje_posto) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'grupni', ?, 'recepcioner', ?, ?, 0, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $pid,
                        $pacijent['ime'],
                        $pacijent['prezime'],
                        $terapeut_id ?: null,
                        $terapeut ? $terapeut['ime'] : null,
                        $terapeut ? $terapeut['prezime'] : null,
                        $prva_usluga_id,  // Prva usluga za kompatibilnost
                        $datum_vrijeme, 
                        $status,
                        $grupa_id,
                        $napomena,
                        $dozvoli_pridruzivanje,
                        $stvarna_cijena,
                        $ukupna_cijena,  // Nova kolona
                        $placeno,
                        $besplatno,
                        $poklon_bon,
                        $umanjenje_posto
                    ]);
                    
                    $termin_id_grupni = $pdo->lastInsertId();
                    
                    // Spremi sve usluge u junction tabelu
                    if (!empty($usluge_podaci)) {
                        $stmt_usluge = $pdo->prepare("
                            INSERT INTO termin_usluge (termin_id, usluga_id, naziv_usluge, cijena) 
                            VALUES (?, ?, ?, ?)
                        ");
                        foreach ($usluge_podaci as $usluga) {
                            $stmt_usluge->execute([
                                $termin_id_grupni,
                                $usluga['id'],
                                $usluga['naziv'],
                                $usluga['cijena']
                            ]);
                        }
                    }
                    
                    $kreirani_termini[] = [
                        'id' => $termin_id_grupni,
                        'pacijent_id' => $pid,
                        'pacijent_ime' => $pacijent['ime'],
                        'pacijent_prezime' => $pacijent['prezime']
                    ];
                }
            }
            
            $pdo->commit();
            
            // ✉️ SLANJE EMAIL NOTIFIKACIJA - SAMO AKO NIJE RETROAKTIVAN
            if (!$je_retroaktivan) {
                require_once __DIR__ . '/../helpers/mailer.php';
                
                // Dohvati nazive svih usluga
                $usluga_nazivi = array_column($usluge_podaci, 'naziv');
                $usluga_naziv = implode(', ', $usluga_nazivi);
                if (empty($usluga_naziv)) $usluga_naziv = 'N/A';
                
                $datum_format = date('d.m.Y', strtotime($datum));
                $vrijeme_format = date('H:i', strtotime($datum . ' ' . $vrijeme));
                
                // Dohvati terapeut email
                $terapeut_email_data = null;
                if (!empty($terapeut_id)) {
                    $stmt = $pdo->prepare("SELECT email, ime, prezime FROM users WHERE id = ?");
                    $stmt->execute([$terapeut_id]);
                    $terapeut_email_data = $stmt->fetch();
                }
                
                // Email terapeutu
                if ($terapeut_email_data && !empty($terapeut_email_data['email'])) {
                    $start_time = strtotime($datum_vrijeme);
                    $end_time = $start_time + (60 * 60);
                    
                    $start_google = gmdate('Ymd\THis\Z', $start_time);
                    $end_google = gmdate('Ymd\THis\Z', $end_time);
                    
                    // Lista pacijenata za email
                    $pacijenti_lista = array_map(function($t) {
                        return $t['pacijent_ime'] . ' ' . $t['pacijent_prezime'];
                    }, $kreirani_termini);
                    $pacijenti_str = implode(', ', $pacijenti_lista);
                    
                    $tip_label = $tip_termina === 'grupni' ? ' (GRUPNI TERMIN)' : '';
                    
                    $calendar_title = urlencode("Termin - {$usluga_naziv}{$tip_label}");
                    $calendar_details = urlencode("Pacijent(i): {$pacijenti_str}");
                    $calendar_location = urlencode("SPES Fizioterapija, Sarajevo");
                    
                    $google_calendar_link = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calendar_title}&dates={$start_google}/{$end_google}&details={$calendar_details}&location={$calendar_location}&sf=true&output=xml";
                    
                    $subject_terapeut = "Novi termin zakazan{$tip_label} - " . $datum_format . " u " . $vrijeme_format;
                    $body_terapeut = "
                    <h3>Poštovani {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']},</h3>
                    
                    <p>Zakazan je novi termin:</p>
                    
                    <ul>
                        <li><strong>Tip:</strong> " . ($tip_termina === 'grupni' ? 'Grupni termin (' . count($kreirani_termini) . ' pacijenata)' : 'Pojedinačni termin') . "</li>
                        <li><strong>Pacijent(i):</strong> {$pacijenti_str}</li>
                        <li><strong>Datum:</strong> {$datum_format}</li>
                        <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                        <li><strong>Usluga:</strong> {$usluga_naziv}</li>
                        " . (!empty($napomena) ? "<li><strong>Napomena:</strong> " . htmlspecialchars($napomena) . "</li>" : "") . "
                    </ul>
                    
                    <div style=\"text-align: center; margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px;\">
                        <p style=\"margin: 0 0 10px 0; font-weight: bold; color: #333;\">Dodaj u kalendar:</p>
                        <a href=\"{$google_calendar_link}\" target=\"_blank\" 
                        style=\"display: inline-block; background: #4285f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;\">
                        Dodaj u Google Calendar
                        </a>
                    </div>
                    
                    <hr>
                    <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
                    ";
                    
                    send_mail($terapeut_email_data['email'], $subject_terapeut, $body_terapeut);
                }
                
                // Email svakom pacijentu
                foreach ($kreirani_termini as $termin_info) {
                    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                    $stmt->execute([$termin_info['pacijent_id']]);
                    $pacijent_email = $stmt->fetchColumn();
                    
                    if (!empty($pacijent_email)) {
                        $start_time = strtotime($datum_vrijeme);
                        $end_time = $start_time + (60 * 60);
                        
                        $start_google = gmdate('Ymd\THis\Z', $start_time);
                        $end_google = gmdate('Ymd\THis\Z', $end_time);
                        
                        $calendar_title = urlencode("Termin - {$usluga_naziv}");
                        $calendar_details = $terapeut_email_data 
                            ? urlencode("Terapeut: {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']}")
                            : urlencode("Terapeut: Bit će dodijeljen");
                        $calendar_location = urlencode("SPES Fizioterapija, Sarajevo");
                        
                        $google_calendar_link = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calendar_title}&dates={$start_google}/{$end_google}&details={$calendar_details}&location={$calendar_location}&sf=true&output=xml";
                        
                        $terapeut_line = $terapeut_email_data 
                            ? "<li><strong>Terapeut:</strong> {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']}</li>"
                            : "<li><strong>Terapeut:</strong> <em>Bit će dodijeljen</em></li>";
                        
                        $grupni_info = $tip_termina === 'grupni' ? "<li><strong>Tip:</strong> Grupni termin</li>" : "";
                        
                        $subject_pacijent = "Potvrda termina - " . $datum_format . " u " . $vrijeme_format;
                        $body_pacijent = "
                        <h3>Poštovani/a {$termin_info['pacijent_ime']} {$termin_info['pacijent_prezime']},</h3>
                        
                        <p>Vaš termin je uspješno zakazan:</p>
                        
                        <ul>
                            {$grupni_info}
                            <li><strong>Datum:</strong> {$datum_format}</li>
                            <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                            {$terapeut_line}
                            <li><strong>Usluga:</strong> {$usluga_naziv}</li>
                            " . (!empty($napomena) ? "<li><strong>Napomena:</strong> " . htmlspecialchars($napomena) . "</li>" : "") . "
                        </ul>
                        
                        <p>Molimo dođite 10 minuta prije termina.</p>
                        
                        <p>Za sve izmjene ili otkazivanja kontaktirajte recepciju.</p>
                        
                        <div style=\"text-align: center; margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px;\">
                            <p style=\"margin: 0 0 10px 0; font-weight: bold; color: #333;\">Dodaj u kalendar:</p>
                            <a href=\"{$google_calendar_link}\" target=\"_blank\" 
                            style=\"display: inline-block; background: #4285f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;\">
                            Dodaj u Google Calendar
                            </a>
                        </div>
                        
                        <hr>
                        <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
                        ";
                        
                        send_mail($pacijent_email, $subject_pacijent, $body_pacijent);
                    }
                }
            }
            
            $msg = $tip_termina === 'grupni' ? 'kreiran_grupni' : 'kreiran';
            if ($je_retroaktivan) $msg .= '_retroaktivan';
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