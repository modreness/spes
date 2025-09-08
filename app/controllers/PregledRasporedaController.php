<?php
require_once __DIR__ . '/../helpers/load.php';

require_login();

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

$pdo = db();

// Filter datum - defaultno ova sedmica
$datum_od = $_GET['filter_datum_od'] ?? date('Y-m-d', strtotime('monday this week'));
$start_date = new DateTime($datum_od);
$end_date = (clone $start_date)->modify('+6 days');

// Dohvati rasporede
try {
    $rasporedi = $pdo->prepare("
        SELECT r.*, CONCAT(u.ime, ' ', u.prezime) AS terapeut_ime
        FROM rasporedi_sedmicni r
        JOIN users u ON r.terapeut_id = u.id
        WHERE r.datum_od BETWEEN :od AND :do
        ORDER BY r.datum_od ASC, FIELD(r.smjena, 'jutro','popodne','vecer')
    ");
    $rasporedi->execute([
        'od' => $start_date->format('Y-m-d'),
        'do' => $end_date->format('Y-m-d')
    ]);
    $data = $rasporedi->fetchAll(PDO::FETCH_ASSOC);

    // Organizuj po danima i smjenama
    $raspored_po_danu = [];
    foreach ($data as $r) {
        $dan = $r['dan'];
        $smjena = $r['smjena'];
        $raspored_po_danu[$dan][$smjena][] = $r['terapeut_ime'];
    }
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju rasporeda: " . $e->getMessage());
    $raspored_po_danu = [];
}

$title = "Pregled rasporeda";

ob_start();
require_once __DIR__ . '/../views/raspored/pregled.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>