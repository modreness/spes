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
$usluga_id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$usluga_id) {
    header('Location: /cjenovnik');
    exit;
}

// Dohvati kategorije za dropdown
try {
    $stmt = $pdo->prepare("SELECT * FROM kategorije_usluga WHERE aktivan = 1 ORDER BY naziv");
    $stmt->execute();
    $kategorije = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju kategorija: " . $e->getMessage());
    $kategorije = [];
}

// Dohvati uslugu
try {
    $stmt = $pdo->prepare("SELECT * FROM cjenovnik WHERE id = ? AND aktivan = 1");
    $stmt->execute([$usluga_id]);
    $usluga = $stmt->fetch();
    
    if (!$usluga) {
        header('Location: /cjenovnik?msg=greska');
        exit;
    }
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju usluge: " . $e->getMessage());
    header('Location: /cjenovnik?msg=greska');
    exit;
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
    
    // Provjera da li već postoji usluga sa istim nazivom (osim trenutne)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cjenovnik WHERE naziv = ? AND id != ? AND aktivan = 1");
        $stmt->execute([$naziv, $usluga_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Usluga sa tim nazivom već postoji.';
        }
    }
    
    // Ažuriraj bazu
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE cjenovnik SET naziv = ?, opis = ?, cijena = ?, kategorija_id = ? WHERE id = ?");
            $stmt->execute([$naziv, $opis, $cijena, $kategorija_id, $usluga_id]);
            
            header('Location: /cjenovnik?msg=azurirana');
            exit;
        } catch (PDOException $e) {
            error_log("Greška pri ažuriranju usluge: " . $e->getMessage());
            $errors[] = 'Greška pri spremanju promjena.';
        }
    }
}

$title = "Uredi uslugu";

ob_start();
require_once __DIR__ . '/../views/cjenovnik/uredi.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>