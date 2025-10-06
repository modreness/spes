<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$korisnik = current_user();

// Dozvola: samo admin i recepcioner mogu uređivati karton
if (!in_array($korisnik['uloga'], ['admin', 'recepcioner'])) {
    require __DIR__ . '/../views/errors/403.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = db();

    $karton_id = $_POST['karton_id'] ?? null;

    if (!$karton_id || !is_numeric($karton_id)) {
        header("Location: /kartoni/lista?msg=neispravan_id");
        exit;
    }
    
    // Dohvati postojeći karton
    $stmt = $pdo->prepare("SELECT * FROM kartoni WHERE id = ?");
    $stmt->execute([$karton_id]);
    $postojeci_karton = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$postojeci_karton) {
        header("Location: /kartoni/lista?msg=nije_pronadjen");
        exit;
    }

    // Prikupi i sanitizuj podatke
    $datum_rodjenja = $_POST['datum_rodjenja'] ?? null;
    $spol = $_POST['spol'] ?? '';
    $adresa = trim($_POST['adresa'] ?? '');
    $telefon = trim($_POST['telefon'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $jmbg = trim($_POST['jmbg'] ?? '');
    $broj_upisa = trim($_POST['broj_upisa'] ?? '');
    $anamneza = trim($_POST['anamneza'] ?? '');
    $rehabilitacija = trim($_POST['rehabilitacija'] ?? '');
    $pocetna_procjena = trim($_POST['pocetna_procjena'] ?? '');
    $biljeske = trim($_POST['biljeske'] ?? '');
    $napomena = trim($_POST['napomena'] ?? '');
    
    // Dijagnoze iz checkboxova
    $odabrane_dijagnoze = $_POST['dijagnoze'] ?? [];
    
    // Validacija JMBG - provjeri da li JMBG postoji kod DRUGOG kartona
    if ($jmbg !== $postojeci_karton['jmbg']) {
        $stmt = $pdo->prepare("SELECT id FROM kartoni WHERE jmbg = ? AND id != ?");
        $stmt->execute([$jmbg, $karton_id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "JMBG već postoji u sistemu!";
            header("Location: /kartoni/uredi?id={$karton_id}&msg=jmbg_postoji");
            exit;
        }
    }

    // Ažuriraj karton (UKLONILI SMO ime, prezime i dijagnoza)
    $stmt = $pdo->prepare("UPDATE kartoni SET
        datum_rodjenja = ?, spol = ?, adresa = ?, telefon = ?, email = ?, jmbg = ?, broj_upisa = ?,
        anamneza = ?, rehabilitacija = ?, pocetna_procjena = ?, biljeske = ?, napomena = ?
        WHERE id = ?
    ");

    $success = $stmt->execute([
        $datum_rodjenja, $spol, $adresa, $telefon, $email, $jmbg, $broj_upisa,
        $anamneza, $rehabilitacija, $pocetna_procjena, $biljeske, $napomena,
        $karton_id
    ]);

    if ($success) {
        // Ažuriraj dijagnoze - obriši stare i dodaj nove
        $pdo->prepare("DELETE FROM karton_dijagnoze WHERE karton_id = ?")->execute([$karton_id]);
        
        if (!empty($odabrane_dijagnoze)) {
            $stmt_dijagnoza = $pdo->prepare("INSERT INTO karton_dijagnoze (karton_id, dijagnoza_id, datum_dijagnoze) VALUES (?, ?, CURDATE())");
            foreach ($odabrane_dijagnoze as $dijagnoza_id) {
                $stmt_dijagnoza->execute([$karton_id, $dijagnoza_id]);
            }
        }
        
        header("Location: /kartoni/pregled?id={$karton_id}&msg=ureden");
    } else {
        header("Location: /kartoni/uredi?id={$karton_id}&msg=greska");
    }

    exit;
}

// Ako nije POST
http_response_code(405);
echo "Metoda nije dozvoljena.";
exit;