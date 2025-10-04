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
    // Osnovne statistike za dashboard
    $danas = date('Y-m-d');
    $ovaj_mesec = date('Y-m');
    
    // Prihodi danas
    $stmt = $pdo->prepare("
        SELECT SUM(c.cijena) as ukupno
        FROM termini t
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE DATE(t.datum_vrijeme) = ? AND t.status = 'obavljen'
    ");
    $stmt->execute([$danas]);
    $prihod_danas = $stmt->fetchColumn() ?: 0;
    
    // Prihodi ovaj mesec (samo pojedinačni termini)
    $stmt = $pdo->prepare("
        SELECT SUM(c.cijena) as ukupno
        FROM termini t
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE DATE(t.datum_vrijeme) LIKE ? AND t.status = 'obavljen'
    ");
    $stmt->execute(["$ovaj_mesec%"]);
    $prihod_mesec = $stmt->fetchColumn() ?: 0;
    
    // Broj obavljenih termina ovaj mesec
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM termini 
        WHERE DATE(datum_vrijeme) LIKE ? AND status = 'obavljen'
    ");
    $stmt->execute(["$ovaj_mesec%"]);
    $termini_mesec = $stmt->fetchColumn();
    
    // Najaktivniji terapeut ovaj mesec
    $stmt = $pdo->prepare("
        SELECT CONCAT(u.ime, ' ', u.prezime) as ime, COUNT(*) as broj_termina
        FROM termini t
        JOIN users u ON t.terapeut_id = u.id
        WHERE DATE(t.datum_vrijeme) LIKE ? AND t.status = 'obavljen'
        GROUP BY t.terapeut_id
        ORDER BY broj_termina DESC
        LIMIT 1
    ");
    $stmt->execute(["$ovaj_mesec%"]);
    $top_terapeut = $stmt->fetch();
    
    // ========== STATISTIKE PAKETA ==========
    
    // Broj aktivnih paketa trenutno
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM kupljeni_paketi 
        WHERE status = 'aktivan'
    ");
    $aktivni_paketi = $stmt->fetchColumn();
    
    // Prihod od prodaje paketa ovaj mesec
    $stmt = $pdo->prepare("
        SELECT SUM(c.cijena) as ukupno
        FROM kupljeni_paketi kp
        JOIN cjenovnik c ON kp.usluga_id = c.id
        WHERE DATE(kp.datum_kupovine) LIKE ?
    ");
    $stmt->execute(["$ovaj_mesec%"]);
    $prihod_paketi_mesec = $stmt->fetchColumn() ?: 0;
    
    // Broj prodanih paketa ovaj mesec
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM kupljeni_paketi 
        WHERE DATE(datum_kupovine) LIKE ?
    ");
    $stmt->execute(["$ovaj_mesec%"]);
    $paketi_prodati_mesec = $stmt->fetchColumn();
    
    // Najkorišteniji paket ovaj mesec (po broju prodaja)
    $stmt = $pdo->prepare("
        SELECT c.naziv, COUNT(*) as broj_prodaja
        FROM kupljeni_paketi kp
        JOIN cjenovnik c ON kp.usluga_id = c.id
        WHERE DATE(kp.datum_kupovine) LIKE ?
        GROUP BY kp.usluga_id
        ORDER BY broj_prodaja DESC
        LIMIT 1
    ");
    $stmt->execute(["$ovaj_mesec%"]);
    $top_paket = $stmt->fetch();
    
    // Broj termina iskorištenih iz paketa ovaj mesec
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM termini_iz_paketa tip
        JOIN termini t ON tip.termin_id = t.id
        WHERE DATE(t.datum_vrijeme) LIKE ? AND t.status = 'obavljen'
    ");
    $stmt->execute(["$ovaj_mesec%"]);
    $termini_iz_paketa_mesec = $stmt->fetchColumn();
    
    // Ukupan prihod ovaj mesec (termini + paketi)
    $ukupan_prihod_mesec = $prihod_mesec + $prihod_paketi_mesec;
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju statistika: " . $e->getMessage());
    $prihod_danas = 0;
    $prihod_mesec = 0;
    $termini_mesec = 0;
    $top_terapeut = null;
    $aktivni_paketi = 0;
    $prihod_paketi_mesec = 0;
    $paketi_prodati_mesec = 0;
    $top_paket = null;
    $termini_iz_paketa_mesec = 0;
    $ukupan_prihod_mesec = 0;
}

$title = "Izvještaji";

ob_start();
require_once __DIR__ . '/../views/izvjestaji/dashboard.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>