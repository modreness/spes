<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$logovani = current_user();
if (!in_array($logovani['uloga'], ['admin', 'recepcioner'])) {
  header('Location: /dashboard');
  exit;
}

$pdo = db();
$pacijenti = $pdo->query("SELECT id, ime, prezime, email FROM users WHERE uloga = 'pacijent'")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tip_pacijenta = $_POST['tip_pacijenta'] ?? 'postojeci';
    $pacijent_id = $_POST['pacijent_id'] ?? '';
    $jmbg = trim($_POST['jmbg'] ?? '');

    // Provjera: ako je postojeći pacijent, da li već ima karton
    if ($tip_pacijenta === 'postojeci' && $pacijent_id !== '') {
        $provjera = $pdo->prepare("SELECT id FROM kartoni WHERE pacijent_id = ?");
        $provjera->execute([$pacijent_id]);
        if ($provjera->fetch()) {
            header('Location: /kartoni/kreiraj?msg=postoji');
            exit;
        }
    }

    // Provjera: ako je novi pacijent, da li već postoji korisnik s tim email/username
    if ($tip_pacijenta === 'novi') {
        $ime = trim($_POST['ime'] ?? '');
        $prezime = trim($_POST['prezime'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $lozinka = trim($_POST['lozinka'] ?? '');

        // Email je sada opcionalan - proveravaj samo ako nije prazan
        $email_provjera_uslovi = [];
        $email_provjera_params = [];
        
        if (!empty($email)) {
            $email_provjera_uslovi[] = "email = ?";
            $email_provjera_params[] = $email;
        }
        
        // Username uvek mora biti jedinstven
        $email_provjera_uslovi[] = "username = ?";
        $email_provjera_params[] = $username;
        
        if (!empty($email_provjera_uslovi)) {
            $email_provjera_sql = "SELECT id FROM users WHERE " . implode(' OR ', $email_provjera_uslovi);
            $provjera = $pdo->prepare($email_provjera_sql);
            $provjera->execute($email_provjera_params);
            if ($provjera->fetch()) {
                header('Location: /kartoni/kreiraj?msg=postoji');
                exit;
            }
        }

        // POBOLJŠANA JMBG PROVJERA - samo ako nije prazan
        if (!empty($jmbg)) {
            $provjeraJMBG = $pdo->prepare("SELECT id FROM kartoni WHERE jmbg = ? AND jmbg IS NOT NULL");
            $provjeraJMBG->execute([$jmbg]);
            if ($provjeraJMBG->fetch()) {
                header('Location: /kartoni/kreiraj?msg=jmbg_postoji');
                exit;
            }
        }

        // Kreiranje novog korisnika - email može biti NULL
        $hash = password_hash($lozinka, PASSWORD_DEFAULT);
        $email_value = !empty($email) ? $email : null;
        
        $stmt = $pdo->prepare("INSERT INTO users (ime, prezime, email, username, lozinka, uloga) VALUES (?, ?, ?, ?, ?, 'pacijent')");
        $stmt->execute([$ime, $prezime, $email_value, $username, $hash]);
        $pacijent_id = $pdo->lastInsertId();
    } else {
        // Za postojeće pacijente, proveravaj JMBG samo ako nije prazan
        if (!empty($jmbg)) {
            $provjeraJMBG = $pdo->prepare("SELECT id FROM kartoni WHERE jmbg = ? AND jmbg IS NOT NULL");
            $provjeraJMBG->execute([$jmbg]);
            if ($provjeraJMBG->fetch()) {
                header('Location: /kartoni/kreiraj?msg=jmbg_postoji');
                exit;
            }
        }
    }

    // Podaci za unos u karton
    $datum_rodjenja = $_POST['datum_rodjenja'] ?: null;
    $spol = $_POST['spol'] ?? null;
    $adresa = $_POST['adresa'] ?? null;
    $telefon = $_POST['telefon'] ?? null;
    $broj_upisa = $_POST['broj_upisa'] ?? null;
    $anamneza = $_POST['anamneza'] ?? null;
    $rehabilitacija = $_POST['rehabilitacija'] ?? null;
    $pocetna_procjena = $_POST['pocetna_procjena'] ?? null;
    $biljeske = $_POST['biljeske'] ?? null;
    $napomena = $_POST['napomena'] ?? null;
    
    // Dijagnoze iz checkboxova
    $odabrane_dijagnoze = $_POST['dijagnoze'] ?? [];

    // JMBG može biti NULL ako je prazan
    $jmbg_value = !empty($jmbg) ? $jmbg : null;

    // UKLONILI SMO dijagnoza iz INSERT-a jer je sad u posebnoj tabeli
    $stmt = $pdo->prepare("INSERT INTO kartoni (pacijent_id, datum_otvaranja, datum_rodjenja, adresa, telefon, jmbg, spol, email, broj_upisa, anamneza, rehabilitacija, pocetna_procjena, biljeske, napomena, otvorio_id)
                           VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $pacijent_id,
        $datum_rodjenja,
        $adresa,
        $telefon,
        $jmbg_value,
        $spol,
        $email,
        $broj_upisa,
        $anamneza,
        $rehabilitacija,
        $pocetna_procjena,
        $biljeske,
        $napomena,
        $logovani['id']
    ]);
    
    $karton_id = $pdo->lastInsertId();
    
    // Sačuvaj dijagnoze u karton_dijagnoze tabelu
    if (!empty($odabrane_dijagnoze)) {
        $stmt_dijagnoza = $pdo->prepare("INSERT INTO karton_dijagnoze (karton_id, dijagnoza_id, datum_dijagnoze) VALUES (?, ?, CURDATE())");
        foreach ($odabrane_dijagnoze as $dijagnoza_id) {
            $stmt_dijagnoza->execute([$karton_id, $dijagnoza_id]);
        }
    }

    header('Location: /kartoni/kreiraj?msg=kreiran');
    exit;
}

require_once __DIR__ . '/../views/kartoni/kreiraj.php';