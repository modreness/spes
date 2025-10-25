<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../helpers/permissions.php';

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
    // Filter sedmica - default trenutna sedmica
    $selected_week = $_GET['sedmica'] ?? date('Y-m-d', strtotime('monday this week'));
    $datum_od = date('Y-m-d', strtotime('monday', strtotime($selected_week)));
    $datum_do = date('Y-m-d', strtotime('sunday', strtotime($selected_week)));
    
    // Dohvati sve dostupne sedmice za ovog terapeuta
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
               DATE(datum_od) as sedmica_od,
               DATE(datum_do) as sedmica_do,
               DATE_FORMAT(datum_od, '%d.%m.%Y') as sedmica_od_format,
               DATE_FORMAT(datum_do, '%d.%m.%Y') as sedmica_do_format
        FROM rasporedi_sedmicni 
        WHERE terapeut_id = ?
        ORDER BY datum_od DESC
    ");
    $stmt->execute([$user['id']]);
    $dostupne_sedmice = $stmt->fetchAll();
    
    // Dohvati raspored za odabranu sedmicu
    $stmt = $pdo->prepare("
        SELECT rs.*, 
               DATE_FORMAT(rs.datum_od, '%d.%m.%Y') as datum_od_format,
               DATE_FORMAT(rs.datum_do, '%d.%m.%Y') as datum_do_format
        FROM rasporedi_sedmicni rs
        WHERE rs.terapeut_id = ? 
        AND DATE(rs.datum_od) = ?
    ");
    $stmt->execute([$user['id'], $datum_od]);
    $moj_raspored = $stmt->fetchAll();
    
    // Formatiraj raspored u lakši format
    $formatted_raspored = [];
    if (!empty($moj_raspored)) {
        $raspored = $moj_raspored[0]; // Uzmi prvi (trebao bi biti jedini)
        $dani = [
            'pon' => ['naziv' => 'Ponedjeljak', 'smjena' => $raspored['ponedjeljak']],
            'uto' => ['naziv' => 'Utorak', 'smjena' => $raspored['utorak']], 
            'sri' => ['naziv' => 'Srijeda', 'smjena' => $raspored['srijeda']],
            'cet' => ['naziv' => 'Četvrtak', 'smjena' => $raspored['cetvrtak']],
            'pet' => ['naziv' => 'Petak', 'smjena' => $raspored['petak']],
            'sub' => ['naziv' => 'Subota', 'smjena' => $raspored['subota']],
            'ned' => ['naziv' => 'Nedjelja', 'smjena' => $raspored['nedjelja']]
        ];
        
        foreach ($dani as $dan => $info) {
            if ($info['smjena']) {
                // Parsiranje smjene za dobijanje vremena
                $pocetak = $kraj = '';
                if (preg_match('/jutarnja/i', $info['smjena'])) {
                    $pocetak = '07:00';
                    $kraj = '15:00';
                } elseif (preg_match('/popodnevna/i', $info['smjena'])) {
                    $pocetak = '15:00';
                    $kraj = '23:00';
                } elseif (preg_match('/noćna/i', $info['smjena'])) {
                    $pocetak = '23:00';
                    $kraj = '07:00';
                }
                
                $formatted_raspored[] = [
                    'dan' => $dan,
                    'dan_naziv' => $info['naziv'],
                    'smjena' => $info['smjena'],
                    'pocetak' => $pocetak,
                    'kraj' => $kraj,
                    'period' => $raspored['datum_od_format'] . ' - ' . $raspored['datum_do_format']
                ];
            }
        }
    }
    
    // Dohvati moje termine za odabranu sedmicu
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
    $selected_week = date('Y-m-d', strtotime('monday this week'));
    $datum_od = date('Y-m-d', strtotime('monday this week'));
    $datum_do = date('Y-m-d', strtotime('sunday this week'));
    $dostupne_sedmice = [];
    $moj_raspored = [];
    $formatted_raspored = [];
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