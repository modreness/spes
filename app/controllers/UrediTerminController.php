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
    
    // Sačuvaj trenutni status za poređenje
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
    
    // Dohvati postojeće usluge ovog termina iz junction tabele
    $termin_usluge = [];
    $stmt = $pdo->prepare("SELECT * FROM termin_usluge WHERE termin_id = ?");
    $stmt->execute([$termin_id]);
    $termin_usluge = $stmt->fetchAll();
    
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
    $usluge_ids = $_POST['usluge_ids'] ?? [];  // MULTISELECT - više usluga
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
    
    // Postavi besplatno i poklon_bon na osnovu tip_placanja
    $besplatno = ($tip_placanja === 'besplatno') ? 1 : 0;
    $poklon_bon = ($tip_placanja === 'poklon_bon') ? 1 : 0;
    if ($tip_placanja !== 'umanjenje') {
        $umanjenje_posto = 0;
    }
    
    // Validacija
    if (empty($pacijent_id)) {
        $errors[] = 'Pacijent je obavezan.';
    }
    
    if (empty($usluge_ids)) {
        $errors[] = 'Morate odabrati barem jednu uslugu.';
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
    
    // Validacija umanjenja
    if ($tip_placanja === 'umanjenje' && ($umanjenje_posto <= 0 || $umanjenje_posto > 100)) {
        $errors[] = 'Umanjenje mora biti između 1 i 100%.';
    }
    
    // Kombinuj datum i vrijeme
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
            
            // Učitaj podatke o terapeutu (ako postoji) i pacijentu za zamrzavanje
            $terapeut = null;
            if (!empty($terapeut_id)) {
                $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
                $stmt->execute([$terapeut_id]);
                $terapeut = $stmt->fetch();
            }

            $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
            $stmt->execute([$pacijent_id]);
            $pacijent = $stmt->fetch();
            
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
            
            // Izračunaj stvarnu cijenu (samo ako nije iz paketa)
            $stvarna_cijena = $termin['stvarna_cijena'];
            
            if (!$termin['placeno_iz_paketa']) {
                if ($besplatno || $poklon_bon) {
                    $stvarna_cijena = 0;
                } elseif ($umanjenje_posto > 0) {
                    $stvarna_cijena = $ukupna_cijena * (100 - $umanjenje_posto) / 100;
                } else {
                    $stvarna_cijena = $ukupna_cijena;
                }
            }
            
            // Ažuriraj glavni termin
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
                    napomena = ?,
                    dozvoli_pridruzivanje = ?,
                    placeno = ?,
                    besplatno = ?,
                    poklon_bon = ?,
                    umanjenje_posto = ?,
                    stvarna_cijena = ?,
                    ukupna_cijena = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $pacijent_id,
                $pacijent['ime'] ?? null,
                $pacijent['prezime'] ?? null,
                $terapeut_id ?: null,
                $terapeut['ime'] ?? null,
                $terapeut['prezime'] ?? null,
                $prva_usluga_id,  // Prva usluga za kompatibilnost
                $datum_vrijeme, 
                $status, 
                $napomena,
                $dozvoli_pridruzivanje,
                $placeno,
                $termin['placeno_iz_paketa'] ? 0 : $besplatno,
                $termin['placeno_iz_paketa'] ? 0 : $poklon_bon,
                $termin['placeno_iz_paketa'] ? 0 : $umanjenje_posto,
                $stvarna_cijena,
                $ukupna_cijena,
                $termin_id
            ]);
            
            // Ažuriraj usluge u junction tabeli
            // 1. Obriši postojeće
            $stmt = $pdo->prepare("DELETE FROM termin_usluge WHERE termin_id = ?");
            $stmt->execute([$termin_id]);
            
            // 2. Dodaj nove
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
            
            // Ako je grupni termin i korisnik želi ažurirati cijelu grupu
            if (!empty($termin['grupa_id']) && $azuriraj_grupu) {
                // Dohvati sve termine iz grupe (osim trenutnog)
                $stmt = $pdo->prepare("SELECT id FROM termini WHERE grupa_id = ? AND id != ?");
                $stmt->execute([$termin['grupa_id'], $termin_id]);
                $grupa_termini = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Ažuriraj svaki termin u grupi
                $stmt = $pdo->prepare("
                    UPDATE termini 
                    SET terapeut_id = ?, 
                        terapeut_ime = ?,
                        terapeut_prezime = ?,
                        usluga_id = ?, 
                        datum_vrijeme = ?, 
                        status = ?, 
                        napomena = ?,
                        dozvoli_pridruzivanje = ?,
                        besplatno = ?,
                        poklon_bon = ?,
                        umanjenje_posto = ?,
                        stvarna_cijena = ?,
                        ukupna_cijena = ?
                    WHERE grupa_id = ? AND id != ?
                ");
                $stmt->execute([
                    $terapeut_id ?: null,
                    $terapeut['ime'] ?? null,
                    $terapeut['prezime'] ?? null,
                    $prva_usluga_id, 
                    $datum_vrijeme, 
                    $status, 
                    $napomena,
                    $dozvoli_pridruzivanje,
                    $besplatno,
                    $poklon_bon,
                    $umanjenje_posto,
                    $stvarna_cijena,
                    $ukupna_cijena,
                    $termin['grupa_id'],
                    $termin_id
                ]);
                
                // Ažuriraj usluge za sve termine u grupi
                foreach ($grupa_termini as $gid) {
                    // Obriši postojeće
                    $stmt = $pdo->prepare("DELETE FROM termin_usluge WHERE termin_id = ?");
                    $stmt->execute([$gid]);
                    
                    // Dodaj nove
                    if (!empty($usluge_podaci)) {
                        $stmt_usluge = $pdo->prepare("
                            INSERT INTO termin_usluge (termin_id, usluga_id, naziv_usluge, cijena) 
                            VALUES (?, ?, ?, ?)
                        ");
                        foreach ($usluge_podaci as $usluga) {
                            $stmt_usluge->execute([
                                $gid,
                                $usluga['id'],
                                $usluga['naziv'],
                                $usluga['cijena']
                            ]);
                        }
                    }
                }
            }
            
            // Ako je termin bio iz paketa i status se promijenio u 'otkazan'
            // Vrati termin na paket (smanji iskoristeno_termina)
            if ($termin['placeno_iz_paketa'] && $old_status !== 'otkazan' && $status === 'otkazan') {
                // Pronađi paket_id iz termini_iz_paketa
                $stmt = $pdo->prepare("SELECT paket_id FROM termini_iz_paketa WHERE termin_id = ?");
                $stmt->execute([$termin_id]);
                $paket_veza = $stmt->fetch();
                
                if ($paket_veza) {
                    $paket_id = $paket_veza['paket_id'];
                    
                    // Smanji broj iskorištenih termina
                    $stmt = $pdo->prepare("
                        UPDATE kupljeni_paketi 
                        SET iskoristeno_termina = GREATEST(0, iskoristeno_termina - 1)
                        WHERE id = ?
                    ");
                    $stmt->execute([$paket_id]);
                    
                    // Vrati status paketa na 'aktivan' ako je bio 'zavrsen'
                    $stmt = $pdo->prepare("
                        UPDATE kupljeni_paketi 
                        SET status = 'aktivan' 
                        WHERE id = ? AND status = 'zavrsen'
                    ");
                    $stmt->execute([$paket_id]);
                    
                    // Obriši vezu iz junction tabele
                    $stmt = $pdo->prepare("DELETE FROM termini_iz_paketa WHERE termin_id = ?");
                    $stmt->execute([$termin_id]);
                    
                    // Ažuriraj termin da više nije iz paketa
                    $stmt = $pdo->prepare("UPDATE termini SET placeno_iz_paketa = 0 WHERE id = ?");
                    $stmt->execute([$termin_id]);
                }
            }
            
            $pdo->commit();
            
            // SLANJE EMAIL NOTIFIKACIJA
            $terapeut_dodan = (empty($termin['terapeut_id']) && !empty($terapeut_id));
            $terapeut_promijenjen = (!empty($termin['terapeut_id']) && !empty($terapeut_id) && $termin['terapeut_id'] != $terapeut_id);

            if (($old_status !== $status && in_array($status, ['obavljen', 'otkazan'])) || $terapeut_dodan || $terapeut_promijenjen) {
                require_once __DIR__ . '/../helpers/mailer.php';
                
                // Dohvati fresh email podatke
                $stmt = $pdo->prepare("
                    SELECT 
                        p.email as pacijent_email, p.ime as pacijent_ime, p.prezime as pacijent_prezime
                    FROM users p
                    WHERE p.id = ?
                ");
                $stmt->execute([$pacijent_id]);
                $email_data = $stmt->fetch();
                
                // Nazivi svih usluga
                $usluga_nazivi = array_column($usluge_podaci, 'naziv');
                $usluga_naziv = !empty($usluga_nazivi) ? implode(', ', $usluga_nazivi) : 'N/A';
                
                // Dohvati NOVOG terapeuta ako postoji
                $terapeut_email_data = null;
                if (!empty($terapeut_id)) {
                    $stmt = $pdo->prepare("SELECT email, ime, prezime FROM users WHERE id = ?");
                    $stmt->execute([$terapeut_id]);
                    $terapeut_email_data = $stmt->fetch();
                }
                
                // Dohvati STAROG terapeuta ako je promijenjen
                $stari_terapeut_email_data = null;
                if ($terapeut_promijenjen) {
                    $stmt = $pdo->prepare("SELECT email, ime, prezime FROM users WHERE id = ?");
                    $stmt->execute([$termin['terapeut_id']]);
                    $stari_terapeut_email_data = $stmt->fetch();
                }
                
                if ($email_data) {
                    $datum_format = date('d.m.Y', strtotime($datum));
                    $vrijeme_format = date('H:i', strtotime($datum . ' ' . $vrijeme));
                    
                    // Ako je terapeut PROMIJENJEN (bio je jedan, sad je drugi)
                    if ($terapeut_promijenjen && $old_status === $status) {
                        // Email STAROM terapeutu - obavijest da je skinut
                        if ($stari_terapeut_email_data && !empty($stari_terapeut_email_data['email'])) {
                            $subject_stari = "Termin promijenjen - " . $datum_format . " u " . $vrijeme_format;
                            $body_stari = "
                            <h3>Poštovani {$stari_terapeut_email_data['ime']} {$stari_terapeut_email_data['prezime']},</h3>
                            
                            <p>Obavještavamo vas da je termin promijenjen i dodijeljen drugom terapeutu:</p>
                            
                            <ul>
                                <li><strong>Pacijent:</strong> {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']}</li>
                                <li><strong>Datum:</strong> {$datum_format}</li>
                                <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                                <li><strong>Usluge:</strong> {$usluga_naziv}</li>
                            </ul>
                            
                            <hr>
                            <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
                            ";
                            
                            send_mail($stari_terapeut_email_data['email'], $subject_stari, $body_stari);
                        }
                        
                        // Email NOVOM terapeutu - dodijeljen termin
                        if ($terapeut_email_data && !empty($terapeut_email_data['email'])) {
                            $start_time = strtotime($datum_vrijeme);
                            $end_time = $start_time + (60 * 60);
                            
                            $start_google = gmdate('Ymd\THis\Z', $start_time);
                            $end_google = gmdate('Ymd\THis\Z', $end_time);
                            
                            $calendar_title = urlencode("Termin - {$usluga_naziv}");
                            $calendar_details = urlencode("Pacijent: {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']}");
                            $calendar_location = urlencode("SPES Fizioterapija, Sarajevo");
                            
                            $google_calendar_link = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calendar_title}&dates={$start_google}/{$end_google}&details={$calendar_details}&location={$calendar_location}&sf=true&output=xml";
                            
                            $subject_terapeut = "Dodijeljen vam je termin - " . $datum_format . " u " . $vrijeme_format;
                            $body_terapeut = "
                            <h3>Poštovani {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']},</h3>
                            
                            <p>Dodijeljen vam je termin:</p>
                            
                            <ul>
                                <li><strong>Pacijent:</strong> {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']}</li>
                                <li><strong>Datum:</strong> {$datum_format}</li>
                                <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                                <li><strong>Usluge:</strong> {$usluga_naziv}</li>
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
                        
                        // Email pacijentu o promjeni terapeuta
                        if (!empty($email_data['pacijent_email']) && $terapeut_email_data) {
                            $subject_pacijent = "Ažuriranje termina - promijenjen terapeut";
                            $body_pacijent = "
                            <h3>Poštovani/a {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']},</h3>
                            
                            <p>Obavještavamo vas da je promijenjen terapeut za vaš termin:</p>
                            
                            <ul>
                                <li><strong>Datum:</strong> {$datum_format}</li>
                                <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                                <li><strong>Novi terapeut:</strong> {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']}</li>
                                <li><strong>Usluge:</strong> {$usluga_naziv}</li>
                            </ul>
                            
                            <p>Molimo dođite 10 minuta prije termina.</p>
                            
                            <hr>
                            <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
                            ";
                            
                            send_mail($email_data['pacijent_email'], $subject_pacijent, $body_pacijent);
                        }
                    }
                    // Ako je terapeut DODAN (prije nije bilo terapeuta)
                    else if ($terapeut_dodan && $old_status === $status) {
                        if ($terapeut_email_data && !empty($terapeut_email_data['email'])) {
                            $start_time = strtotime($datum_vrijeme);
                            $end_time = $start_time + (60 * 60);
                            
                            $start_google = gmdate('Ymd\THis\Z', $start_time);
                            $end_google = gmdate('Ymd\THis\Z', $end_time);
                            
                            $calendar_title = urlencode("Termin - {$usluga_naziv}");
                            $calendar_details = urlencode("Pacijent: {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']}");
                            $calendar_location = urlencode("SPES Fizioterapija, Sarajevo");
                            
                            $google_calendar_link = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calendar_title}&dates={$start_google}/{$end_google}&details={$calendar_details}&location={$calendar_location}&sf=true&output=xml";
                            
                            $subject_terapeut = "Dodijeljen vam je termin - " . $datum_format . " u " . $vrijeme_format;
                            $body_terapeut = "
                            <h3>Poštovani {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']},</h3>
                            
                            <p>Dodijeljen vam je termin:</p>
                            
                            <ul>
                                <li><strong>Pacijent:</strong> {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']}</li>
                                <li><strong>Datum:</strong> {$datum_format}</li>
                                <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                                <li><strong>Usluge:</strong> {$usluga_naziv}</li>
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
                        
                        if (!empty($email_data['pacijent_email']) && $terapeut_email_data) {
                            $subject_pacijent = "Ažuriranje termina - dodijeljen terapeut";
                            $body_pacijent = "
                            <h3>Poštovani/a {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']},</h3>
                            
                            <p>Obavještavamo vas da je vašem terminu dodijeljen terapeut:</p>
                            
                            <ul>
                                <li><strong>Datum:</strong> {$datum_format}</li>
                                <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                                <li><strong>Terapeut:</strong> {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']}</li>
                                <li><strong>Usluge:</strong> {$usluga_naziv}</li>
                            </ul>
                            
                            <p>Molimo dođite 10 minuta prije termina.</p>
                            
                            <hr>
                            <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
                            ";
                            
                            send_mail($email_data['pacijent_email'], $subject_pacijent, $body_pacijent);
                        }
                    }
                    // Ako je promijenjen STATUS
                    else if ($old_status !== $status && in_array($status, ['obavljen', 'otkazan'])) {
                        $status_labels = [
                            'zakazan' => 'Zakazan',
                            'obavljen' => 'Obavljen',
                            'otkazan' => 'Otkazan',
                            'slobodan' => 'Slobodan'
                        ];
                        
                        $old_status_label = $status_labels[$old_status] ?? $old_status;
                        $new_status_label = $status_labels[$status] ?? $status;
                        
                        if ($terapeut_email_data && !empty($terapeut_email_data['email'])) {
                            $subject_terapeut = "Status termina promijenjen - " . $datum_format;
                            $body_terapeut = "
                            <h3>Poštovani {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']},</h3>
                            
                            <p>Status termina je promijenjen:</p>
                            
                            <ul>
                                <li><strong>Pacijent:</strong> {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']}</li>
                                <li><strong>Datum:</strong> {$datum_format}</li>
                                <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                                <li><strong>Usluga:</strong> {$usluga_naziv}</li>
                                <li><strong>Status:</strong> {$old_status_label} → {$new_status_label}</li>
                            </ul>
                            
                            <hr>
                            <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
                            ";
                            
                            send_mail($terapeut_email_data['email'], $subject_terapeut, $body_terapeut);
                        }
                        
                        if (!empty($email_data['pacijent_email'])) {
                            $status_message = '';
                            if ($status === 'obavljen') {
                                $status_message = "<p><strong>Vaš termin je uspješno obavljen.</strong> Hvala što ste došli!</p>";
                            } elseif ($status === 'otkazan') {
                                $status_message = "<p><strong>Vaš termin je otkazan.</strong> Za nova zakazivanja kontaktirajte recepciju.</p>";
                            }
                            
                            $terapeut_line = $terapeut_email_data 
                                ? "<li><strong>Terapeut:</strong> {$terapeut_email_data['ime']} {$terapeut_email_data['prezime']}</li>"
                                : "";
                            
                            $subject_pacijent = "Status termina: " . $new_status_label;
                            $body_pacijent = "
                            <h3>Poštovani/a {$email_data['pacijent_ime']} {$email_data['pacijent_prezime']},</h3>
                            
                            <p>Obavještavamo vas o promjeni statusa vašeg termina:</p>
                            
                            <ul>
                                <li><strong>Datum:</strong> {$datum_format}</li>
                                <li><strong>Vrijeme:</strong> {$vrijeme_format}</li>
                                {$terapeut_line}
                                <li><strong>Usluga:</strong> {$usluga_naziv}</li>
                                <li><strong>Status:</strong> {$old_status_label} → {$new_status_label}</li>
                            </ul>
                            
                            {$status_message}
                            
                            <hr>
                            <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
                            ";
                            
                            send_mail($email_data['pacijent_email'], $subject_pacijent, $body_pacijent);
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