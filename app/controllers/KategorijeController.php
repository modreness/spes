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
    $stmt = $pdo->prepare("SELECT * FROM kategorije_usluga WHERE aktivan = 1 ORDER BY naziv");
    $stmt->execute();
    $kategorije = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju kategorija: " . $e->getMessage());
    $kategorije = [];
}

$title = "Kategorije usluga";

// Capture view output
ob_start();
require_once __DIR__ . '/../views/kategorije/lista.php';
$content = ob_get_clean();

// Include layout
require_once __DIR__ . '/../views/layout.php';
?>