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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $termin_id = $_POST['id'] ?? null;
    
    if (!$termin_id) {
        header('Location: /termini/lista?msg=greska');
        exit;
    }
    
    try {
        // Provjeri da li je termin vec obavljen - ako jeste, ne dozvoli brisanje
        $stmt = $pdo->prepare("SELECT status FROM termini WHERE id = ?");
        $stmt->execute([$termin_id]);
        $termin = $stmt->fetch();
        
        if (!$termin) {
            header('Location: /termini/lista?msg=greska');
            exit;
        }
        
        if ($termin['status'] === 'obavljen') {
            header('Location: /termini/lista?msg=ne-moze-brisati');
            exit;
        }
        
        // Obriši termin
        $stmt = $pdo->prepare("DELETE FROM termini WHERE id = ?");
        $stmt->execute([$termin_id]);
        
        header('Location: /termini/lista?msg=obrisan');
        exit;
        
    } catch (PDOException $e) {
        error_log("Greška pri brisanju termina: " . $e->getMessage());
        header('Location: /termini/lista?msg=greska');
        exit;
    }
}

header('Location: /termini/lista');
exit;
?>