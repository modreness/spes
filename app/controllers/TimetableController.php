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
    // Dohvati trenutna vremena smjena
    $stmt = $pdo->prepare("SELECT * FROM smjene_vremena WHERE aktivan = 1 ORDER BY FIELD(smjena, 'jutro', 'popodne', 'vecer')");
    $stmt->execute();
    $vremena_smjena = $stmt->fetchAll();
    
    // Organizuj po smjeni za lakše prikazivanje
    $vremena = [];
    foreach ($vremena_smjena as $smjena) {
        $vremena[$smjena['smjena']] = $smjena;
    }
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju vremena smjena: " . $e->getMessage());
    $vremena = [];
}

$title = "Timetable - Radna vremena";

ob_start();
require_once __DIR__ . '/../views/timetable/lista.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>