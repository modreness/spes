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
    case 'custom':
        // Koristićemo datume iz GET parametara
        break;
}

try {
    // Statistike termina po statusu
    $stmt = $pdo->prepare("
        SELECT 
            status,
            COUNT(*) as broj
        FROM termini 
        WHERE DATE(datum_vrijeme) BETWEEN ? AND ?
        GROUP BY status
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $statistike_statusa = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Iskorišćenost terapeuta
    $stmt = $pdo->prepare("
        SELECT 
            u.ime,
            u.prezime,
            COUNT(t.id) as ukupno_termina,
            SUM(CASE WHEN t.status = 'obavljen' THEN 1 ELSE 0 END) as obavljeni,
            SUM(CASE WHEN t.status = 'otkazan' THEN 1 ELSE 0 END) as otkazani,
            SUM(CASE WHEN t.status = 'zakazan' THEN 1 ELSE 0 END) as zakazani
        FROM users u
        LEFT JOIN termini t ON u.id = t.terapeut_id 
            AND DATE(t.datum_vrijeme) BETWEEN ? AND ?
        WHERE u.uloga = 'terapeut'
        GROUP BY u.id, u.ime, u.prezime
        ORDER BY obavljeni DESC
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $iskoriscenost_terapeuta = $stmt->fetchAll();
    
    // Najpopularnije usluge
    $stmt = $pdo->prepare("
        SELECT 
            c.naziv as usluga,
            k.naziv as kategorija,
            COUNT(*) as broj_zahtjeva,
            SUM(CASE WHEN t.status = 'obavljen' THEN 1 ELSE 0 END) as obavljeno,
            ROUND((SUM(CASE WHEN t.status = 'obavljen' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as procenat_uspjeha
        FROM termini t
        JOIN cjenovnik c ON t.usluga_id = c.id
        LEFT JOIN kategorije_usluga k ON c.kategorija_id = k.id
        WHERE DATE(t.datum_vrijeme) BETWEEN ? AND ?
        GROUP BY c.id, c.naziv, k.naziv
        ORDER BY broj_zahtjeva DESC
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $popularne_usluge = $stmt->fetchAll();
    
    // Statistike po danima u sedmici
    $stmt = $pdo->prepare("
        SELECT 
            DAYNAME(datum_vrijeme) as dan,
            COUNT(*) as ukupno,
            SUM(CASE WHEN status = 'obavljen' THEN 1 ELSE 0 END) as obavljeno
        FROM termini 
        WHERE DATE(datum_vrijeme) BETWEEN ? AND ?
        GROUP BY DAYOFWEEK(datum_vrijeme), DAYNAME(datum_vrijeme)
        ORDER BY DAYOFWEEK(datum_vrijeme)
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $statistike_po_danima = $stmt->fetchAll();
    
    // Prosečno vreme između termina
    $stmt = $pdo->prepare("
        SELECT 
            AVG(TIMESTAMPDIFF(MINUTE, 
                LAG(datum_vrijeme) OVER (PARTITION BY terapeut_id ORDER BY datum_vrijeme), 
                datum_vrijeme
            )) as prosecno_vreme_minuta
        FROM termini 
        WHERE DATE(datum_vrijeme) BETWEEN ? AND ?
        AND status IN ('zakazan', 'obavljen')
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $prosecno_vreme = $stmt->fetchColumn() ?: 0;
    
} catch (PDOException $e) {
    error_log("Greška pri generiranju operativnog izvještaja: " . $e->getMessage());
    $statistike_statusa = [];
    $iskoriscenost_terapeuta = [];
    $popularne_usluge = [];
    $statistike_po_danima = [];
    $prosecno_vreme = 0;
}

$title = "Operativni izvještaj";

ob_start();
require_once __DIR__ . '/../views/izvjestaji/operativni.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>