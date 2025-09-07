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
    // Dohvati usluge sa kategorijama
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
    error_log("Greška pri dohvaćanju cjenovnika: " . $e->getMessage());
    $usluge = [];
}

$title = "Cjenovnik";

ob_start();
require_once __DIR__ . '/../views/cjenovnik/lista.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>