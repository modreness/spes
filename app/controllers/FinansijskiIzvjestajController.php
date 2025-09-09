<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

// Filter parametri
$period = $_GET['period'] ?? 'ovaj_mesec';
$datum_od = $_GET['datum_od'] ?? '';
$datum_do = $_GET['datum_do'] ?? '';

// Izračunaj datume na osnovu perioda
switch ($period) {
    case 'danas':
        $datum_od = $datum_do = date('Y-m-d');
        break;
    case 'ova_sedmica':
        $datum_od = date('Y-m-d', strtotime('monday this week'));
        $datum_do = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'ovaj_mesec':
        $datum_od = date('Y-m-01');
        $datum_do = date('Y-m-t');
        break;
    case 'prosli_mesec':
        $datum_od = date('Y-m-01', strtotime('last month'));
        $datum_do = date('Y-m-t', strtotime('last month'));
        break;
    case 'ova_godina':
        $datum_od = date('Y-01-01');
        $datum_do = date('Y-12-31');
        break;
    case 'custom':
        // Koristićemo datume iz GET parametara
        break;
}

try {
    // Ukupni prihodi
    $stmt = $pdo->prepare("
        SELECT 
            SUM(c.cijena) as ukupno,
            COUNT(*) as broj_termina
        FROM termini t
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE DATE(t.datum_vrijeme) BETWEEN ? AND ? 
        AND t.status = 'obavljen'
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $ukupni_prihodi = $stmt->fetch();
    
    // Prihodi po danima
    $stmt = $pdo->prepare("
        SELECT 
            DATE(t.datum_vrijeme) as dan,
            SUM(c.cijena) as prihod,
            COUNT(*) as termini
        FROM termini t
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE DATE(t.datum_vrijeme) BETWEEN ? AND ? 
        AND t.status = 'obavljen'
        GROUP BY DATE(t.datum_vrijeme)
        ORDER BY dan ASC
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $prihodi_po_danima = $stmt->fetchAll();
    
    // Prihodi po uslugama
    $stmt = $pdo->prepare("
        SELECT 
            c.naziv as usluga,
            k.naziv as kategorija,
            COUNT(*) as broj_termina,
            SUM(c.cijena) as ukupno,
            AVG(c.cijena) as prosek
        FROM termini t
        JOIN cjenovnik c ON t.usluga_id = c.id
        LEFT JOIN kategorije_usluga k ON c.kategorija_id = k.id
        WHERE DATE(t.datum_vrijeme) BETWEEN ? AND ? 
        AND t.status = 'obavljen'
        GROUP BY c.id, c.naziv, k.naziv
        ORDER BY ukupno DESC
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $prihodi_po_uslugama = $stmt->fetchAll();
    
    // Prihodi po terapeutima
    $stmt = $pdo->prepare("
        SELECT 
            CONCAT(u.ime, ' ', u.prezime) as terapeut,
            COUNT(*) as broj_termina,
            SUM(c.cijena) as ukupno,
            AVG(c.cijena) as prosek
        FROM termini t
        JOIN users u ON t.terapeut_id = u.id
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE DATE(t.datum_vrijeme) BETWEEN ? AND ? 
        AND t.status = 'obavljen'
        GROUP BY t.terapeut_id, u.ime, u.prezime
        ORDER BY ukupno DESC
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $prihodi_po_terapeutima = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri generiranju finansijskog izvještaja: " . $e->getMessage());
    $ukupni_prihodi = ['ukupno' => 0, 'broj_termina' => 0];
    $prihodi_po_danima = [];
    $prihodi_po_uslugama = [];
    $prihodi_po_terapeutima = [];
}

$title = "Finansijski izvještaj";

ob_start();
require_once __DIR__ . '/../views/izvjestaji/finansijski.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>