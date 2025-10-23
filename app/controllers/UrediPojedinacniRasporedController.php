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
        
        // Dohvati vremena za novu smjenu iz smjene_vremena tabele - SAMO AKTIVNE!
        $stmt_smjena = $pdo->prepare("SELECT pocetak, kraj FROM smjene_vremena WHERE smjena = ? AND aktivan = 1");
        $stmt_smjena->execute([$smjena]);
        $smjena_vremena = $stmt_smjena->fetch();

        // Ako nema definisanih vremena, koristi NULL
        $pocetak = $smjena_vremena ? $smjena_vremena['pocetak'] : null;
        $kraj = $smjena_vremena ? $smjena_vremena['kraj'] : null;
        
        // Ažuriraj smjenu I vremena
        $stmt = $pdo->prepare("UPDATE rasporedi_sedmicni SET smjena = ?, pocetak = ?, kraj = ? WHERE id = ?");
        $stmt->execute([$smjena, $pocetak, $kraj, $raspored_id]);
        
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
        SELECT r.*, 
               -- Koristi zamrznute podatke ako postoje, inače trenutne iz users tabele
               COALESCE(r.terapeut_ime, u.ime, 'Nepoznat') as terapeut_ime_display,
               COALESCE(r.terapeut_prezime, u.prezime, '') as terapeut_prezime_display,
               CONCAT(COALESCE(r.terapeut_ime, u.ime, 'Nepoznat'), ' ', COALESCE(r.terapeut_prezime, u.prezime, '')) as terapeut_ime,
               u.email as terapeut_email
        FROM rasporedi_sedmicni r
        LEFT JOIN users u ON r.terapeut_id = u.id
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