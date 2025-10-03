<?php
require_once __DIR__ . '/../helpers/load.php';

require_login();

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

$pdo = db();

$paket_id = (int)($_GET['id'] ?? 0);

if (!$paket_id) {
    header('Location: /paketi');
    exit;
}

// Dohvati detalje paketa
try {
    $stmt = $pdo->prepare("
        SELECT 
            kp.*,
            CONCAT(u.ime, ' ', u.prezime) as pacijent_ime,
            u.email as pacijent_email,
            u.telefon as pacijent_telefon,
            c.naziv as paket_naziv,
            c.cijena as paket_cijena,
            c.broj_termina as paket_termina,
            c.period as paket_period,
            k.naziv as kategorija_naziv,
            CONCAT(kreator.ime, ' ', kreator.prezime) as kreirao_ime
        FROM kupljeni_paketi kp
        JOIN users u ON kp.pacijent_id = u.id
        JOIN cjenovnik c ON kp.usluga_id = c.id
        LEFT JOIN kategorije_usluga k ON c.kategorija_id = k.id
        JOIN users kreator ON kp.kreirao_id = kreator.id
        WHERE kp.id = ?
    ");
    $stmt->execute([$paket_id]);
    $paket = $stmt->fetch();
    
    if (!$paket) {
        header('Location: /paketi?msg=greska');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Greška: " . $e->getMessage());
    header('Location: /paketi?msg=greska');
    exit;
}

// Dohvati termine iz ovog paketa
try {
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            tp.datum_koriscenja,
            CONCAT(terapeut.ime, ' ', terapeut.prezime) as terapeut_ime,
            u.naziv as usluga_naziv
        FROM termini_iz_paketa tp
        JOIN termini t ON tp.termin_id = t.id
        LEFT JOIN users terapeut ON t.terapeut_id = terapeut.id
        LEFT JOIN cjenovnik u ON t.usluga_id = u.id
        WHERE tp.paket_id = ?
        ORDER BY t.datum_vrijeme DESC
    ");
    $stmt->execute([$paket_id]);
    $termini = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju termina: " . $e->getMessage());
    $termini = [];
}

// Izračunaj procenat
$procenat = $paket['ukupno_termina'] > 0 ? round(($paket['iskoristeno_termina'] / $paket['ukupno_termina']) * 100) : 0;

$title = "Detalji paketa";

ob_start();
require_once __DIR__ . '/../views/paketi/detalji.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>