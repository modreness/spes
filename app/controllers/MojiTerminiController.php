<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../helpers/permissions.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

// Samo pacijent može pristupiti ovom kontroleru
if ($user['uloga'] !== 'pacijent') {
    header('Location: /dashboard');
    exit;
}

// Provjeri permissions
if (!hasPermission($user, 'pregled_vlastiti_tretmani')) {
    header('Location: /dashboard?error=no_permission');
    exit;
}

// Parametri za filtriranje
$status_filter = $_GET['status'] ?? '';
$datum_od = $_GET['datum_od'] ?? '';
$datum_do = $_GET['datum_do'] ?? '';
$show = $_GET['show'] ?? 'all'; // all, budući, prošli

try {
    // Osnovni upit
    $where_conditions = ["t.pacijent_id = ?"];
    $params = [$user['id']];
    
    // Dodaj filtere
    if ($status_filter) {
        $where_conditions[] = "t.status = ?";
        $params[] = $status_filter;
    }
    
    if ($datum_od) {
        $where_conditions[] = "DATE(t.datum_vrijeme) >= ?";
        $params[] = $datum_od;
    }
    
    if ($datum_do) {
        $where_conditions[] = "DATE(t.datum_vrijeme) <= ?";
        $params[] = $datum_do;
    }
    
    // Filter po vremenu
    if ($show === 'budući') {
        $where_conditions[] = "t.datum_vrijeme >= NOW()";
    } elseif ($show === 'prošli') {
        $where_conditions[] = "t.datum_vrijeme < NOW()";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Dohvati termine sa svim detaljima
    $stmt = $pdo->prepare("
        SELECT t.*, 
               DATE(t.datum_vrijeme) as datum,
               TIME(t.datum_vrijeme) as vrijeme,
               DATE_FORMAT(t.datum_vrijeme, '%d.%m.%Y') as datum_format,
               DATE_FORMAT(t.datum_vrijeme, '%H:%i') as vrijeme_format,
               CONCAT(te.ime, ' ', te.prezime) as terapeut_ime,
               c.naziv as usluga_naziv,
               c.cijena as usluga_cijena,
               CASE 
                   WHEN t.datum_vrijeme >= NOW() THEN 'budući'
                   ELSE 'prošli'
               END as tip_termina
        FROM termini t
        JOIN users te ON t.terapeut_id = te.id
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE $where_clause
        ORDER BY t.datum_vrijeme DESC
    ");
    $stmt->execute($params);
    $moji_termini = $stmt->fetchAll();
    
    // Statistike za dashboard kartice
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as ukupno_termina,
            COUNT(CASE WHEN t.datum_vrijeme >= NOW() THEN 1 END) as buduci_termini,
            COUNT(CASE WHEN t.datum_vrijeme < NOW() THEN 1 END) as prosli_termini,
            COUNT(CASE WHEN t.status = 'zakazan' AND t.datum_vrijeme >= NOW() THEN 1 END) as zakazani_termini,
            COUNT(CASE WHEN t.status = 'obavljen' THEN 1 END) as obavljeni_termini,
            COUNT(CASE WHEN t.status = 'otkazan' THEN 1 END) as otkazani_termini
        FROM termini t
        WHERE t.pacijent_id = ?
    ");
    $stmt->execute([$user['id']]);
    $statistike = $stmt->fetch();
    
    // Sljedeći termin
    $stmt = $pdo->prepare("
        SELECT t.*, 
               DATE_FORMAT(t.datum_vrijeme, '%d.%m.%Y %H:%i') as datum_vrijeme_format,
               CONCAT(te.ime, ' ', te.prezime) as terapeut_ime,
               c.naziv as usluga_naziv
        FROM termini t
        JOIN users te ON t.terapeut_id = te.id
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE t.pacijent_id = ? 
        AND t.datum_vrijeme >= NOW() 
        AND t.status = 'zakazan'
        ORDER BY t.datum_vrijeme ASC
        LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $sljedeci_termin = $stmt->fetch();
    
    // Zadnji termin
    $stmt = $pdo->prepare("
        SELECT t.*, 
               DATE_FORMAT(t.datum_vrijeme, '%d.%m.%Y %H:%i') as datum_vrijeme_format,
               CONCAT(te.ime, ' ', te.prezime) as terapeut_ime,
               c.naziv as usluga_naziv
        FROM termini t
        JOIN users te ON t.terapeut_id = te.id
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE t.pacijent_id = ? 
        AND t.datum_vrijeme < NOW() 
        AND t.status = 'obavljen'
        ORDER BY t.datum_vrijeme DESC
        LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $zadnji_termin = $stmt->fetch();
    
    // Grupiraj termine po statusu za lakši prikaz
    $termini_po_statusu = [];
    foreach ($moji_termini as $termin) {
        $termini_po_statusu[$termin['status']][] = $termin;
    }
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju termina: " . $e->getMessage());
    $moji_termini = [];
    $statistike = [
        'ukupno_termina' => 0,
        'buduci_termini' => 0,
        'prosli_termini' => 0,
        'zakazani_termini' => 0,
        'obavljeni_termini' => 0,
        'otkazani_termini' => 0
    ];
    $sljedeci_termin = null;
    $zadnji_termin = null;
    $termini_po_statusu = [];
}

$title = "Moji termini";

ob_start();
require_once __DIR__ . '/../views/termini/moji-termini.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';