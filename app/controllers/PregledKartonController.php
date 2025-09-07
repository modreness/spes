<?php
require_once __DIR__ . '/../helpers/load.php';
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

// Dohvati tretmane
$tretmani = $pdo->prepare("SELECT * FROM tretmani WHERE karton_id = ? ORDER BY datum DESC");
$tretmani->execute([$karton_id]);
$tretmani = $tretmani->fetchAll(PDO::FETCH_ASSOC);

$title = "Pregled kartona";

ob_start();
require __DIR__ . '/../views/kartoni/pregled.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';