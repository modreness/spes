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

// Izračunaj datume
switch ($period) {
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
        break;
}

try {
    // Najčešće dijagnoze - AŽURIRANO za novu strukturu
    $stmt = $pdo->prepare("
        SELECT 
            d.id as dijagnoza_id,
            d.naziv as dijagnoza,
            d.opis,
            COUNT(kd.id) as broj_slucajeva,
            COUNT(DISTINCT k.pacijent_id) as broj_pacijenata
        FROM dijagnoze d
        LEFT JOIN karton_dijagnoze kd ON d.id = kd.dijagnoza_id
        LEFT JOIN kartoni k ON kd.karton_id = k.id
        WHERE k.datum_otvaranja BETWEEN ? AND ?
        GROUP BY d.id, d.naziv, d.opis
        HAVING broj_slucajeva > 0
        ORDER BY broj_slucajeva DESC
        LIMIT 10
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $dijagnoze = $stmt->fetchAll();
    
    // Statistike tretmana po pacijentima
    $stmt = $pdo->prepare("
        SELECT 
            CONCAT(u.ime, ' ', u.prezime) as pacijent,
            COUNT(tr.id) as broj_tretmana,
            MIN(tr.datum) as prvi_tretman,
            MAX(tr.datum) as poslednji_tretman
        FROM users u
        JOIN kartoni k ON u.id = k.pacijent_id
        LEFT JOIN tretmani tr ON k.id = tr.karton_id
        WHERE DATE(tr.datum) BETWEEN ? AND ?
        GROUP BY u.id, u.ime, u.prezime
        HAVING broj_tretmana > 0
        ORDER BY broj_tretmana DESC
        LIMIT 15
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $pacijenti_tretmani = $stmt->fetchAll();
    
    // Aktivnost terapeuta u tretmanima
    $stmt = $pdo->prepare("
        SELECT 
            CONCAT(u.ime, ' ', u.prezime) as terapeut,
            COUNT(tr.id) as broj_tretmana,
            COUNT(DISTINCT tr.karton_id) as broj_kartona
        FROM users u
        LEFT JOIN tretmani tr ON u.id = tr.terapeut_id
        WHERE u.uloga = 'terapeut'
        AND (tr.datum IS NULL OR DATE(tr.datum) BETWEEN ? AND ?)
        GROUP BY u.id, u.ime, u.prezime
        ORDER BY broj_tretmana DESC
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $terapeuti_tretmani = $stmt->fetchAll();
    
    // Broj novih kartona po mjesecima
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(datum_otvaranja, '%Y-%m') as mesec,
            COUNT(*) as novi_kartoni
        FROM kartoni
        WHERE datum_otvaranja BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(datum_otvaranja, '%Y-%m')
        ORDER BY mesec
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $novi_kartoni = $stmt->fetchAll();
    
    // Osnovne statistike
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM kartoni WHERE datum_otvaranja BETWEEN ? AND ?");
    $stmt->execute([$datum_od, $datum_do]);
    $ukupno_kartona = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tretmani WHERE DATE(datum) BETWEEN ? AND ?");
    $stmt->execute([$datum_od, $datum_do]);
    $ukupno_tretmana = $stmt->fetchColumn();
    
    // Ukupan broj različitih dijagnoza koje se koriste
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT kd.dijagnoza_id) 
        FROM karton_dijagnoze kd
        JOIN kartoni k ON kd.karton_id = k.id
        WHERE k.datum_otvaranja BETWEEN ? AND ?
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $ukupno_razlicitih_dijagnoza = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    error_log("Greška pri generiranju medicinskog izvještaja: " . $e->getMessage());
    $dijagnoze = [];
    $pacijenti_tretmani = [];
    $terapeuti_tretmani = [];
    $novi_kartoni = [];
    $ukupno_kartona = 0;
    $ukupno_tretmana = 0;
    $ukupno_razlicitih_dijagnoza = 0;
}

$title = "Medicinski izvještaj";

ob_start();
require_once __DIR__ . '/../views/izvjestaji/medicinski.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>