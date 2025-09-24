<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

// Dodaj terapeuta u dozvoljene uloge
if (!in_array($user['uloga'], ['admin', 'recepcioner', 'terapeut'])) {
    header('Location: /dashboard');
    exit;
}

// Dohvati statistike
try {
    if ($user['uloga'] === 'terapeut') {
        // Za terapeuta - samo osnovne brojke
        $broj_terapeuta = 1; // On sam
        $rasporedeni_terapeuti = 1;
    } else {
        // Za admin/recepcioner - sve kao i pre
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE uloga = 'terapeut' AND aktivan = 1");
        $stmt->execute();
        $broj_terapeuta = $stmt->fetchColumn();
        
        $datum_od = date('Y-m-d', strtotime('monday this week'));
        $datum_do = date('Y-m-d', strtotime('sunday this week'));
        
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT terapeut_id) FROM rasporedi_sedmicni WHERE datum_od >= ? AND datum_do <= ?");
        $stmt->execute([$datum_od, $datum_do]);
        $rasporedeni_terapeuti = $stmt->fetchColumn();
    }
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju statistika: " . $e->getMessage());
    $broj_terapeuta = 0;
    $rasporedeni_terapeuti = 0;
}

$title = "Raspored terapeuta";

ob_start();
require_once __DIR__ . '/../views/raspored/dashboard.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>