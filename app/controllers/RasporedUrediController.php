<?php
require_once __DIR__ . '/../helpers/load.php';

require_login();

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

$pdo = db();

// Filteri
$datum_od = $_GET['datum_od'] ?? date('Y-m-d', strtotime('monday this week'));
$terapeut_filter = $_GET['filter_terapeut'] ?? '';

$start_date = new DateTime($datum_od);
$end_date = (clone $start_date)->modify('+6 days');

// Dohvati sve terapeute za dropdown
try {
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' AND aktivan = 1 ORDER BY ime, prezime");
    $stmt->execute();
    $svi_terapeuti = $stmt->fetchAll();
} catch (PDOException $e) {
    $svi_terapeuti = [];
}

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

// Dohvati sve rasporede za sedmicu - KORISTI ZAMRZNUTE PODATKE + VREMENA
try {
    $sql = "SELECT r.*, 
               -- Koristi zamrznute podatke ako postoje, inače trenutne iz users tabele
               COALESCE(r.terapeut_ime, u.ime) AS terapeut_ime_display,
               COALESCE(r.terapeut_prezime, u.prezime) AS terapeut_prezime_display,
               CONCAT(COALESCE(r.terapeut_ime, u.ime), ' ', COALESCE(r.terapeut_prezime, u.prezime)) AS terapeut_ime,
               u.email as terapeut_email,
               r.terapeut_id,
               -- Dodaj i podatke o unositelju
               COALESCE(r.unosio_ime, u2.ime) as unosio_ime_display,
               COALESCE(r.unosio_prezime, u2.prezime) as unosio_prezime_display,
               -- Vremena - koristi zamrznuta iz rasporedi_sedmicni ili fallback iz smjene_vremena
               COALESCE(r.pocetak, sv.pocetak) as pocetak_display,
               COALESCE(r.kraj, sv.kraj) as kraj_display
        FROM rasporedi_sedmicni r
        LEFT JOIN users u ON r.terapeut_id = u.id
        LEFT JOIN users u2 ON r.unosio_id = u2.id
        LEFT JOIN smjene_vremena sv ON r.smjena = sv.smjena
        WHERE r.datum_od = ?";
    
    $params = [$datum_od];
    
    // Dodaj filter za terapeuta ako je odabran
    if (!empty($terapeut_filter)) {
        $sql .= " AND r.terapeut_id = ?";
        $params[] = $terapeut_filter;
    }
    
    $sql .= " ORDER BY COALESCE(r.terapeut_prezime, u.prezime), COALESCE(r.terapeut_ime, u.ime), 
              FIELD(r.dan, 'pon','uto','sri','cet','pet','sub','ned'),
              FIELD(r.smjena, 'jutro','popodne','vecer')";
    
    $rasporedi = $pdo->prepare($sql);
    $rasporedi->execute($params);
    $svi_rasporedi = $rasporedi->fetchAll(PDO::FETCH_ASSOC);
    
    // Grupiraj po terapeutima
    $rasporedi_po_terapeutu = [];
    foreach ($svi_rasporedi as $r) {
        $rasporedi_po_terapeutu[$r['terapeut_id']]['info'] = [
            'ime' => $r['terapeut_ime'],
            'email' => $r['terapeut_email']
        ];
        $rasporedi_po_terapeutu[$r['terapeut_id']]['dani'][] = $r;
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