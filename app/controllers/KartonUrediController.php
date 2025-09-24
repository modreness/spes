<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$korisnik = current_user();

if (!in_array($korisnik['uloga'], ['admin', 'recepcioner'])) {
    http_response_code(403);
    $user = $korisnik; // Dodaj ovu liniju
    require __DIR__ . '/../views/errors/403.php';
    exit;
}

$karton_id = $_GET['id'] ?? null;

if (!$karton_id || !is_numeric($karton_id)) {
    header('Location: /kartoni/lista?msg=neispravan_id');
    exit;
}

$pdo = db();

$stmt = $pdo->prepare("SELECT k.*, u.ime, u.prezime 
                       FROM kartoni k 
                       JOIN users u ON k.pacijent_id = u.id 
                       WHERE k.id = ?");

$stmt->execute([$karton_id]);
$karton = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$karton) {
    header('Location: /kartoni/lista?msg=karton_ne_postoji');
    exit;
}

$title = 'Uredi karton: ' . htmlspecialchars($karton['ime'] . ' ' . $karton['prezime']);

ob_start();
require __DIR__ . '/../views/kartoni/uredi.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';
