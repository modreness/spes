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

    // Podaci o korisniku koji unosi tretman
    $unio_id = $user['id'];
    $unio_ime = $user['ime'];
    $unio_prezime = $user['prezime'];

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

    // Unos u bazu
    $stmt = $pdo->prepare("
    INSERT INTO tretmani 
    (karton_id, datum, stanje_prije, terapija, stanje_poslije, unio_id, terapeut_id, terapeut_ime, terapeut_prezime)
    VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $karton_id,
    $stanje_prije,
    $terapija,
    $stanje_poslije,
    $user['id'],
    $terapeut_id,
    $terapeut_ime,
    $terapeut_prezime
]);

    header("Location: " . $_SERVER['HTTP_REFERER'] . "?msg=tretman-ok");
    exit;
} else {
    header("Location: " . $_SERVER['HTTP_REFERER'] . "?msg=tretman-greska");
}
