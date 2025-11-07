<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

//if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
if (!in_array($user['uloga'], ['admin'])) {
    header('Location: /dashboard');
    exit;
}

// Filter parametri
$period = $_GET['period'] ?? 'ovaj_mjesec';
$datum_od = $_GET['datum_od'] ?? '';
$datum_do = $_GET['datum_do'] ?? '';

// Izračunaj datume na osnovu perioda
switch ($period) {
    case 'danas':
        $datum_od = $datum_do = date('Y-m-d');
        break;
    case 'ova_sedmica':
        $datum_od = date('Y-m-d', strtotime('monday this week'));
        $datum_do = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'ovaj_mjesec':
        $datum_od = date('Y-m-01');
        $datum_do = date('Y-m-t');
        break;
    case 'prosli_mjesec':
        $datum_od = date('Y-m-01', strtotime('last month'));
        $datum_do = date('Y-m-t', strtotime('last month'));
        break;
    case 'ova_godina':
        $datum_od = date('Y-01-01');
        $datum_do = date('Y-12-31');
        break;
    case 'custom':
        // Koristićemo datume iz GET parametara
        break;
}

try {
    // PRIHOD OD POJEDINAČNIH TERMINA
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(t.stvarna_cijena), 0) as ukupno,
            COUNT(*) as broj_termina
        FROM termini t
        WHERE DATE(t.datum_vrijeme) BETWEEN ? AND ? 
        AND t.status = 'obavljen'
        AND t.placeno_iz_paketa = 0
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $prihod_termini = $stmt->fetch();
    
    // PRIHOD OD PRODAJE PAKETA
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(c.cijena), 0) as ukupno,
            COUNT(*) as broj_paketa
        FROM kupljeni_paketi kp
        JOIN cjenovnik c ON kp.usluga_id = c.id
        WHERE DATE(kp.datum_kupovine) BETWEEN ? AND ?
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $prihod_paketi = $stmt->fetch();
    
    // UKUPNI PRIHODI
    $ukupni_prihodi = [
        'ukupno' => $prihod_termini['ukupno'] + $prihod_paketi['ukupno'],
        'broj_termina' => $prihod_termini['broj_termina'],
        'termini_prihod' => $prihod_termini['ukupno'],
        'paketi_prihod' => $prihod_paketi['ukupno'],
        'broj_paketa' => $prihod_paketi['broj_paketa']
    ];
    
    // Prihodi po danima - UKUPNO (termini + paketi)
    $stmt = $pdo->prepare("
        SELECT 
            dan,
            SUM(prihod) as prihod,
            SUM(termini) as termini
        FROM (
            -- Prihod od termina
            SELECT 
                DATE(t.datum_vrijeme) as dan,
                SUM(t.stvarna_cijena) as prihod,
                COUNT(*) as termini
            FROM termini t
            WHERE DATE(t.datum_vrijeme) BETWEEN ? AND ? 
            AND t.status = 'obavljen'
            AND t.placeno_iz_paketa = 0
            GROUP BY DATE(t.datum_vrijeme)
            
            UNION ALL
            
            -- Prihod od paketa
            SELECT 
                DATE(kp.datum_kupovine) as dan,
                SUM(c.cijena) as prihod,
                0 as termini
            FROM kupljeni_paketi kp
            JOIN cjenovnik c ON kp.usluga_id = c.id
            WHERE DATE(kp.datum_kupovine) BETWEEN ? AND ?
            GROUP BY DATE(kp.datum_kupovine)
        ) combined
        GROUP BY dan
        ORDER BY dan ASC
    ");
    $stmt->execute([$datum_od, $datum_do, $datum_od, $datum_do]);
    $prihodi_po_danima = $stmt->fetchAll();
    
    // Prihodi po uslugama - SAMO POJEDINAČNI TERMINI
    $stmt = $pdo->prepare("
        SELECT 
            c.naziv as usluga,
            k.naziv as kategorija,
            COUNT(*) as broj_termina,
            SUM(t.stvarna_cijena) as ukupno,
            AVG(t.stvarna_cijena) as prosek
        FROM termini t
        JOIN cjenovnik c ON t.usluga_id = c.id
        LEFT JOIN kategorije_usluga k ON c.kategorija_id = k.id
        WHERE DATE(t.datum_vrijeme) BETWEEN ? AND ? 
        AND t.status = 'obavljen'
        AND t.placeno_iz_paketa = 0
        GROUP BY c.id, c.naziv, k.naziv
        ORDER BY ukupno DESC
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $prihodi_po_uslugama = $stmt->fetchAll();
    
    // Prihodi po terapeutima - SAMO POJEDINAČNI TERMINI
    $stmt = $pdo->prepare("
        SELECT 
            CONCAT(u.ime, ' ', u.prezime) as terapeut,
            COUNT(*) as broj_termina,
            SUM(t.stvarna_cijena) as ukupno,
            AVG(t.stvarna_cijena) as prosek
        FROM termini t
        JOIN users u ON t.terapeut_id = u.id
        WHERE DATE(t.datum_vrijeme) BETWEEN ? AND ? 
        AND t.status = 'obavljen'
        AND t.placeno_iz_paketa = 0
        GROUP BY t.terapeut_id, u.ime, u.prezime
        ORDER BY ukupno DESC
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $prihodi_po_terapeutima = $stmt->fetchAll();
    
    // PRODATI PAKETI PO VRSTAMA
    $stmt = $pdo->prepare("
        SELECT 
            c.naziv as paket,
            k.naziv as kategorija,
            COUNT(*) as broj_prodatih,
            SUM(c.cijena) as ukupno,
            AVG(c.cijena) as prosek
        FROM kupljeni_paketi kp
        JOIN cjenovnik c ON kp.usluga_id = c.id
        LEFT JOIN kategorije_usluga k ON c.kategorija_id = k.id
        WHERE DATE(kp.datum_kupovine) BETWEEN ? AND ?
        GROUP BY c.id, c.naziv, k.naziv
        ORDER BY ukupno DESC
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $prodati_paketi = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri generiranju finansijskog izvještaja: " . $e->getMessage());
    $ukupni_prihodi = ['ukupno' => 0, 'broj_termina' => 0, 'termini_prihod' => 0, 'paketi_prihod' => 0, 'broj_paketa' => 0];
    $prihodi_po_danima = [];
    $prihodi_po_uslugama = [];
    $prihodi_po_terapeutima = [];
    $prodati_paketi = [];
}

$title = "Finansijski izvještaj";

ob_start();
require_once __DIR__ . '/../views/izvjestaji/finansijski.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>