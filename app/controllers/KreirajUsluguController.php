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

// Dohvati kategorije za dropdown
try {
    $stmt = $pdo->prepare("SELECT * FROM kategorije_usluga WHERE aktivan = 1 ORDER BY naziv");
    $stmt->execute();
    $kategorije = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju kategorija: " . $e->getMessage());
    $kategorije = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naziv = trim($_POST['naziv'] ?? '');
    $opis = trim($_POST['opis'] ?? '');
    $cijena = $_POST['cijena'] ?? '';
    $kategorija_id = $_POST['kategorija_id'] ?? '';
    
    // Validacija
    if (empty($naziv)) {
        $errors[] = 'Naziv usluge je obavezan.';
    }
    
    if (empty($cijena) || !is_numeric($cijena) || $cijena <= 0) {
        $errors[] = 'Cijena mora biti pozitivni broj.';
    }
    
    if (empty($kategorija_id)) {
        $errors[] = 'Kategorija je obavezna.';
    }
    
    // Provjera da li već postoji usluga sa istim nazivom
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cjenovnik WHERE naziv = ? AND aktivan = 1");
        $stmt->execute([$naziv]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Usluga sa tim nazivom već postoji.';
        }
    }
    
    // Spremi u bazu
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO cjenovnik (naziv, opis, cijena, kategorija_id, aktivan, datum_unosa) VALUES (?, ?, ?, ?, 1, NOW())");
            $stmt->execute([$naziv, $opis, $cijena, $kategorija_id]);
            
            header('Location: /cjenovnik?msg=kreirana');
            exit;
        } catch (PDOException $e) {
            error_log("Greška pri kreiranju usluge: " . $e->getMessage());
            $errors[] = 'Greška pri spremanju usluge.';
        }
    }
}

$title = "Kreiraj uslugu";

ob_start();
require_once __DIR__ . '/../views/cjenovnik/kreiraj.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>