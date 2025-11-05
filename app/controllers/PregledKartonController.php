<?php
require_once __DIR__ . '/../helpers/load.php';
require_once __DIR__ . '/../helpers/permissions.php';
require_login();

$pdo = db();
$user = current_user();
$karton_id = $_GET['id'] ?? null;

if (!$karton_id) {
    header('Location: /kartoni/lista');
    exit;
}

// Dohvati podatke o kartonu
$karton = $pdo->prepare("SELECT k.*, u.ime, u.prezime, u.email FROM kartoni k JOIN users u ON k.pacijent_id = u.id WHERE k.id = ?");
$karton->execute([$karton_id]);
$karton = $karton->fetch(PDO::FETCH_ASSOC);

if (!$karton) {
    header('Location: /kartoni/lista?msg=nema');
    exit;
}

// **PROVJERI PRISTUP OVISNO O ULOZI**
if ($user['uloga'] === 'pacijent') {
    // Pacijent može vidjeti samo svoj karton
    if (!hasPermission($user, 'pregled_vlastiti_karton')) {
        header('Location: /dashboard?error=no_permission');
        exit;
    }
    
    if ($karton['pacijent_id'] != $user['id']) {
        header('Location: /dashboard?error=not_your_record');
        exit;
    }
} elseif ($user['uloga'] === 'terapeut') {
    // Terapeut može vidjeti kartone svojih pacijenata
    $stmt = $pdo->prepare("
        SELECT 1 FROM termini 
        WHERE pacijent_id = ? AND terapeut_id = ? 
        LIMIT 1
    ");
    $stmt->execute([$karton['pacijent_id'], $user['id']]);
    if (!$stmt->fetch()) {
        header('Location: /dashboard?error=not_your_patient');
        exit;
    }
} elseif (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    // Ostale uloge nemaju pristup
    header('Location: /dashboard?error=no_access');
    exit;
}

// Dohvati tretmane (samo ako korisnik ima pravo pristupa)
$tretmani = [];
if ($user['uloga'] === 'pacijent') {
    // Pacijent vidi samo osnovne informacije o tretmanima
    $tretmani = $pdo->prepare("
        SELECT t.datum, 
               CONCAT(ter.ime, ' ', ter.prezime) as terapeut_ime
        FROM tretmani t
        LEFT JOIN users ter ON t.terapeut_id = ter.id
        WHERE t.karton_id = ? 
        ORDER BY t.datum DESC
        LIMIT 5
    ");
    $tretmani->execute([$karton_id]);
    $tretmani = $tretmani->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Admin, recepcioner, terapeut vide sve detalje
    $tretmani = $pdo->prepare("SELECT * FROM tretmani WHERE karton_id = ? ORDER BY datum DESC");
    $tretmani->execute([$karton_id]);
    $tretmani = $tretmani->fetchAll(PDO::FETCH_ASSOC);
}

$title = "Pregled kartona";

ob_start();
require __DIR__ . '/../views/kartoni/pregled.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';