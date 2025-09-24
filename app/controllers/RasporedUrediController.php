<?php
require_once __DIR__ . '/../helpers/load.php';

require_login();

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

$pdo = db();

// Filter datum - defaultno ova sedmica
$datum_od = $_GET['datum_od'] ?? date('Y-m-d', strtotime('monday this week'));
$start_date = new DateTime($datum_od);
$end_date = (clone $start_date)->modify('+6 days');

// Obradi brisanje rasporeda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'obrisi') {
    try {
        $stmt = $pdo->prepare("DELETE FROM rasporedi_sedmicni WHERE id = ?");
        $stmt->execute([$_POST['raspored_id']]);
        
        header("Location: /raspored/uredi?datum_od=" . urlencode($datum_od) . "&msg=obrisan");
        exit;
    } catch (PDOException $e) {
        error_log("Greška pri brisanju rasporeda: " . $e->getMessage());
        header("Location: /raspored/uredi?datum_od=" . urlencode($datum_od) . "&msg=greska");
        exit;
    }
}

// Obradi ažuriranje statusa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    try {
        $stmt = $pdo->prepare("UPDATE rasporedi_sedmicni SET aktivan = NOT aktivan WHERE id = ?");
        $stmt->execute([$_POST['raspored_id']]);
        
        header("Location: /raspored/uredi?datum_od=" . urlencode($datum_od) . "&msg=azuriran");
        exit;
    } catch (PDOException $e) {
        error_log("Greška pri ažuriranju statusa: " . $e->getMessage());
        header("Location: /raspored/uredi?datum_od=" . urlencode($datum_od) . "&msg=greska");
        exit;
    }
}

// Dohvati sve rasporede za sedmicu
try {
    $rasporedi = $pdo->prepare("
        SELECT r.*, 
               CONCAT(u.ime, ' ', u.prezime) AS terapeut_ime,
               u.email as terapeut_email
        FROM rasporedi_sedmicni r
        JOIN users u ON r.terapeut_id = u.id
        WHERE r.datum_od BETWEEN :od AND :do
        ORDER BY u.prezime, u.ime, 
                 FIELD(r.dan, 'ponedeljak','utorak','sreda','cetvrtak','petak','subota','nedelja'),
                 FIELD(r.smjena, 'jutro','popodne','vecer')
    ");
    $rasporedi->execute([
        'od' => $start_date->format('Y-m-d'),
        'do' => $end_date->format('Y-m-d')
    ]);
    $svi_rasporedi = $rasporedi->fetchAll(PDO::FETCH_ASSOC);
    
    // Grupiraj po terapeutima
    $rasporedi_po_terapeutu = [];
    foreach ($svi_rasporedi as $r) {
        $rasporedi_po_terapeutu[$r['terapeut_ime']][] = $r;
    }
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju rasporeda: " . $e->getMessage());
    $svi_rasporedi = [];
    $rasporedi_po_terapeutu = [];
}

$title = "Uredi rasporede";

ob_start();
require_once __DIR__ . '/../views/raspored/uredi.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>