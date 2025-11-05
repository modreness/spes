<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../helpers/permissions.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

// Samo pacijent može pristupiti ovom dashboard-u
if ($user['uloga'] !== 'pacijent') {
    header('Location: /dashboard');
    exit;
}

$dashboard_data = [];

try {
    // KOPIRAM ORIGINALNU LOGIKU IZ DashboardController.php - pacijent sekcija
    
    // Pacijent podaci - moji termini, moj karton, moji nalazi
    $stmt = $pdo->prepare("
        SELECT t.*, 
               CONCAT(te.ime, ' ', te.prezime) as terapeut_ime,
               c.naziv as usluga,
               DATE(t.datum_vrijeme) as datum,
               TIME(t.datum_vrijeme) as vrijeme
        FROM termini t
        JOIN users te ON t.terapeut_id = te.id
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE t.pacijent_id = ? AND t.datum_vrijeme >= CURDATE()
        ORDER BY t.datum_vrijeme ASC
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $dashboard_data['predstojeci_termini'] = $stmt->fetchAll();
    
    // Moj aktivan karton
    $stmt = $pdo->prepare("SELECT * FROM kartoni WHERE pacijent_id = ? ORDER BY datum_otvaranja DESC LIMIT 1");
    $stmt->execute([$user['id']]);
    $dashboard_data['aktivan_karton'] = $stmt->fetch();
    
    // Poslednji tretmani
    $stmt = $pdo->prepare("
        SELECT t.*, 
               CONCAT(ter.ime, ' ', ter.prezime) as terapeut_ime
        FROM tretmani t
        LEFT JOIN users ter ON t.terapeut_id = ter.id
        WHERE EXISTS (
            SELECT 1 FROM kartoni k 
            WHERE k.id = t.karton_id AND k.pacijent_id = ?
        )
        ORDER BY t.datum DESC
        LIMIT 3
    ");
    $stmt->execute([$user['id']]);
    $dashboard_data['poslednji_tretmani'] = $stmt->fetchAll();
    
    // Moji nalazi
    $stmt = $pdo->prepare("
        SELECT n.*, 
               CONCAT(d.ime, ' ', d.prezime) as dodao_ime
        FROM nalazi n
        LEFT JOIN users d ON n.dodao_id = d.id
        WHERE n.pacijent_id = ?
        ORDER BY n.datum_upload DESC
        LIMIT 3
    ");
    $stmt->execute([$user['id']]);
    $dashboard_data['moji_nalazi'] = $stmt->fetchAll();
    
    // Statistike
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE pacijent_id = ? AND status = 'obavljen'");
    $stmt->execute([$user['id']]);
    $dashboard_data['ukupno_tretmana'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE pacijent_id = ? AND datum_vrijeme >= CURDATE()");
    $stmt->execute([$user['id']]);
    $dashboard_data['predstojeci_termini_broj'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM nalazi WHERE pacijent_id = ?");
    $stmt->execute([$user['id']]);
    $dashboard_data['broj_nalaza'] = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju pacijent dashboard podataka: " . $e->getMessage());
    $dashboard_data = [];
}

$title = "Moj zdravstveni portal";

ob_start();
require_once __DIR__ . '/../views/dashboard/pacijent.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';