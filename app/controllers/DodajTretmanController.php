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
    $termin_id = $_POST['termin_id'] ?? null;
    $datum_tretmana = $_POST['datum_tretmana'] ?? null;
    
    // Ako je proslijeđen termin_id ali nema datum_tretmana, dohvati iz termina
    if (!empty($termin_id) && empty($datum_tretmana)) {
        $stmt = $pdo->prepare("SELECT DATE(datum_vrijeme) as datum FROM termini WHERE id = ?");
        $stmt->execute([$termin_id]);
        $termin_datum = $stmt->fetchColumn();
        if ($termin_datum) {
            $datum_tretmana = $termin_datum;
        }
    }
    
    // Ako i dalje nema datum_tretmana, koristi današnji datum
    if (empty($datum_tretmana)) {
        $datum_tretmana = date('Y-m-d');
    }

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
        (karton_id, pacijent_ime, pacijent_prezime, termin_id,
         datum, datum_tretmana, stanje_prije, terapija, stanje_poslije, 
         unio_id, terapeut_id, terapeut_ime, terapeut_prezime)
        VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $karton_id,
        $karton_data['pacijent_ime'],      // 👈 Zamrzni ime pacijenta
        $karton_data['pacijent_prezime'],  // 👈 Zamrzni prezime pacijenta
        $termin_id ?: null,                // 👈 Poveznica na termin (može biti NULL)
        $datum_tretmana,                   // 👈 Stvarni datum tretmana
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