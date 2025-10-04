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
$status_filter = $_GET['status'] ?? '';
$terapeut_filter = $_GET['terapeut'] ?? '';
$datum_od = $_GET['datum_od'] ?? date('Y-m-d');
$datum_do = $_GET['datum_do'] ?? date('Y-m-d', strtotime('+30 days'));

try {
    // Dohvati terapeute za filter
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' AND aktivan = 1 ORDER BY ime, prezime");
    $stmt->execute();
    $terapeuti = $stmt->fetchAll();
    
    // Builduj WHERE clause
    $where_conditions = ["DATE(t.datum_vrijeme) BETWEEN ? AND ?"];
    $params = [$datum_od, $datum_do];
    
    if ($status_filter) {
        $where_conditions[] = "t.status = ?";
        $params[] = $status_filter;
    }
    
    if ($terapeut_filter) {
        $where_conditions[] = "t.terapeut_id = ?";
        $params[] = $terapeut_filter;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Dohvati termine - DODATO: placeno_iz_paketa i stvarna_cijena
    $stmt = $pdo->prepare("
        SELECT t.*, 
               CONCAT(u_pacijent.ime, ' ', u_pacijent.prezime) as pacijent_ime,
               CONCAT(u_terapeut.ime, ' ', u_terapeut.prezime) as terapeut_ime,
               c.naziv as usluga_naziv,
               c.cijena as usluga_cijena,
               t.placeno_iz_paketa,
               t.stvarna_cijena
        FROM termini t
        LEFT JOIN users u_pacijent ON t.pacijent_id = u_pacijent.id
        LEFT JOIN users u_terapeut ON t.terapeut_id = u_terapeut.id
        LEFT JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE $where_clause
        ORDER BY t.datum_vrijeme ASC
    ");
    $stmt->execute($params);
    $termini = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju liste termina: " . $e->getMessage());
    $termini = [];
    $terapeuti = [];
}

$title = "Lista termina";

ob_start();
require_once __DIR__ . '/../views/termini/lista.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>