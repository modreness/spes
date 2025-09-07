<?php
require_once __DIR__ . '/../helpers/load.php';
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

// Dohvati sve tretmane
$tretmani = $pdo->prepare("
  SELECT t.*, 
         u.ime AS unio_ime, u.prezime AS unio_prezime,
         ter.ime AS terapeut_ime, ter.prezime AS terapeut_prezime
  FROM tretmani t
  LEFT JOIN users u ON t.unio_id = u.id
  LEFT JOIN users ter ON t.terapeut_id = ter.id
  WHERE t.karton_id = ?
  ORDER BY t.datum DESC
");


$tretmani->execute([$karton_id]);
$tretmani = $tretmani->fetchAll(PDO::FETCH_ASSOC);
// Dohvati sve terapeute
$terapeuti = $pdo->query("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' ORDER BY ime ASC")->fetchAll(PDO::FETCH_ASSOC);

$title = "Tretmani za " . htmlspecialchars($karton['ime']) . ' ' . htmlspecialchars($karton['prezime']);

ob_start();
require __DIR__ . '/../views/kartoni/tretmani.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';
