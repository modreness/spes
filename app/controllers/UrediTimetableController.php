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

// Dohvati trenutna vremena smjena (i aktivne i neaktivne)
try {
    $stmt = $pdo->prepare("
        SELECT * FROM smjene_vremena 
        WHERE id IN (
            SELECT MAX(id) FROM smjene_vremena GROUP BY smjena
        )
        ORDER BY FIELD(smjena, 'jutro', 'popodne', 'vecer')
    ");
    $stmt->execute();
    $vremena_smjena = $stmt->fetchAll();
    
    $vremena = [];
    foreach ($vremena_smjena as $smjena) {
        $vremena[$smjena['smjena']] = $smjena;
    }
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju vremena smjena: " . $e->getMessage());
    $vremena = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smjene_data = $_POST['smjene'] ?? [];
    
    foreach ($smjene_data as $smjena_key => $data) {
        $pocetak = trim($data['pocetak'] ?? '');
        $kraj = trim($data['kraj'] ?? '');
        $aktivan = isset($data['aktivan']) ? 1 : 0;
        
        // Validacija
        if (empty($pocetak) || empty($kraj)) {
            $errors[] = "Početak i kraj vremena za smjenu " . smjene()[$smjena_key] . " su obavezni.";
            continue;
        }
        
        // Provjeri da li je kraj nakon početka
        if (strtotime($pocetak) >= strtotime($kraj)) {
            $errors[] = "Kraj smjene " . smjene()[$smjena_key] . " mora biti nakon početka.";
            continue;
        }
    }
    
    // Spremi promjene ako nema grešaka
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Ubaci nova vremena (ne brišemo stara zbog historije)
            foreach ($smjene_data as $smjena_key => $data) {
                $aktivan = isset($data['aktivan']) ? 1 : 0;
                
                // Deaktiviraj prethodne verzije ove smjene
                $stmt = $pdo->prepare("UPDATE smjene_vremena SET aktivan = 0 WHERE smjena = ?");
                $stmt->execute([$smjena_key]);
                
                // Ubaci novu verziju
                $stmt = $pdo->prepare("INSERT INTO smjene_vremena (smjena, pocetak, kraj, aktivan, kreirao_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$smjena_key, $data['pocetak'], $data['kraj'], $aktivan, $user['id']]);
            }
            
            $pdo->commit();
            header('Location: /timetable?msg=azurirano');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Greška pri ažuriranju vremena: " . $e->getMessage());
            $errors[] = 'Greška pri spremanju promjena.';
        }
    }
}

$title = "Uredi radna vremena";

ob_start();
require_once __DIR__ . '/../views/timetable/uredi.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>