<?php
require_once __DIR__ . '/../helpers/load.php';

require_login();

$user = current_user();

// Dodaj terapeuta u dozvoljene uloge
if (!in_array($user['uloga'], ['admin', 'recepcioner', 'terapeut'])) {
    header('Location: /dashboard');
    exit;
}

$pdo = db();

// Funkcije dani() i smjene() su već u utils.php kroz load.php

// Filter datum - defaultno ova sedmica
$datum_od = $_GET['filter_datum_od'] ?? date('Y-m-d', strtotime('monday this week'));
$start_date = new DateTime($datum_od);
$end_date = (clone $start_date)->modify('+6 days');

// Dohvati rasporede
try {
    $sql = "
        SELECT r.*, CONCAT(u.ime, ' ', u.prezime) AS terapeut_ime
        FROM rasporedi_sedmicni r
        JOIN users u ON r.terapeut_id = u.id
        WHERE r.datum_od BETWEEN :od AND :do
    ";
    
    $params = [
        'od' => $start_date->format('Y-m-d'),
        'do' => $end_date->format('Y-m-d')
    ];
    
    // Ako je terapeut, dodaj filter
    if ($user['uloga'] === 'terapeut') {
        $sql .= " AND r.terapeut_id = :terapeut_id";
        $params['terapeut_id'] = $user['id'];
    }
    
    $sql .= " ORDER BY r.datum_od ASC, FIELD(r.smjena, 'jutro','popodne','vecer')";
    
    $rasporedi = $pdo->prepare($sql);
    $rasporedi->execute($params);
    $data = $rasporedi->fetchAll(PDO::FETCH_ASSOC);

    // Organizuj po danima i smjenama - prilagodi postojeći kod
    $raspored_po_danu = [];
    
    // Inicijalizuj sve dane i smene
    foreach (array_keys(dani()) as $dan_key) {
        foreach (array_keys(smjene()) as $smjena_key) {
            $raspored_po_danu[$dan_key][$smjena_key] = [];
        }
    }
    
    // Popuni podatke
    foreach ($data as $r) {
        // Pretpostavljam da u bazi imaš kolone kao 'ponedeljak', 'utorak', etc.
        // i da označavaju da li terapeut radi taj dan
        foreach (array_keys(dani()) as $dan_key) {
            if (isset($r[$dan_key]) && $r[$dan_key] && $r['aktivan']) {
                $raspored_po_danu[$dan_key][$r['smjena']][] = $r['terapeut_ime'];
            }
        }
    }
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju rasporeda: " . $e->getMessage());
    $raspored_po_danu = [];
}

$title = $user['uloga'] === 'terapeut' ? 'Moj raspored' : 'Pregled rasporeda';

ob_start();
require_once __DIR__ . '/../views/raspored/pregled.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>