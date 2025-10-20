<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = db();

    $karton_id = $_POST['karton_id'] ?? null;
    $stanje_prije = $_POST['stanje_prije'] ?? '';
    $terapija = $_POST['terapija'] ?? '';
    $stanje_poslije = $_POST['stanje_poslije'] ?? '';
    $terapeut_id = $_POST['terapeut_id'] ?? null;

    // 👉 VAŽNO: Učitaj podatke o PACIJENTU iz kartona
    $stmt = $pdo->prepare("
        SELECT k.pacijent_id, u.ime as pacijent_ime, u.prezime as pacijent_prezime
        FROM kartoni k
        JOIN users u ON k.pacijent_id = u.id
        WHERE k.id = ?
    ");
    $stmt->execute([$karton_id]);
    $karton_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$karton_data) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?msg=tretman-greska");
        exit;
    }

    // Podaci o terapeutu
    $terapeut_ime = null;
    $terapeut_prezime = null;

    if ($terapeut_id) {
        $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
        $stmt->execute([$terapeut_id]);
        $terapeut = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($terapeut) {
            $terapeut_ime = $terapeut['ime'];
            $terapeut_prezime = $terapeut['prezime'];
        }
    }

    // 👉 Unos u bazu SA ZAMRZNUTIM PODACIMA (bez pacijent_id)
    $stmt = $pdo->prepare("
        INSERT INTO tretmani 
        (karton_id, pacijent_ime, pacijent_prezime, 
         datum, stanje_prije, terapija, stanje_poslije, 
         unio_id, terapeut_id, terapeut_ime, terapeut_prezime)
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $karton_id,
        $karton_data['pacijent_ime'],      // 👈 Zamrzni ime pacijenta
        $karton_data['pacijent_prezime'],  // 👈 Zamrzni prezime pacijenta
        $stanje_prije,
        $terapija,
        $stanje_poslije,
        $user['id'],
        $terapeut_id,
        $terapeut_ime,                     // 👈 Zamrzni ime terapeuta
        $terapeut_prezime                  // 👈 Zamrzni prezime terapeuta
    ]);

    header("Location: " . $_SERVER['HTTP_REFERER'] . "?msg=tretman-ok");
    exit;
} else {
    header("Location: " . $_SERVER['HTTP_REFERER'] . "?msg=tretman-greska");
}