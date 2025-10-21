<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

// Samo terapeut može videti svoje pacijente
if ($user['uloga'] !== 'terapeut') {
    header('Location: /dashboard');
    exit;
}

try {
    // Dohvati sve kartone pacijenata sa kojima sam radio
    $stmt = $pdo->prepare("
        SELECT DISTINCT k.*, 
               CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
               p.email,
               COUNT(DISTINCT t.id) as broj_termina,
               COUNT(DISTINCT tr.id) as broj_tretmana,
               MAX(t.datum_vrijeme) as poslednji_termin,
               MAX(tr.datum) as poslednji_tretman
        FROM kartoni k
        JOIN users p ON k.pacijent_id = p.id
        LEFT JOIN termini t ON t.pacijent_id = p.id AND t.terapeut_id = ?
        LEFT JOIN tretmani tr ON tr.karton_id = k.id AND tr.terapeut_id = ?
        WHERE EXISTS (
            SELECT 1 FROM termini t2 
            WHERE t2.pacijent_id = p.id AND t2.terapeut_id = ?
        )
        GROUP BY k.id, p.id
        ORDER BY MAX(COALESCE(tr.datum, t.datum_vrijeme)) DESC
    ");
    $stmt->execute([$user['id'], $user['id'], $user['id']]);
    $moji_kartoni = $stmt->fetchAll();
    
    // Statistike
    $ukupno_pacijenata = count($moji_kartoni);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE terapeut_id = ? AND status = 'obavljen'");
    $stmt->execute([$user['id']]);
    $ukupno_tretmana = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE terapeut_id = ? AND DATE(datum_vrijeme) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    $stmt->execute([$user['id']]);
    $termini_30_dana = $stmt->fetchColumn();
    
    // Pacijenti kojima je potreban follow-up (više od 14 dana od poslednjeg tretmana)
    $stmt = $pdo->prepare("
        SELECT k.*, 
               CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
               MAX(tr.datum) as poslednji_tretman,
               DATEDIFF(CURDATE(), MAX(tr.datum)) as dana_od_tretmana
        FROM kartoni k
        JOIN users p ON k.pacijent_id = p.id
        JOIN tretmani tr ON tr.karton_id = k.id
        WHERE tr.terapeut_id = ?
        GROUP BY k.id
        HAVING dana_od_tretmana > 14
        ORDER BY dana_od_tretmana DESC
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $potreban_followup = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju kartona: " . $e->getMessage());
    $moji_kartoni = [];
    $ukupno_pacijenata = 0;
    $ukupno_tretmana = 0;
    $termini_30_dana = 0;
    $potreban_followup = [];
}

$title = "Moji pacijenti";

ob_start();
require_once __DIR__ . '/../views/kartoni/moji.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>