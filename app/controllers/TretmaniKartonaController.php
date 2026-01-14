<?php
require_once __DIR__ . '/../helpers/load.php';
require_once __DIR__ . '/../helpers/permissions.php';
require_login();

$pdo = db();
$user = current_user();

$karton_id = $_GET['id'] ?? null;
if (!$karton_id) {
    header('Location: /kartoni/lista?msg=nema_id');
    exit;
}

// Dohvati karton i pacijenta
$karton = $pdo->prepare("SELECT k.*, u.ime, u.prezime FROM kartoni k JOIN users u ON k.pacijent_id = u.id WHERE k.id = ?");
$karton->execute([$karton_id]);
$karton = $karton->fetch(PDO::FETCH_ASSOC);
if (!$karton) {
    header('Location: /kartoni/lista?msg=nema_karton');
    exit;
}

// **PROVJERI PRISTUP OVISNO O ULOZI**
if ($user['uloga'] === 'pacijent') {
    // Pacijent može vidjeti samo svoje tretmane
    if (!hasPermission($user, 'pregled_vlastiti_tretmani')) {
        header('Location: /dashboard?error=no_permission');
        exit;
    }
    
    if ($karton['pacijent_id'] != $user['id']) {
        header('Location: /dashboard?error=not_your_record');
        exit;
    }
} elseif ($user['uloga'] === 'terapeut') {
    // Terapeut može vidjeti tretmane svojih pacijenata
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

// Dohvati tretmane ovisno o ulozi
if ($user['uloga'] === 'pacijent') {
    // Pacijent vidi ograničene informacije o tretmanima
    $tretmani = $pdo->prepare("
        SELECT t.id, t.datum, t.datum_tretmana, t.stanje_prije, t.terapija, t.stanje_poslije,
               COALESCE(CONCAT(ter.ime, ' ', ter.prezime), 
                        CONCAT(t.terapeut_ime, ' ', t.terapeut_prezime), 
                        'N/A') AS terapeut_ime_prezime,
               DATE_FORMAT(t.datum, '%d.%m.%Y %H:%i') as datum_format
        FROM tretmani t
        LEFT JOIN users ter ON t.terapeut_id = ter.id
        WHERE t.karton_id = ?
        ORDER BY COALESCE(t.datum_tretmana, t.datum) DESC
    ");
    $tretmani->execute([$karton_id]);
    $tretmani = $tretmani->fetchAll(PDO::FETCH_ASSOC);
    
    // Pacijent ne može dodavati/uređivati tretmane
    $terapeuti = [];
} else {
    // Admin/recepcioner/terapeut vide sve detalje i mogu uređivati
    $tretmani = $pdo->prepare("
        SELECT t.*, 
               COALESCE(CONCAT(u.ime, ' ', u.prezime), 'N/A') AS unio_ime_prezime,
               COALESCE(CONCAT(ter.ime, ' ', ter.prezime), 
                        CONCAT(t.terapeut_ime, ' ', t.terapeut_prezime), 
                        'N/A') AS terapeut_ime_prezime,
               u.ime as unio_ime, u.prezime as unio_prezime,
               ter.ime as terapeut_ime, ter.prezime as terapeut_prezime
        FROM tretmani t
        LEFT JOIN users u ON t.unio_id = u.id
        LEFT JOIN users ter ON t.terapeut_id = ter.id
        WHERE t.karton_id = ?
        ORDER BY COALESCE(t.datum_tretmana, t.datum) DESC
    ");
    $tretmani->execute([$karton_id]);
    $tretmani = $tretmani->fetchAll(PDO::FETCH_ASSOC);
    
    // Dohvati sve terapeute za dropdown
    $terapeuti = $pdo->query("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' ORDER BY ime ASC")->fetchAll(PDO::FETCH_ASSOC);
}

$title = "Tretmani za " . htmlspecialchars($karton['ime']) . ' ' . htmlspecialchars($karton['prezime']);

ob_start();
require __DIR__ . '/../views/kartoni/tretmani.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';