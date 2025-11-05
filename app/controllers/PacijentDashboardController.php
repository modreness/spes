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

try {
    // Predstojeci termini - samo budući termini
    $stmt = $pdo->prepare("
        SELECT t.*, 
               CONCAT(te.ime, ' ', te.prezime) as terapeut_ime,
               c.naziv as usluga,
               DATE(t.datum_vrijeme) as datum,
               TIME(t.datum_vrijeme) as vrijeme
        FROM termini t
        JOIN users te ON t.terapeut_id = te.id
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE t.pacijent_id = ? AND t.datum_vrijeme >= NOW()
        ORDER BY t.datum_vrijeme ASC
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $dashboard_data['predstojeci_termini'] = $stmt->fetchAll();
    
    // Moj aktivan karton
    $stmt = $pdo->prepare("
        SELECT k.*, 
               DATE_FORMAT(k.datum_otvaranja, '%d.%m.%Y') as datum_otvaranja_format
        FROM kartoni k 
        WHERE k.pacijent_id = ? 
        ORDER BY k.datum_otvaranja DESC 
        LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $dashboard_data['aktivan_karton'] = $stmt->fetch();
    
    // Poslednji tretmani (samo zadnja 3)
    if ($dashboard_data['aktivan_karton']) {
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   DATE_FORMAT(t.datum, '%d.%m.%Y') as datum_format,
                   COALESCE(CONCAT(ter.ime, ' ', ter.prezime), 
                           CONCAT(t.terapeut_ime, ' ', t.terapeut_prezime), 
                           'N/A') as terapeut_ime
            FROM tretmani t
            LEFT JOIN users ter ON t.terapeut_id = ter.id
            WHERE t.karton_id = ?
            ORDER BY t.datum DESC
            LIMIT 3
        ");
        $stmt->execute([$dashboard_data['aktivan_karton']['id']]);
        $dashboard_data['poslednji_tretmani'] = $stmt->fetchAll();
    } else {
        $dashboard_data['poslednji_tretmani'] = [];
    }
    
    // Moji nalazi (samo zadnja 3)
    $stmt = $pdo->prepare("
        SELECT n.*, 
               DATE_FORMAT(n.datum_upload, '%d.%m.%Y') as datum_upload_format,
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
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM termini 
        WHERE pacijent_id = ? AND datum_vrijeme >= NOW()
    ");
    $stmt->execute([$user['id']]);
    $dashboard_data['predstojeci_termini_broj'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM tretmani t
        JOIN kartoni k ON t.karton_id = k.id
        WHERE k.pacijent_id = ?
    ");
    $stmt->execute([$user['id']]);
    $dashboard_data['ukupno_tretmana'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM nalazi WHERE pacijent_id = ?");
    $stmt->execute([$user['id']]);
    $dashboard_data['broj_nalaza'] = $stmt->fetchColumn();
    
    // Zadnji termin - kad sam bio zadnji put
    $stmt = $pdo->prepare("
        SELECT MAX(t.datum_vrijeme) as zadnji_termin
        FROM termini t
        WHERE t.pacijent_id = ? AND t.status = 'obavljen'
    ");
    $stmt->execute([$user['id']]);
    $zadnji_termin = $stmt->fetchColumn();
    $dashboard_data['zadnji_termin'] = $zadnji_termin ? date('d.m.Y', strtotime($zadnji_termin)) : null;
    
    // Sljedeći termin - kad idem sljedeći put
    $stmt = $pdo->prepare("
        SELECT MIN(t.datum_vrijeme) as sljedeci_termin
        FROM termini t
        WHERE t.pacijent_id = ? AND t.datum_vrijeme >= NOW() AND t.status = 'zakazan'
    ");
    $stmt->execute([$user['id']]);
    $sljedeci_termin = $stmt->fetchColumn();
    $dashboard_data['sljedeci_termin'] = $sljedeci_termin ? date('d.m.Y H:i', strtotime($sljedeci_termin)) : null;
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju pacijent dashboard podataka: " . $e->getMessage());
    $dashboard_data = [
        'predstojeci_termini' => [],
        'aktivan_karton' => null,
        'poslednji_tretmani' => [],
        'moji_nalazi' => [],
        'predstojeci_termini_broj' => 0,
        'ukupno_tretmana' => 0,
        'broj_nalaza' => 0,
        'zadnji_termin' => null,
        'sljedeci_termin' => null
    ];
}

$title = "Moj zdravstveni portal";

ob_start();
require_once __DIR__ . '/../views/dashboard/pacijent.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';