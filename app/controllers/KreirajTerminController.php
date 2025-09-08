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
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'pacijent' ORDER BY ime, prezime");
    $stmt->execute();
    $pacijenti = $stmt->fetchAll();
    
    // Terapeuti
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' ORDER BY ime, prezime");
    $stmt->execute();
    $terapeuti = $stmt->fetchAll();
    
    // Usluge sa kategorijama
    $stmt = $pdo->prepare("
        SELECT c.*, k.naziv as kategorija_naziv 
        FROM cjenovnik c 
        LEFT JOIN kategorije_usluga k ON c.kategorija_id = k.id 
        WHERE c.aktivan = 1 
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pacijent_id = $_POST['pacijent_id'] ?? '';
    $terapeut_id = $_POST['terapeut_id'] ?? '';
    $usluga_id = $_POST['usluga_id'] ?? '';
    $datum = $_POST['datum'] ?? '';
    $vrijeme = $_POST['vrijeme'] ?? '';
    $napomena = trim($_POST['napomena'] ?? '');
    
    // Validacija
    if (empty($pacijent_id)) {
        $errors[] = 'Pacijent je obavezan.';
    }
    
    if (empty($terapeut_id)) {
        $errors[] = 'Terapeut je obavezan.';
    }
    
    if (empty($usluga_id)) {
        $errors[] = 'Usluga je obavezna.';
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
    
    // Spremi termin
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO termini (pacijent_id, terapeut_id, usluga_id, datum_vrijeme, status, tip_zakazivanja, napomena) 
                VALUES (?, ?, ?, ?, 'zakazan', 'recepcioner', ?)
            ");
            $stmt->execute([$pacijent_id, $terapeut_id, $usluga_id, $datum_vrijeme, $napomena]);
            
            header('Location: /termini?msg=kreiran');
            exit;
        } catch (PDOException $e) {
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