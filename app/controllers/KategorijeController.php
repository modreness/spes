<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

// Samo admin i recepcioner mogu pristupiti
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
require_once __DIR__ . '/../views/layout/header.php';
require_once __DIR__ . '/../views/kategorije/lista.php';
require_once __DIR__ . '/../views/layout/footer.php';
?>