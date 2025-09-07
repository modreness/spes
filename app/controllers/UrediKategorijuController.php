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
$kategorija_id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$kategorija_id) {
    header('Location: /kategorije');
    exit;
}

// Dohvati kategoriju
try {
    $stmt = $pdo->prepare("SELECT * FROM kategorije_usluga WHERE id = ? AND aktivan = 1");
    $stmt->execute([$kategorija_id]);
    $kategorija = $stmt->fetch();
    
    if (!$kategorija) {
        header('Location: /kategorije?msg=greska');
        exit;
    }
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju kategorije: " . $e->getMessage());
    header('Location: /kategorije?msg=greska');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naziv = trim($_POST['naziv'] ?? '');
    $opis = trim($_POST['opis'] ?? '');
    
    // Validacija
    if (empty($naziv)) {
        $errors[] = 'Naziv kategorije je obavezan.';
    }
    
    // Provjera da li već postoji kategorija sa istim nazivom (osim trenutne)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM kategorije_usluga WHERE naziv = ? AND id != ? AND aktivan = 1");
        $stmt->execute([$naziv, $kategorija_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Kategorija sa tim nazivom već postoji.';
        }
    }
    
    // Ažuriraj bazu
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE kategorije_usluga SET naziv = ?, opis = ? WHERE id = ?");
            $stmt->execute([$naziv, $opis, $kategorija_id]);
            
            header('Location: /kategorije?msg=azurirana');
            exit;
        } catch (PDOException $e) {
            error_log("Greška pri ažuriranju kategorije: " . $e->getMessage());
            $errors[] = 'Greška pri spremanju promjena.';
        }
    }
}

$title = "Uredi kategoriju";

ob_start();
require_once __DIR__ . '/../views/kategorije/uredi.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>