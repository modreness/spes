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

$errors = [];

// Dohvati podatke za dropdowne
try {
    // Pacijenti
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'pacijent' AND aktivan = 1 ORDER BY ime, prezime");
    $stmt->execute();
    $pacijenti = $stmt->fetchAll();
    
    // Terapeuti
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' AND aktivan = 1 ORDER BY ime, prezime");
    $stmt->execute();
    $terapeuti = $stmt->fetchAll();
    
    // Usluge sa kategorijama - SAMO POJEDINAČNE za sada (pakete ne prikazuj u dropdown)
    $stmt = $pdo->prepare("
        SELECT c.*, k.naziv as kategorija_naziv 
        FROM cjenovnik c 
        LEFT JOIN kategorije_usluga k ON c.kategorija_id = k.id 
        WHERE c.aktivan = 1 AND c.tip_usluge = 'pojedinacna'
        ORDER BY k.naziv, c.naziv
    ");
    $stmt->execute();
    $usluge = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju podataka: " . $e->getMessage());
    $pacijenti = [];
    $terapeuti = [];
    $usluge = [];
}

// Proveri da li pacijent ima aktivan paket
$aktivni_paketi = [];
$odabrani_pacijent_id = $_POST['pacijent_id'] ?? $_GET['pacijent_id'] ?? '';
$odabrani_terapeut_id = $_POST['terapeut_id'] ?? $_GET['terapeut_id'] ?? '';

if (!empty($odabrani_pacijent_id)) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                kp.*,
                c.naziv as paket_naziv,
                c.cijena as paket_cijena
            FROM kupljeni_paketi kp
            JOIN cjenovnik c ON kp.usluga_id = c.id
            WHERE kp.pacijent_id = ? 
            AND kp.status = 'aktivan'
            AND kp.iskoristeno_termina < kp.ukupno_termina
            ORDER BY kp.datum_kupovine DESC
        ");
        $stmt->execute([$odabrani_pacijent_id]);
        $aktivni_paketi = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Greška pri dohvaćanju paketa: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pacijent_id = $_POST['pacijent_id'] ?? '';
    $terapeut_id = $_POST['terapeut_id'] ?? '';
    $usluga_id = $_POST['usluga_id'] ?? '';
    $datum = $_POST['datum'] ?? '';
    $vrijeme = $_POST['vrijeme'] ?? '';
    $napomena = trim($_POST['napomena'] ?? '');
    $koristi_paket = $_POST['koristi_paket'] ?? '';  // ID paketa ili 'ne'
    
    // Validacija
    if (empty($pacijent_id)) {
        $errors[] = 'Pacijent je obavezan.';
    }
    
    if (empty($terapeut_id)) {
        $errors[] = 'Terapeut je obavezan.';
    }
    
    // Usluga je obavezna samo ako se NE koristi paket
    if (empty($koristi_paket) || $koristi_paket === 'ne') {
        if (empty($usluga_id)) {
            $errors[] = 'Usluga je obavezna.';
        }
    }
    
    if (empty($datum)) {
        $errors[] = 'Datum je obavezan.';
    }
    
    if (empty($vrijeme)) {
        $errors[] = 'Vrijeme je obavezno.';
    }
    
    // Kombinuj datum i vrijeme
    $datum_vrijeme = '';
    if (!empty($datum) && !empty($vrijeme)) {
        $datum_vrijeme = $datum . ' ' . $vrijeme;
        
        // Provjeri da li je u budućnosti
        if (strtotime($datum_vrijeme) <= time()) {
            $errors[] = 'Termin mora biti u budućnosti.';
        }
    }
    
    // Provjeri koliziju termina
    if (empty($errors) && !empty($datum_vrijeme)) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM termini 
            WHERE terapeut_id = ? 
            AND datum_vrijeme = ? 
            AND status IN ('zakazan', 'slobodan')
        ");
        $stmt->execute([$terapeut_id, $datum_vrijeme]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Terapeut već ima zakazan termin u to vrijeme.';
        }
    }
    
    // Ako koristi paket - proveri validnost
    $paket_id = null;
    if (!empty($koristi_paket) && $koristi_paket !== 'ne') {
        $paket_id = (int)$koristi_paket;
        
        // Proveri da li paket postoji i ima slobodne termine
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM kupljeni_paketi 
                WHERE id = ? 
                AND pacijent_id = ? 
                AND status = 'aktivan'
                AND iskoristeno_termina < ukupno_termina
            ");
            $stmt->execute([$paket_id, $pacijent_id]);
            $paket = $stmt->fetch();
            
            if (!$paket) {
                $errors[] = 'Odabrani paket nije validan ili nema slobodnih termina.';
                $paket_id = null;
            }
        } catch (PDOException $e) {
            error_log("Greška: " . $e->getMessage());
            $errors[] = 'Greška pri provjeri paketa.';
        }
    }
    
    // Spremi termin
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // 1. Kreiraj termin
            $stmt = $pdo->prepare("
                INSERT INTO termini (pacijent_id, terapeut_id, usluga_id, datum_vrijeme, status, tip_zakazivanja, napomena) 
                VALUES (?, ?, ?, ?, 'zakazan', 'recepcioner', ?)
            ");
            $stmt->execute([$pacijent_id, $terapeut_id, $usluga_id, $datum_vrijeme, $napomena]);
            $termin_id = $pdo->lastInsertId();
            
            // 2. Ako se koristi paket - poveži termin sa paketom
            if ($paket_id) {
                $stmt = $pdo->prepare("
                    INSERT INTO termini_iz_paketa (termin_id, paket_id) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$termin_id, $paket_id]);
                
                // Trigger će automatski povećati iskoristeno_termina
            }
            
            $pdo->commit();
            
            header('Location: /termini?msg=kreiran');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Greška pri kreiranju termina: " . $e->getMessage());
            $errors[] = 'Greška pri spremanju termina.';
        }
    }
}

$title = "Kreiraj termin";

ob_start();
require_once __DIR__ . '/../views/termini/kreiraj.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>