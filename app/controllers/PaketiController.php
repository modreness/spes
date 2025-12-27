<?php
require_once __DIR__ . '/../helpers/load.php';

require_login();

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

$pdo = db();

$action = $_GET['action'] ?? 'index';

// Promjena statusa paketa
if ($action === 'update_status') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        $dozvoljeni_statusi = ['aktivan', 'završen', 'istekao', 'otkazan'];
        
        if (!in_array($status, $dozvoljeni_statusi)) {
            $_SESSION['error'] = "Nevažeći status.";
            header("Location: /paketi?action=detalji&id=$id");
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE kupljeni_paketi SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            $_SESSION['message'] = "Status paketa uspješno promijenjen!";
        } catch (PDOException $e) {
            error_log("Greška pri promjeni statusa: " . $e->getMessage());
            $_SESSION['error'] = "Greška pri promjeni statusa.";
        }
        
        header("Location: /paketi?action=detalji&id=$id");
        exit;
    }
}

// Brisanje paketa
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? 0;
        
        $transakcija_aktivna = false;
        
        try {
            $pdo->beginTransaction();
            $transakcija_aktivna = true;
            
            // Dohvati termine povezane sa paketom
            $stmt = $pdo->prepare("SELECT termin_id FROM termini_iz_paketa WHERE paket_id = ?");
            $stmt->execute([$id]);
            $termin_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Ako ima povezanih termina, resetuj flag placeno_iz_paketa
            if (!empty($termin_ids)) {
                $placeholders = implode(',', array_fill(0, count($termin_ids), '?'));
                $stmt = $pdo->prepare("UPDATE termini SET placeno_iz_paketa = 0 WHERE id IN ($placeholders)");
                $stmt->execute($termin_ids);
            }
            
            // Obriši veze iz termini_iz_paketa
            $stmt = $pdo->prepare("DELETE FROM termini_iz_paketa WHERE paket_id = ?");
            $stmt->execute([$id]);
            
            // Obriši paket
            $stmt = $pdo->prepare("DELETE FROM kupljeni_paketi WHERE id = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            
            $_SESSION['message'] = "Paket uspješno obrisan!";
            header("Location: /paketi");
            exit;
            
        } catch (PDOException $e) {
            if ($transakcija_aktivna) {
                $pdo->rollBack();
            }
            error_log("Greška pri brisanju paketa: " . $e->getMessage());
            $_SESSION['error'] = "Greška pri brisanju paketa.";
            header("Location: /paketi?action=detalji&id=$id");
            exit;
        }
    }
}

// Filteri
$filter_pacijent = $_GET['filter_pacijent'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

// Statistike
try {
    // Ukupno prodatih paketa
    $stmt = $pdo->query("SELECT COUNT(*) FROM kupljeni_paketi");
    $ukupno_paketa = $stmt->fetchColumn();
    
    // Aktivnih paketa
    $stmt = $pdo->query("SELECT COUNT(*) FROM kupljeni_paketi WHERE status = 'aktivan'");
    $aktivnih_paketa = $stmt->fetchColumn();
    
    // Prosječna iskorištenost
    $stmt = $pdo->query("
        SELECT AVG((iskoristeno_termina / ukupno_termina) * 100) 
        FROM kupljeni_paketi 
        WHERE ukupno_termina > 0
    ");
    $prosjecna_iskoristenos = round($stmt->fetchColumn() ?? 0, 1);
    
} catch (PDOException $e) {
    error_log("Greška statistike: " . $e->getMessage());
    $ukupno_paketa = 0;
    $aktivnih_paketa = 0;
    $prosjecna_iskoristenos = 0;
}

// Dohvati sve pacijente za filter
try {
    $stmt = $pdo->prepare("
        SELECT id, ime, prezime 
        FROM users 
        WHERE uloga = 'pacijent' AND aktivan = 1 
        ORDER BY ime, prezime
    ");
    $stmt->execute();
    $pacijenti = $stmt->fetchAll();
} catch (PDOException $e) {
    $pacijenti = [];
}

// Dohvati pakete sa filterima
try {
    $sql = "
        SELECT 
            kp.*,
            CONCAT(u.ime, ' ', u.prezime) as pacijent_ime,
            u.email as pacijent_email,
            c.naziv as paket_naziv,
            c.cijena as paket_cijena,
            CONCAT(k.ime, ' ', k.prezime) as kreirao_ime
        FROM kupljeni_paketi kp
        JOIN users u ON kp.pacijent_id = u.id
        JOIN cjenovnik c ON kp.usluga_id = c.id
        JOIN users k ON kp.kreirao_id = k.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if (!empty($filter_pacijent)) {
        $sql .= " AND kp.pacijent_id = ?";
        $params[] = $filter_pacijent;
    }
    
    if (!empty($filter_status)) {
        $sql .= " AND kp.status = ?";
        $params[] = $filter_status;
    }
    
    $sql .= " ORDER BY kp.datum_kupovine DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $paketi = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju paketa: " . $e->getMessage());
    $paketi = [];
}

$title = "Paketi";

ob_start();
require_once __DIR__ . '/../views/paketi/dashboard.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>