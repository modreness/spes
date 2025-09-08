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
$mesec = $_GET['mesec'] ?? date('m');
$godina = $_GET['godina'] ?? date('Y');
$terapeut_filter = $_GET['terapeut'] ?? '';

// Validacija
$mesec = max(1, min(12, intval($mesec)));
$godina = max(2020, min(2030, intval($godina)));

try {
    // Dohvati terapeute za filter
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' ORDER BY ime, prezime");
    $stmt->execute();
    $terapeuti = $stmt->fetchAll();
    
    // Pripremi datum range za mesec
    $prvi_dan = "$godina-" . str_pad($mesec, 2, '0', STR_PAD_LEFT) . "-01";
    $poslednji_dan = date('Y-m-t', strtotime($prvi_dan));
    
    // Query sa filterom
    $where_clause = "DATE(t.datum_vrijeme) BETWEEN ? AND ?";
    $params = [$prvi_dan, $poslednji_dan];
    
    if ($terapeut_filter) {
        $where_clause .= " AND t.terapeut_id = ?";
        $params[] = $terapeut_filter;
    }
    
    // Dohvati termine
    $stmt = $pdo->prepare("
        SELECT t.*, 
               CONCAT(u_pacijent.ime, ' ', u_pacijent.prezime) as pacijent_ime,
               CONCAT(u_terapeut.ime, ' ', u_terapeut.prezime) as terapeut_ime,
               c.naziv as usluga_naziv,
               c.cijena as usluga_cijena
        FROM termini t
        LEFT JOIN users u_pacijent ON t.pacijent_id = u_pacijent.id
        LEFT JOIN users u_terapeut ON t.terapeut_id = u_terapeut.id
        LEFT JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE $where_clause
        ORDER BY t.datum_vrijeme ASC
    ");
    $stmt->execute($params);
    $termini = $stmt->fetchAll();
    
    // Organizuj termine po danima
    $termini_po_danu = [];
    foreach ($termini as $termin) {
        $dan = date('j', strtotime($termin['datum_vrijeme']));
        $termini_po_danu[$dan][] = $termin;
    }
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju kalendara: " . $e->getMessage());
    $termini_po_danu = [];
    $terapeuti = [];
}

$title = "Kalendar termina";

ob_start();
require_once __DIR__ . '/../views/termini/kalendar.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>