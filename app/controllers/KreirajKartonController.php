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
    $jmbg = $_POST['jmbg'] ?? '';

    // Provjera: ako je postojeći pacijent, da li već ima karton
    if ($tip_pacijenta === 'postojeci' && $pacijent_id !== '') {
        $provjera = $pdo->prepare("SELECT id FROM kartoni WHERE pacijent_id = ?");
        $provjera->execute([$pacijent_id]);
        if ($provjera->fetch()) {
            header('Location: /kartoni/kreiraj?msg=postoji');
            exit;
        }
    }

    // Provjera: ako je novi pacijent, da li već postoji korisnik s tim email/username i jmbg
    if ($tip_pacijenta === 'novi') {
        $ime = trim($_POST['ime'] ?? '');
        $prezime = trim($_POST['prezime'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $lozinka = trim($_POST['lozinka'] ?? '');

        $provjera = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $provjera->execute([$email, $username]);
        if ($provjera->fetch()) {
            header('Location: /kartoni/kreiraj?msg=postoji');
            exit;
        }

        $provjeraJMBG = $pdo->prepare("SELECT id FROM kartoni WHERE jmbg = ?");
        $provjeraJMBG->execute([$jmbg]);
        if ($provjeraJMBG->fetch()) {
            header('Location: /kartoni/kreiraj?msg=jmbg_postoji');
            exit;
        }

        $hash = password_hash($lozinka, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (ime, prezime, email, username, lozinka, uloga) VALUES (?, ?, ?, ?, ?, 'pacijent')");
        $stmt->execute([$ime, $prezime, $email, $username, $hash]);
        $pacijent_id = $pdo->lastInsertId();
    }

    // Podaci za unos u karton
    $datum_rodjenja = $_POST['datum_rodjenja'] ?: null;
    $spol = $_POST['spol'] ?? null;
    $adresa = $_POST['adresa'] ?? null;
    $telefon = $_POST['telefon'] ?? null;
    $broj_upisa = $_POST['broj_upisa'] ?? null;
    $anamneza = $_POST['anamneza'] ?? null;
    $dijagnoza = $_POST['dijagnoza'] ?? null;
    $rehabilitacija = $_POST['rehabilitacija'] ?? null;
    $pocetna_procjena = $_POST['pocetna_procjena'] ?? null;
    $biljeske = $_POST['biljeske'] ?? null;
    $napomena = $_POST['napomena'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO kartoni (pacijent_id, datum_otvaranja, datum_rodjenja, adresa, telefon, jmbg, spol, email, broj_upisa, anamneza, dijagnoza, rehabilitacija, pocetna_procjena, biljeske, napomena, otvorio_id)
                           VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $pacijent_id,
        $datum_rodjenja,
        $adresa,
        $telefon,
        $jmbg,
        $spol,
        $email,
        $broj_upisa,
        $anamneza,
        $dijagnoza,
        $rehabilitacija,
        $pocetna_procjena,
        $biljeske,
        $napomena,
        $logovani['id']
    ]);

    header('Location: /kartoni/kreiraj?msg=kreiran');
    exit;
}

require_once __DIR__ . '/../views/kartoni/kreiraj.php';
