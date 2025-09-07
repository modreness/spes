<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$korisnik = current_user();

// Dozvola: samo admin i recepcioner mogu uređivati karton
if (!in_array($korisnik['uloga'], ['admin', 'recepcioner'])) {
    http_response_code(403);
    echo "Nemate ovlasti za ovu akciju.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = db();

    $karton_id = $_POST['karton_id'] ?? null;

    if (!$karton_id || !is_numeric($karton_id)) {
        header("Location: /kartoni/lista?msg=neispravan_id");
        exit;
    }

    // Prikupi i sanitizuj podatke
    $ime = trim($_POST['ime'] ?? '');
    $prezime = trim($_POST['prezime'] ?? '');
    $datum_rodjenja = $_POST['datum_rodjenja'] ?? null;
    $spol = $_POST['spol'] ?? '';
    $adresa = trim($_POST['adresa'] ?? '');
    $telefon = trim($_POST['telefon'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $jmbg = trim($_POST['jmbg'] ?? '');
    $broj_upisa = trim($_POST['broj_upisa'] ?? '');
    $anamneza = trim($_POST['anamneza'] ?? '');
    $dijagnoza = trim($_POST['dijagnoza'] ?? '');
    $rehabilitacija = trim($_POST['rehabilitacija'] ?? '');
    $pocetna_procjena = trim($_POST['pocetna_procjena'] ?? '');
    $biljeske = trim($_POST['biljeske'] ?? '');
    $napomena = trim($_POST['napomena'] ?? '');

    // Ažuriraj karton
    $stmt = $pdo->prepare("UPDATE kartoni SET
        ime = ?, prezime = ?, datum_rodjenja = ?, spol = ?, adresa = ?, telefon = ?, email = ?, jmbg = ?, broj_upisa = ?,
        anamneza = ?, dijagnoza = ?, rehabilitacija = ?, pocetna_procjena = ?, biljeske = ?, napomena = ?
        WHERE id = ?
    ");

    $success = $stmt->execute([
        $ime, $prezime, $datum_rodjenja, $spol, $adresa, $telefon, $email, $jmbg, $broj_upisa,
        $anamneza, $dijagnoza, $rehabilitacija, $pocetna_procjena, $biljeske, $napomena,
        $karton_id
    ]);

    if ($success) {
        header("Location: /kartoni/pregled?id={$karton_id}&msg=ureden");
    } else {
        header("Location: /kartoni/pregled?id={$karton_id}&msg=gagal");
    }

    exit;
}

// Ako nije POST
http_response_code(405);
echo "Metoda nije dozvoljena.";
exit;
