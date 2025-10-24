<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

// Samo terapeut može videti svoje izvještaje
if ($user['uloga'] !== 'terapeut') {
    header('Location: /dashboard');
    exit;
}

try {
    $danas = date('Y-m-d');
    $ovaj_mjesec = date('Y-m');
    $prethodnih_30_dana = date('Y-m-d', strtotime('-30 days'));
    
    // Osnovne statistike
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE terapeut_id = ? AND status = 'obavljen'");
    $stmt->execute([$user['id']]);
    $ukupno_termina = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tretmani WHERE terapeut_id = ?");
    $stmt->execute([$user['id']]);
    $ukupno_tretmana = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT k.pacijent_id) 
        FROM tretmani tr
        JOIN kartoni k ON tr.karton_id = k.id
        WHERE tr.terapeut_id = ?
    ");
    $stmt->execute([$user['id']]);
    $broj_pacijenata = $stmt->fetchColumn();
    
    // Termini ovaj mjesec
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM termini 
        WHERE terapeut_id = ? AND DATE(datum_vrijeme) LIKE ? AND status = 'obavljen'
    ");
    $stmt->execute([$user['id'], "$ovaj_mjesec%"]);
    $termini_ovaj_mjesec = $stmt->fetchColumn();
    
    // Tretmani ovaj mjesec
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM tretmani 
        WHERE terapeut_id = ? AND DATE(datum) LIKE ?
    ");
    $stmt->execute([$user['id'], "$ovaj_mjesec%"]);
    $tretmani_ovaj_mjesec = $stmt->fetchColumn();
    
    // Najčešće usluge koje radim
    $stmt = $pdo->prepare("
        SELECT c.naziv, COUNT(*) as broj_termina
        FROM termini t
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE t.terapeut_id = ? AND t.status = 'obavljen'
        GROUP BY c.id
        ORDER BY broj_termina DESC
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $top_usluge = $stmt->fetchAll();
    
    // Statistike po mjesecima (poslednih 6 mjeseci)
    $mjesecne_statistike = [];
    for ($i = 5; $i >= 0; $i--) {
        $mjesec = date('Y-m', strtotime("-$i months"));
        $mjesec_naziv = date('M Y', strtotime("-$i months"));
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM termini 
            WHERE terapeut_id = ? AND DATE(datum_vrijeme) LIKE ? AND status = 'obavljen'
        ");
        $stmt->execute([$user['id'], "$mjesec%"]);
        $termini = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM tretmani 
            WHERE terapeut_id = ? AND DATE(datum) LIKE ?
        ");
        $stmt->execute([$user['id'], "$mjesec%"]);
        $tretmani = $stmt->fetchColumn();
        
        $mjesecne_statistike[] = [
            'mjesec' => $mjesec_naziv,
            'termini' => $termini,
            'tretmani' => $tretmani
        ];
    }
    
    // Aktivnost poslednih 30 dana (po danima)
    $dnevne_statistike = [];
    for ($i = 29; $i >= 0; $i--) {
        $dan = date('Y-m-d', strtotime("-$i days"));
        $dan_naziv = date('d.m', strtotime("-$i days"));
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM termini 
            WHERE terapeut_id = ? AND DATE(datum_vrijeme) = ? AND status = 'obavljen'
        ");
        $stmt->execute([$user['id'], $dan]);
        $termini = $stmt->fetchColumn();
        
        $dnevne_statistike[] = [
            'dan' => $dan_naziv,
            'termini' => $termini
        ];
    }
    
    // Pacijenti sa kojima najčešće radim
    $stmt = $pdo->prepare("
        SELECT CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
               COUNT(DISTINCT t.id) as broj_termina,
               COUNT(DISTINCT tr.id) as broj_tretmana,
               MAX(COALESCE(tr.datum, t.datum_vrijeme)) as poslednja_aktivnost
        FROM users p
        LEFT JOIN termini t ON t.pacijent_id = p.id AND t.terapeut_id = ?
        LEFT JOIN kartoni k ON k.pacijent_id = p.id
        LEFT JOIN tretmani tr ON tr.karton_id = k.id AND tr.terapeut_id = ?
        WHERE (t.id IS NOT NULL OR tr.id IS NOT NULL)
        GROUP BY p.id
        ORDER BY broj_termina DESC, broj_tretmana DESC
        LIMIT 10
    ");
    $stmt->execute([$user['id'], $user['id']]);
    $top_pacijenti = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju izvještaja: " . $e->getMessage());
    $ukupno_termina = 0;
    $ukupno_tretmana = 0;
    $broj_pacijenata = 0;
    $termini_ovaj_mjesec = 0;
    $tretmani_ovaj_mjesec = 0;
    $top_usluge = [];
    $mjesecne_statistike = [];
    $dnevne_statistike = [];
    $top_pacijenti = [];
}

$title = "Moji izvještaji";

ob_start();
require_once __DIR__ . '/../views/izvjestaji/terapeut.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>