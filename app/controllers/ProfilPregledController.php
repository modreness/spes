<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$pdo = db();
$user = current_user();

// Dozvoljene role
$dozvoljene = ['admin', 'recepcioner'];
if (!in_array($user['uloga'], $dozvoljene)) {
    http_response_code(403);
    echo "Nemate pristup ovoj stranici.";
    exit;
}

// Detekcija role iz URL-a
$path = $_SERVER['REQUEST_URI'];
$rola = '';
if (str_contains($path, 'pacijent')) $rola = 'pacijent';
if (str_contains($path, 'terapeut')) $rola = 'terapeut';
if (str_contains($path, 'recepcioner')) $rola = 'recepcioner';
if (str_contains($path, 'admin')) $rola = 'admin';

if (!$rola) {
    http_response_code(400);
    echo "Nedefinisana rola.";
    exit;
}

// OGRANIČI PRISTUP: recepcioner ne može vidjeti admin/recepcioner profile
if ($user['uloga'] === 'recepcioner' && in_array($rola, ['admin', 'recepcioner'])) {
    require __DIR__ . '/../views/errors/403.php';
    exit;
}

// Dohvati korisnike - za pacijente dodaj info o kartonu
if ($rola === 'pacijent') {
    $stmt = $pdo->prepare("
        SELECT u.*, 
               CASE WHEN k.id IS NOT NULL THEN 1 ELSE 0 END as ima_karton,
               k.id as karton_id
        FROM users u
        LEFT JOIN kartoni k ON k.pacijent_id = u.id
        WHERE u.uloga = ?
        ORDER BY u.ime, u.prezime
    ");
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE uloga = ? ORDER BY ime, prezime");
}
$stmt->execute([$rola]);
$korisnici = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Pregled profila: " . ucfirst($rola);
ob_start();
require __DIR__ . '/../views/profil/pregled.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';