<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

// Samo terapeut može videti svoj raspored
if ($user['uloga'] !== 'terapeut') {
    header('Location: /dashboard');
    exit;
}

try {
    // Dohvati moj raspored za tekuću sedmicu
    $datum_od = date('Y-m-d', strtotime('monday this week'));
    $datum_do = date('Y-m-d', strtotime('sunday this week'));
    
    $stmt = $pdo->prepare("
        SELECT rs.*, 
               DATE_FORMAT(rs.datum_od, '%d.%m.%Y') as datum_od_format,
               DATE_FORMAT(rs.datum_do, '%d.%m.%Y') as datum_do_format
        FROM rasporedi_sedmicni rs
        WHERE rs.terapeut_id = ? 
        AND rs.datum_od >= ? 
        AND rs.datum_do <= ?
        ORDER BY rs.datum_od DESC
    ");
    $stmt->execute([$user['id'], $datum_od, $datum_do]);
    $moj_raspored = $stmt->fetchAll();
    
    // Dohvati moje termine za ovu sedmicu
    $stmt = $pdo->prepare("
        SELECT t.*, 
               CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
               c.naziv as usluga,
               DATE(t.datum_vrijeme) as datum,
               TIME(t.datum_vrijeme) as vrijeme,
               DAYNAME(t.datum_vrijeme) as dan_naziv
        FROM termini t
        JOIN users p ON t.pacijent_id = p.id
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE t.terapeut_id = ? 
        AND DATE(t.datum_vrijeme) >= ? 
        AND DATE(t.datum_vrijeme) <= ?
        ORDER BY t.datum_vrijeme ASC
    ");
    $stmt->execute([$user['id'], $datum_od, $datum_do]);
    $moji_termini = $stmt->fetchAll();
    
    // Grupiraj termine po danima
    $termini_po_danima = [];
    foreach ($moji_termini as $termin) {
        $termini_po_danima[$termin['datum']][] = $termin;
    }
    
    // Statistike za sedmicu
    $ukupno_termina = count($moji_termini);
    $obavljeni_termini = array_filter($moji_termini, function($t) { return $t['status'] === 'obavljen'; });
    $broj_obavljenih = count($obavljeni_termini);
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju rasporeda: " . $e->getMessage());
    $moj_raspored = [];
    $moji_termini = [];
    $termini_po_danima = [];
    $ukupno_termina = 0;
    $broj_obavljenih = 0;
}

$title = "Moj raspored";

ob_start();
require_once __DIR__ . '/../views/raspored/moj.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>