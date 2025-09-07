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
    $kategorija_id = $_POST['id'] ?? null;
    
    if (!$kategorija_id) {
        header('Location: /kategorije?msg=greska');
        exit;
    }
    
    try {
        // Provjeri da li postoje usluge u cjenovniku za ovu kategoriju
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cjenovnik WHERE kategorija_id = ? AND aktivan = 1");
        $stmt->execute([$kategorija_id]);
        $broj_usluga = $stmt->fetchColumn();
        
        if ($broj_usluga > 0) {
            // Ako ima usluga, samo deaktiviraj kategoriju ali ostavi naziv za historijski trag
            $stmt = $pdo->prepare("UPDATE kategorije_usluga SET aktivan = 0, datum_brisanja = NOW() WHERE id = ?");
            $stmt->execute([$kategorija_id]);
            
            header('Location: /kategorije?msg=deaktivirana');
            exit;
        } else {
            // Ako nema usluga, možemo potpuno obrisati
            $stmt = $pdo->prepare("DELETE FROM kategorije_usluga WHERE id = ?");
            $stmt->execute([$kategorija_id]);
            
            header('Location: /kategorije?msg=obrisana');
            exit;
        }
        
    } catch (PDOException $e) {
        error_log("Greška pri brisanju kategorije: " . $e->getMessage());
        header('Location: /kategorije?msg=greska');
        exit;
    }
}

// Ako nije POST, vrati na listu
header('Location: /kategorije');
exit;
?>