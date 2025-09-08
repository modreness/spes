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

try {
    // Statistike za dashboard
    $danas = date('Y-m-d');
    
    // Termini danas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE DATE(datum_vrijeme) = ? AND status = 'zakazan'");
    $stmt->execute([$danas]);
    $termini_danas = $stmt->fetchColumn();
    
    // Termini ove sedmice
    $sedmica_od = date('Y-m-d', strtotime('monday this week'));
    $sedmica_do = date('Y-m-d', strtotime('sunday this week'));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE DATE(datum_vrijeme) BETWEEN ? AND ? AND status = 'zakazan'");
    $stmt->execute([$sedmica_od, $sedmica_do]);
    $termini_sedmica = $stmt->fetchColumn();
    
    // Ukupno aktivnih termina
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE status IN ('zakazan', 'slobodan')");
    $stmt->execute();
    $ukupno_termina = $stmt->fetchColumn();
    
    // Najnoviji termini (poslednji zakazani)
    $stmt = $pdo->prepare("
        SELECT t.*, 
               CONCAT(u_pacijent.ime, ' ', u_pacijent.prezime) as pacijent_ime,
               CONCAT(u_terapeut.ime, ' ', u_terapeut.prezime) as terapeut_ime,
               c.naziv as usluga_naziv
        FROM termini t
        LEFT JOIN users u_pacijent ON t.pacijent_id = u_pacijent.id
        LEFT JOIN users u_terapeut ON t.terapeut_id = u_terapeut.id
        LEFT JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE t.status = 'zakazan'
        ORDER BY t.datum_vrijeme DESC
        LIMIT 5
    ");
    $stmt->execute();
    $najnoviji_termini = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju statistika termina: " . $e->getMessage());
    $termini_danas = 0;
    $termini_sedmica = 0;
    $ukupno_termina = 0;
    $najnoviji_termini = [];
}

$title = "Termini - Dashboard";

ob_start();
require_once __DIR__ . '/../views/termini/dashboard.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>