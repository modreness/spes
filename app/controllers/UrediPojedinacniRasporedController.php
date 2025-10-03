<?php
require_once __DIR__ . '/../helpers/load.php';

require_login();

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

$pdo = db();

// Dohvati raspored za uređivanje
if (!isset($_GET['id'])) {
    header('Location: /raspored/uredi');
    exit;
}

$raspored_id = (int)$_GET['id'];

// POST - Ažuriranje
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $smjena = $_POST['smjena'];
        
        if (empty($smjena)) {
            header("Location: /raspored/uredi-pojedinacni?id=$raspored_id&msg=greska");
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE rasporedi_sedmicni SET smjena = ? WHERE id = ?");
        $stmt->execute([$smjena, $raspored_id]);
        
        // Dohvati datum_od za redirect
        $stmt = $pdo->prepare("SELECT datum_od FROM rasporedi_sedmicni WHERE id = ?");
        $stmt->execute([$raspored_id]);
        $datum_od = $stmt->fetchColumn();
        
        header("Location: /raspored/uredi?datum_od=" . urlencode($datum_od) . "&msg=azuriran");
        exit;
        
    } catch (PDOException $e) {
        error_log("Greška pri ažuriranju rasporeda: " . $e->getMessage());
        header("Location: /raspored/uredi-pojedinacni?id=$raspored_id&msg=greska");
        exit;
    }
}

// GET - Prikaz forme
try {
    $stmt = $pdo->prepare("
        SELECT r.*, CONCAT(u.ime, ' ', u.prezime) as terapeut_ime, u.email as terapeut_email
        FROM rasporedi_sedmicni r
        JOIN users u ON r.terapeut_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$raspored_id]);
    $raspored = $stmt->fetch();
    
    if (!$raspored) {
        header('Location: /raspored/uredi?msg=greska');
        exit;
    }
    
    // Izračunaj stvarni datum dana
    $dan_offset = array_search($raspored['dan'], array_keys(dani()));
    $stvarni_datum = date('d.m.Y', strtotime("+$dan_offset days", strtotime($raspored['datum_od'])));
    
} catch (PDOException $e) {
    error_log("Greška: " . $e->getMessage());
    header('Location: /raspored/uredi?msg=greska');
    exit;
}

$title = "Uredi raspored";

ob_start();
require_once __DIR__ . '/../views/raspored/uredi-pojedinacni.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>