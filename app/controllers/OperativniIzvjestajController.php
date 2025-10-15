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
    // 1. NOVI PACIJENTI u periodu
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as broj_novih
        FROM users 
        WHERE uloga = 'pacijent' 
        AND DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $novi_pacijenti = $stmt->fetchColumn();
    
    // 2. UKUPNO AKTIVNIH PACIJENATA
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE uloga = 'pacijent'");
    $ukupno_pacijenata = $stmt->fetchColumn();
    
    // 3. STATISTIKE TERMINA PO STATUSU
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
    
    // Ukupan broj termina
    $ukupno_termina = array_sum($statistike_statusa);
    
    // 4. ISKORIŠĆENOST TERAPEUTA
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(u.ime, t.terapeut_ime) as ime,
            COALESCE(u.prezime, t.terapeut_prezime) as prezime,
            COUNT(t.id) as ukupno_termina,
            SUM(CASE WHEN t.status = 'obavljen' THEN 1 ELSE 0 END) as obavljeni,
            SUM(CASE WHEN t.status = 'otkazan' THEN 1 ELSE 0 END) as otkazani,
            SUM(CASE WHEN t.status = 'zakazan' THEN 1 ELSE 0 END) as zakazani,
            SUM(CASE WHEN t.status = 'propusten' THEN 1 ELSE 0 END) as propusteni
        FROM termini t
        LEFT JOIN users u ON u.id = t.terapeut_id
        WHERE DATE(t.datum_vrijeme) BETWEEN ? AND ?
        GROUP BY COALESCE(u.id, t.terapeut_ime, t.terapeut_prezime), 
                 COALESCE(u.ime, t.terapeut_ime), 
                 COALESCE(u.prezime, t.terapeut_prezime)
        ORDER BY obavljeni DESC
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $iskoriscenost_terapeuta = $stmt->fetchAll();
    
    // 5. NAJPOPULARNIJE USLUGE
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
        LIMIT 10
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $popularne_usluge = $stmt->fetchAll();
    
    // 6. STATISTIKE PO DANIMA U SEDMICI
    $stmt = $pdo->prepare("
        SELECT 
            CASE DAYOFWEEK(datum_vrijeme)
                WHEN 1 THEN 'Nedjelja'
                WHEN 2 THEN 'Ponedjeljak'
                WHEN 3 THEN 'Utorak'
                WHEN 4 THEN 'Srijeda'
                WHEN 5 THEN 'Četvrtak'
                WHEN 6 THEN 'Petak'
                WHEN 7 THEN 'Subota'
            END as dan,
            COUNT(*) as ukupno,
            SUM(CASE WHEN status = 'obavljen' THEN 1 ELSE 0 END) as obavljeno,
            SUM(CASE WHEN status = 'zakazan' THEN 1 ELSE 0 END) as zakazano,
            SUM(CASE WHEN status = 'otkazan' THEN 1 ELSE 0 END) as otkazano
        FROM termini 
        WHERE DATE(datum_vrijeme) BETWEEN ? AND ?
        GROUP BY DAYOFWEEK(datum_vrijeme)
        ORDER BY DAYOFWEEK(datum_vrijeme)
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $statistike_po_danima = $stmt->fetchAll();
    
    // 7. PROSJEČNO VRIJEME IZMEĐU TERMINA (opcionalno)
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
    
    // 8. BROJ TERMINA PO DANIMA (za graf)
    $stmt = $pdo->prepare("
        SELECT 
            DATE(datum_vrijeme) as dan,
            COUNT(*) as broj_termina,
            SUM(CASE WHEN status = 'obavljen' THEN 1 ELSE 0 END) as obavljeno
        FROM termini 
        WHERE DATE(datum_vrijeme) BETWEEN ? AND ?
        GROUP BY DATE(datum_vrijeme)
        ORDER BY datum_vrijeme
    ");
    $stmt->execute([$datum_od, $datum_do]);
    $termini_po_danima = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri generiranju operativnog izvještaja: " . $e->getMessage());
    $novi_pacijenti = 0;
    $ukupno_pacijenata = 0;
    $statistike_statusa = [];
    $ukupno_termina = 0;
    $iskoriscenost_terapeuta = [];
    $popularne_usluge = [];
    $statistike_po_danima = [];
    $prosecno_vreme = 0;
    $termini_po_danima = [];
}

$title = "Operativni izvještaj";

ob_start();
require_once __DIR__ . '/../views/izvjestaji/operativni.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';