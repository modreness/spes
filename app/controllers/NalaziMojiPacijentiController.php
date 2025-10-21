<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

// Samo terapeut može videti nalaze svojih pacijenata
if ($user['uloga'] !== 'terapeut') {
    header('Location: /dashboard');
    exit;
}

// Parametri za filtriranje
$pacijent_id = $_GET['pacijent_id'] ?? '';
$datum_od = $_GET['datum_od'] ?? '';
$datum_do = $_GET['datum_do'] ?? '';

try {
    // Osnovni upit - nalazi pacijenata sa kojima sam radio
    $where_conditions = [
        "EXISTS (
            SELECT 1 FROM termini t 
            WHERE t.pacijent_id = n.pacijent_id AND t.terapeut_id = ?
        )"
    ];
    $params = [$user['id']];
    
    // Dodaj filtere
    if ($pacijent_id) {
        $where_conditions[] = "n.pacijent_id = ?";
        $params[] = $pacijent_id;
    }
    
    if ($datum_od) {
        $where_conditions[] = "n.datum_upload >= ?";
        $params[] = $datum_od;
    }
    
    if ($datum_do) {
        $where_conditions[] = "n.datum_upload <= ?";
        $params[] = $datum_do;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Dohvati nalaze
    $stmt = $pdo->prepare("
        SELECT n.*, 
               CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
               CONCAT(d.ime, ' ', d.prezime) as dodao_ime,
               k.broj_upisa,
               DATE_FORMAT(n.datum_upload, '%d.%m.%Y %H:%i') as datum_upload_format
        FROM nalazi n
        JOIN users p ON n.pacijent_id = p.id
        LEFT JOIN users d ON n.dodao_id = d.id
        LEFT JOIN kartoni k ON k.pacijent_id = n.pacijent_id
        WHERE $where_clause
        ORDER BY n.datum_upload DESC
    ");
    $stmt->execute($params);
    $nalazi = $stmt->fetchAll();
    
    // Statistike
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM nalazi n
        WHERE EXISTS (
            SELECT 1 FROM termini t 
            WHERE t.pacijent_id = n.pacijent_id AND t.terapeut_id = ?
        )
    ");
    $stmt->execute([$user['id']]);
    $ukupno_nalaza = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT n.pacijent_id) FROM nalazi n
        WHERE EXISTS (
            SELECT 1 FROM termini t 
            WHERE t.pacijent_id = n.pacijent_id AND t.terapeut_id = ?
        )
    ");
    $stmt->execute([$user['id']]);
    $pacijenti_sa_nalazima = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM nalazi n
        WHERE EXISTS (
            SELECT 1 FROM termini t 
            WHERE t.pacijent_id = n.pacijent_id AND t.terapeut_id = ?
        ) AND DATE(n.datum_upload) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$user['id']]);
    $novi_nalazi_30_dana = $stmt->fetchColumn();
    
    // Lista pacijenata za filter dropdown
    $stmt = $pdo->prepare("
        SELECT DISTINCT p.id, CONCAT(p.ime, ' ', p.prezime) as ime_prezime
        FROM users p
        WHERE EXISTS (
            SELECT 1 FROM termini t 
            WHERE t.pacijent_id = p.id AND t.terapeut_id = ?
        ) AND EXISTS (
            SELECT 1 FROM nalazi n 
            WHERE n.pacijent_id = p.id
        )
        ORDER BY p.ime, p.prezime
    ");
    $stmt->execute([$user['id']]);
    $pacijenti_lista = $stmt->fetchAll();
    
    // Grupiraj nalaze po pacijentima
    $nalazi_po_pacijentima = [];
    foreach ($nalazi as $nalaz) {
        $nalazi_po_pacijentima[$nalaz['pacijent_ime']][] = $nalaz;
    }
    
    // Najčešći tipovi nalaza
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN n.file_path LIKE '%.pdf' THEN 'PDF dokumenti'
                WHEN n.file_path LIKE '%.jpg' OR n.file_path LIKE '%.jpeg' OR n.file_path LIKE '%.png' THEN 'Slike/RTG'
                WHEN n.file_path LIKE '%.doc%' THEN 'Word dokumenti'
                ELSE 'Ostalo'
            END as tip_nalaza,
            COUNT(*) as broj
        FROM nalazi n
        WHERE EXISTS (
            SELECT 1 FROM termini t 
            WHERE t.pacijent_id = n.pacijent_id AND t.terapeut_id = ?
        )
        GROUP BY tip_nalaza
        ORDER BY broj DESC
    ");
    $stmt->execute([$user['id']]);
    $tipovi_nalaza = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju nalaza: " . $e->getMessage());
    $nalazi = [];
    $ukupno_nalaza = 0;
    $pacijenti_sa_nalazima = 0;
    $novi_nalazi_30_dana = 0;
    $pacijenti_lista = [];
    $nalazi_po_pacijentima = [];
    $tipovi_nalaza = [];
}

$title = "Nalazi mojih pacijenata";

ob_start();
require_once __DIR__ . '/../views/kartoni/nalazi-moji-pacijenti.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>