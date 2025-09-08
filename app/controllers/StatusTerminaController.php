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
    $novi_status = $_POST['status'] ?? null;
    
    if (!$termin_id || !$novi_status) {
        header('Location: /termini/lista?msg=greska');
        exit;
    }
    
    // Validacija dozvoljenih statusa
    $dozvoljeni_statusi = ['zakazan', 'otkazan', 'obavljen', 'slobodan'];
    if (!in_array($novi_status, $dozvoljeni_statusi)) {
        header('Location: /termini/lista?msg=greska');
        exit;
    }
    
    try {
        // Provjeri da li termin postoji
        $stmt = $pdo->prepare("SELECT id, status FROM termini WHERE id = ?");
        $stmt->execute([$termin_id]);
        $termin = $stmt->fetch();
        
        if (!$termin) {
            header('Location: /termini/lista?msg=greska');
            exit;
        }
        
        // Ažuriraj status
        $stmt = $pdo->prepare("UPDATE termini SET status = ? WHERE id = ?");
        $stmt->execute([$novi_status, $termin_id]);
        
        // Redirekt sa porukom
        $msg = $novi_status === 'otkazan' ? 'otkazan' : 
               ($novi_status === 'obavljen' ? 'obavljen' : 'azuriran');
        
        header("Location: /termini/lista?msg=$msg");
        exit;
        
    } catch (PDOException $e) {
        error_log("Greška pri ažuriranju statusa termina: " . $e->getMessage());
        header('Location: /termini/lista?msg=greska');
        exit;
    }
}

// Ako nije POST, redirekt na listu
header('Location: /termini/lista');
exit;
?>