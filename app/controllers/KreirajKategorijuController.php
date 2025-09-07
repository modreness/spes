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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naziv = trim($_POST['naziv'] ?? '');
    $opis = trim($_POST['opis'] ?? '');
    
    // Validacija
    if (empty($naziv)) {
        $errors[] = 'Naziv kategorije je obavezan.';
    }
    
    // Provjera da li već postoji kategorija sa istim nazivom
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM kategorije_usluga WHERE naziv = ? AND aktivan = 1");
        $stmt->execute([$naziv]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Kategorija sa tim nazivom već postoji.';
        }
    }
    
    // Spremi u bazu
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO kategorije_usluga (naziv, opis, aktivan) VALUES (?, ?, 1)");
            $stmt->execute([$naziv, $opis]);
            
            header('Location: /kategorije?msg=kreirana');
            exit;
        } catch (PDOException $e) {
            error_log("Greška pri kreiranju kategorije: " . $e->getMessage());
            $errors[] = 'Greška pri spremanju kategorije.';
        }
    }
}

$title = "Kreiraj kategoriju";

ob_start();
require_once __DIR__ . '/../views/kategorije/kreiraj.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>