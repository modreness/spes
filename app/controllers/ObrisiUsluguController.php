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
    $usluga_id = $_POST['id'] ?? null;
    
    if (!$usluga_id) {
        header('Location: /cjenovnik?msg=greska');
        exit;
    }
    
    try {
        // Provjeri da li je usluga korištena u terminima
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE usluga_id = ?");
        $stmt->execute([$usluga_id]);
        $broj_termina = $stmt->fetchColumn();
        
        if ($broj_termina > 0) {
            // Ako je korištena u terminima, samo deaktiviraj
            $stmt = $pdo->prepare("UPDATE cjenovnik SET aktivan = 0 WHERE id = ?");
            $stmt->execute([$usluga_id]);
            
            header('Location: /cjenovnik?msg=deaktivirana');
            exit;
        } else {
            // Ako nije korištena, možemo potpuno obrisati
            $stmt = $pdo->prepare("DELETE FROM cjenovnik WHERE id = ?");
            $stmt->execute([$usluga_id]);
            
            header('Location: /cjenovnik?msg=obrisana');
            exit;
        }
        
    } catch (PDOException $e) {
        error_log("Greška pri brisanju usluge: " . $e->getMessage());
        header('Location: /cjenovnik?msg=greska');
        exit;
    }
}

// Ako nije POST, vrati na listu
header('Location: /cjenovnik');
exit;
?>